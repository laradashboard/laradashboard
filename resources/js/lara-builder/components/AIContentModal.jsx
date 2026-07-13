/**
 * AIContentModal - AI Content Generation drawer for LaraBuilder
 *
 * Provides AI-powered content generation within a right-side drawer.
 * Supports multiple AI providers (OpenAI, Claude) and generates structured content.
 */

import { useState, useEffect, useCallback } from "react";
import { __ } from "@lara-builder/i18n";
import { blockRegistry } from "../registry/BlockRegistry";
import SideDrawer from "./SideDrawer";

function AIContentModal({
    isOpen,
    onClose,
    onInsertContent,
    isPostContext = false,
    setTitle,
    setExcerpt,
}) {
    const [provider, setProvider] = useState("");
    const [providers, setProviders] = useState({});
    const [defaultProvider, setDefaultProvider] = useState("");
    const [isConfigured, setIsConfigured] = useState(false);
    const [prompt, setPrompt] = useState("");
    const [loading, setLoading] = useState(false);
    const [loadingProviders, setLoadingProviders] = useState(true);
    const [generatedContent, setGeneratedContent] = useState(null);
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        if (isOpen) {
            fetchProviders();
        }
    }, [isOpen]);

    const fetchProviders = async () => {
        setLoadingProviders(true);
        try {
            const response = await fetch("/admin/ai/providers", {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                throw new Error("Failed to fetch providers");
            }

            const data = await response.json();

            if (data.success) {
                setProviders(data.data.providers || {});
                setDefaultProvider(data.data.default_provider || "");
                setIsConfigured(data.data.is_configured || false);
                setProvider(data.data.default_provider || "");
            }
        } catch (error) {
            console.error("Error fetching AI providers:", error);
            setErrorMessage(__("Failed to load AI providers"));
        } finally {
            setLoadingProviders(false);
        }
    };

    const generateContent = async () => {
        if (!prompt.trim()) {
            setErrorMessage(__("Please enter a prompt to generate content."));
            return;
        }

        if (!provider) {
            setErrorMessage(
                __(
                    "Please select an AI provider or configure from AI Integrations settings."
                )
            );
            return;
        }

        setLoading(true);
        setErrorMessage("");
        setGeneratedContent(null);

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch("/admin/ai/generate-content", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    prompt: prompt,
                    provider: provider,
                    content_type: "post_content",
                }),
            });

            const data = await response.json();

            if (data.success) {
                let content = data.data;
                if (typeof content === "string") {
                    try {
                        content = JSON.parse(content);
                    } catch (e) {
                        console.warn("Failed to parse AI response as JSON:", e);
                    }
                }
                setGeneratedContent(content);
            } else {
                setErrorMessage(
                    data.message || __("Failed to generate content")
                );
            }
        } catch (error) {
            setErrorMessage(__("Network error. Please try again."));
            console.error("AI Generation Error:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleClose = useCallback(() => {
        setPrompt("");
        setGeneratedContent(null);
        setErrorMessage("");
        onClose();
    }, [onClose]);

    const insertContent = useCallback(() => {
        if (!generatedContent) return;

        if (isPostContext && setTitle && generatedContent.title) {
            setTitle(generatedContent.title);
        }

        if (isPostContext && setExcerpt && generatedContent.excerpt) {
            setExcerpt(generatedContent.excerpt);
        }

        if (generatedContent.content && onInsertContent) {
            const blocks = [];

            if (generatedContent.title && !isPostContext) {
                const headingBlock = blockRegistry.createInstance("heading", {
                    content: generatedContent.title,
                    level: "h1",
                    align: "left",
                });
                if (headingBlock) blocks.push(headingBlock);
            }

            const content = generatedContent.content;

            if (content.includes("<") && content.includes(">")) {
                const textEditorBlock = blockRegistry.createInstance(
                    "text-editor",
                    {
                        content: content,
                    }
                );
                if (textEditorBlock) blocks.push(textEditorBlock);
            } else {
                const paragraphs = content
                    .split(/\n\n+/)
                    .map((p) => p.trim())
                    .filter((p) => p.length > 0);

                paragraphs.forEach((paragraph) => {
                    const formattedContent = paragraph.replace(/\n/g, "<br>");

                    const textBlock = blockRegistry.createInstance("text", {
                        content: formattedContent,
                        align: "left",
                    });
                    if (textBlock) blocks.push(textBlock);
                });
            }

            if (blocks.length > 0) {
                onInsertContent(blocks);
            }
        }

        handleClose();
    }, [
        generatedContent,
        isPostContext,
        setTitle,
        setExcerpt,
        onInsertContent,
        handleClose,
    ]);

    return (
        <SideDrawer
            isOpen={isOpen}
            onClose={handleClose}
            title={__("AI Content Generator")}
            subtitle={__("Generate title, excerpt, and body content with AI")}
            icon={
                <iconify-icon
                    icon="mdi:lightning-bolt"
                    width="20"
                    height="20"
                ></iconify-icon>
            }
            iconClassName="text-white"
            iconStyle={{ backgroundColor: "var(--color-primary, #635bff)" }}
            footer={
                generatedContent ? (
                    <div className="px-5 py-4 border-t border-gray-200 bg-gray-50 flex gap-3">
                        <button
                            type="button"
                            onClick={handleClose}
                            className="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            {__("Cancel")}
                        </button>
                        <button
                            type="button"
                            onClick={insertContent}
                            className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-lg transition-all duration-200"
                        >
                            {__("Insert Content")}
                        </button>
                    </div>
                ) : (
                    <div className="px-5 py-4 border-t border-gray-200 bg-gray-50">
                        <button
                            type="button"
                            onClick={generateContent}
                            disabled={!prompt.trim() || loading || !provider}
                            className="btn-primary w-full inline-flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {loading ? (
                                <>
                                    <iconify-icon
                                        icon="mdi:loading"
                                        width="16"
                                        height="16"
                                        class="animate-spin mr-2"
                                    ></iconify-icon>
                                    {__("Generating...")}
                                </>
                            ) : (
                                <>
                                    <iconify-icon
                                        icon="mdi:lightning-bolt"
                                        width="16"
                                        height="16"
                                        class="mr-2"
                                    ></iconify-icon>
                                    {__("Generate Content")}
                                </>
                            )}
                        </button>
                    </div>
                )
            }
        >
            <div className="px-5 py-5 space-y-4">
                {loadingProviders ? (
                    <div className="flex items-center justify-center py-12">
                        <iconify-icon
                            icon="mdi:loading"
                            width="24"
                            height="24"
                            class="animate-spin text-gray-400"
                        ></iconify-icon>
                        <span className="ml-2 text-gray-500">
                            {__("Loading providers...")}
                        </span>
                    </div>
                ) : !isConfigured ? (
                    <div className="py-12 text-center">
                        <iconify-icon
                            icon="mdi:alert-circle-outline"
                            width="48"
                            height="48"
                            class="text-amber-500 mx-auto mb-4"
                        ></iconify-icon>
                        <p className="text-gray-600 mb-4">
                            {__(
                                "No AI providers configured. Please configure an AI provider in settings."
                            )}
                        </p>
                        <a
                            href="/admin/settings?tab=integrations"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn-primary inline-flex items-center"
                        >
                            <iconify-icon
                                icon="mdi:cog"
                                width="16"
                                height="16"
                                class="mr-2"
                            ></iconify-icon>
                            {__("Go to Settings")}
                        </a>
                    </div>
                ) : (
                    <>
                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <label
                                    htmlFor="ai_provider"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    {__("AI Provider")}
                                </label>
                                <a
                                    href="/admin/settings?tab=integrations"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-xs text-primary hover:text-primary/80 hover:underline flex items-center"
                                    title={__("Configure AI Settings")}
                                >
                                    <iconify-icon
                                        icon="mdi:cog"
                                        width="12"
                                        height="12"
                                        class="mr-1"
                                    ></iconify-icon>
                                    {__("Settings")}
                                </a>
                            </div>
                            <select
                                id="ai_provider"
                                value={provider}
                                onChange={(e) => setProvider(e.target.value)}
                                className="form-control"
                            >
                                <option value="" disabled>
                                    {__("Select AI Provider")}
                                </option>
                                {Object.entries(providers).map(
                                    ([key, label]) => (
                                        <option key={key} value={key}>
                                            {label}
                                        </option>
                                    )
                                )}
                            </select>
                        </div>

                        <div className="space-y-2">
                            <label
                                htmlFor="ai_prompt"
                                className="block text-sm font-medium text-gray-700"
                            >
                                {__("Describe your content")}
                            </label>
                            <textarea
                                id="ai_prompt"
                                value={prompt}
                                onChange={(e) => setPrompt(e.target.value)}
                                rows={5}
                                placeholder={__(
                                    "Example: Write a blog post about the benefits of AI in modern web development, focusing on productivity and user experience..."
                                )}
                                className="form-control-textarea w-full"
                                maxLength={1000}
                            />
                            <div className="flex justify-between text-xs text-gray-500">
                                <span>
                                    {__(
                                        "Be specific about your content requirements"
                                    )}
                                </span>
                                <span>{prompt.length}/1000</span>
                            </div>
                        </div>

                        {generatedContent && (
                            <div className="space-y-4 border-t border-gray-200 pt-6">
                                <h4 className="text-sm font-medium text-gray-900">
                                    {__("Generated Content Preview")}
                                </h4>

                                {generatedContent.title && (
                                    <div className="space-y-1">
                                        <label className="text-xs font-medium text-gray-700">
                                            {__("Title")}
                                        </label>
                                        <div className="p-3 bg-gray-50 rounded-md border border-gray-200 text-sm">
                                            {generatedContent.title}
                                        </div>
                                    </div>
                                )}

                                {generatedContent.excerpt && (
                                    <div className="space-y-1">
                                        <label className="text-xs font-medium text-gray-700">
                                            {__("Excerpt")}
                                        </label>
                                        <div className="p-3 bg-gray-50 rounded-md border border-gray-200 text-sm">
                                            {generatedContent.excerpt}
                                        </div>
                                    </div>
                                )}

                                {generatedContent.content && (
                                    <div className="space-y-1">
                                        <label className="text-xs font-medium text-gray-700">
                                            {__("Content")}
                                        </label>
                                        <div
                                            className="p-3 bg-gray-50 rounded-md border border-gray-200 text-sm max-h-48 overflow-y-auto"
                                            dangerouslySetInnerHTML={{
                                                __html: generatedContent.content.replace(
                                                    /\n/g,
                                                    "<br>"
                                                ),
                                            }}
                                        />
                                    </div>
                                )}
                            </div>
                        )}

                        {errorMessage && (
                            <div className="p-3 bg-red-50 border border-red-200 rounded-md">
                                <p className="text-sm text-red-700">
                                    {errorMessage}
                                </p>
                            </div>
                        )}
                    </>
                )}
            </div>
        </SideDrawer>
    );
}

export default AIContentModal;
