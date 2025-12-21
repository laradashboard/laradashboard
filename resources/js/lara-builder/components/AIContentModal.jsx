/**
 * AIContentModal - AI Content Generation Modal for LaraBuilder
 *
 * Provides AI-powered content generation functionality within the builder.
 * Supports multiple AI providers (OpenAI, Claude) and generates structured content.
 */

import { useState, useEffect, useCallback } from "react";
import { __ } from "@lara-builder/i18n";
import { blockRegistry } from "../registry/BlockRegistry";

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

    // Fetch available providers on mount
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
                // Handle case where data.data might be a JSON string
                let content = data.data;
                if (typeof content === "string") {
                    try {
                        content = JSON.parse(content);
                    } catch (e) {
                        // If parsing fails, try to extract from the string
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

    const insertContent = useCallback(() => {
        if (!generatedContent) return;

        // Insert title if in post context and setTitle is available
        if (isPostContext && setTitle && generatedContent.title) {
            setTitle(generatedContent.title);
        }

        // Insert excerpt if in post context and setExcerpt is available
        if (isPostContext && setExcerpt && generatedContent.excerpt) {
            setExcerpt(generatedContent.excerpt);
        }

        // Create blocks from content
        if (generatedContent.content && onInsertContent) {
            const blocks = [];

            // Add heading block with title (if not in post context or as a fallback)
            if (generatedContent.title && !isPostContext) {
                const headingBlock = blockRegistry.createInstance("heading", {
                    content: generatedContent.title,
                    level: "h1",
                    align: "left",
                });
                if (headingBlock) blocks.push(headingBlock);
            }

            // Parse content and create appropriate blocks
            const content = generatedContent.content;

            // Check if content has HTML
            if (content.includes("<") && content.includes(">")) {
                // Create a single text-editor block with HTML content
                const textEditorBlock = blockRegistry.createInstance(
                    "text-editor",
                    {
                        content: content,
                    }
                );
                if (textEditorBlock) blocks.push(textEditorBlock);
            } else {
                // Split by double line breaks to create paragraphs
                const paragraphs = content
                    .split(/\n\n+/)
                    .map((p) => p.trim())
                    .filter((p) => p.length > 0);

                paragraphs.forEach((paragraph) => {
                    // Convert single line breaks to <br> within paragraphs
                    const formattedContent = paragraph.replace(/\n/g, "<br>");

                    const textBlock = blockRegistry.createInstance("text", {
                        content: formattedContent,
                        align: "left",
                    });
                    if (textBlock) blocks.push(textBlock);
                });
            }

            // Call the insert callback with the blocks
            if (blocks.length > 0) {
                onInsertContent(blocks);
            }
        }

        // Reset and close
        handleClose();
    }, [
        generatedContent,
        isPostContext,
        setTitle,
        setExcerpt,
        onInsertContent,
    ]);

    const handleClose = () => {
        setPrompt("");
        setGeneratedContent(null);
        setErrorMessage("");
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-black/20 p-4 backdrop-blur-sm"
            onClick={(e) => {
                if (e.target === e.currentTarget) handleClose();
            }}
        >
            <div
                className="flex max-w-2xl w-full max-h-[95vh] flex-col overflow-hidden rounded-lg border border-gray-200 bg-white text-gray-900 shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Modal Header */}
                <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div className="flex items-center space-x-3">
                        <div className="flex items-center justify-center w-8 h-8 rounded-full text-white" style={{ backgroundColor: 'var(--color-primary, #635bff)' }}>
                            <iconify-icon
                                icon="mdi:lightning-bolt"
                                width="16"
                                height="16"
                            ></iconify-icon>
                        </div>
                        <h3 className="text-lg font-semibold text-gray-900">
                            {__("AI Content Generator")}
                        </h3>
                    </div>
                    <button
                        type="button"
                        onClick={handleClose}
                        className="text-gray-400 hover:text-gray-600 transition-colors"
                        title={__("Close")}
                    >
                        <iconify-icon
                            icon="mdi:close"
                            width="24"
                            height="24"
                        ></iconify-icon>
                    </button>
                </div>

                {/* Modal Body */}
                <div className="px-6 pb-6 space-y-4 flex-1 overflow-y-auto">
                    {loadingProviders ? (
                        <div className="flex items-center justify-center py-8">
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
                        <div className="py-8 text-center">
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
                                className="btn-primary"
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
                            {/* AI Provider Selection */}
                            <div className="space-y-2 pt-4">
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
                                    onChange={(e) =>
                                        setProvider(e.target.value)
                                    }
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

                            {/* Prompt Input */}
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
                                    rows={4}
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

                            {/* Generate Button */}
                            <div className="flex justify-center pt-2">
                                <button
                                    type="button"
                                    onClick={generateContent}
                                    disabled={!prompt.trim() || loading}
                                    className="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                    style={{ backgroundColor: 'var(--color-primary, #635bff)' }}
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

                            {/* Generated Content Preview */}
                            {generatedContent && (
                                <div className="mt-6 space-y-4 border-t border-gray-200 pt-6">
                                    <h4 className="text-sm font-medium text-gray-900">
                                        {__("Generated Content Preview")}
                                    </h4>

                                    {/* Title Preview */}
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

                                    {/* Excerpt Preview */}
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

                                    {/* Content Preview */}
                                    {generatedContent.content && (
                                        <div className="space-y-1">
                                            <label className="text-xs font-medium text-gray-700">
                                                {__("Content")}
                                            </label>
                                            <div
                                                className="p-3 bg-gray-50 rounded-md border border-gray-200 text-sm max-h-32 overflow-y-auto"
                                                dangerouslySetInnerHTML={{
                                                    __html: generatedContent.content.replace(
                                                        /\n/g,
                                                        "<br>"
                                                    ),
                                                }}
                                            />
                                        </div>
                                    )}

                                    {/* Action Buttons */}
                                    <div className="flex justify-end space-x-3 pt-4">
                                        <button
                                            type="button"
                                            onClick={handleClose}
                                            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                                        >
                                            {__("Cancel")}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={insertContent}
                                            className="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-md transition-all duration-200"
                                        >
                                            {__("Insert Content")}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Error Message */}
                            {errorMessage && (
                                <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                    <p className="text-sm text-red-700">
                                        {errorMessage}
                                    </p>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

export default AIContentModal;
