/**
 * AITextAssistant - Inline AI text modification assistant for LaraBuilder
 *
 * Provides quick AI-powered text modifications like formatting, expanding,
 * shortening, fixing grammar, changing tone, etc.
 */

import { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";
import { __ } from "@lara-builder/i18n";

// Quick action presets
const QUICK_ACTIONS = [
    {
        id: "improve",
        label: __("Improve writing"),
        icon: "mdi:auto-fix",
        prompt: "Improve the writing quality of this text while keeping the same meaning. Make it more clear, engaging, and professional.",
    },
    {
        id: "fix_grammar",
        label: __("Fix grammar"),
        icon: "mdi:spellcheck",
        prompt: "Fix any grammar, spelling, and punctuation errors in this text. Keep the original meaning and tone.",
    },
    {
        id: "make_shorter",
        label: __("Make shorter"),
        icon: "mdi:text-short",
        prompt: "Make this text more concise while keeping the key points. Remove unnecessary words and phrases.",
    },
    {
        id: "make_longer",
        label: __("Make longer"),
        icon: "mdi:text-long",
        prompt: "Expand this text with more details and explanations while keeping it relevant and engaging.",
    },
    {
        id: "simplify",
        label: __("Simplify"),
        icon: "mdi:lightbulb-outline",
        prompt: "Simplify this text to make it easier to understand. Use simpler words and shorter sentences.",
    },
    {
        id: "formal",
        label: __("Make formal"),
        icon: "mdi:briefcase-outline",
        prompt: "Rewrite this text in a more formal, professional tone suitable for business communication.",
    },
    {
        id: "casual",
        label: __("Make casual"),
        icon: "mdi:emoticon-happy-outline",
        prompt: "Rewrite this text in a more casual, friendly tone while keeping the message clear.",
    },
    {
        id: "translate",
        label: __("Translate to English"),
        icon: "mdi:translate",
        prompt: "Translate this text to English. Keep the original meaning and tone.",
    },
];

function AITextAssistant({
    isOpen,
    onClose,
    selectedText,
    onApply,
    position = { top: 0, left: 0 },
}) {
    const [loading, setLoading] = useState(false);
    const [customPrompt, setCustomPrompt] = useState("");
    const [result, setResult] = useState("");
    const [error, setError] = useState("");
    const [showCustomInput, setShowCustomInput] = useState(false);
    const [adjustedPosition, setAdjustedPosition] = useState(position);
    const popoverRef = useRef(null);
    const inputRef = useRef(null);

    // Adjust position to keep popup within viewport
    useEffect(() => {
        if (!isOpen || !popoverRef.current) {
            setAdjustedPosition(position);
            return;
        }

        // Small delay to ensure the popup is rendered and we can measure it
        const adjustPosition = () => {
            const popup = popoverRef.current;
            if (!popup) return;

            const rect = popup.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const padding = 16; // Minimum distance from viewport edges

            let newTop = position.top;
            let newLeft = position.left;

            // Check if popup extends beyond bottom of viewport
            if (position.top + rect.height > viewportHeight - padding) {
                // Position above the trigger point instead
                newTop = Math.max(
                    padding,
                    position.top - rect.height - 10
                );
                // If still doesn't fit, position at top with some margin
                if (newTop < padding) {
                    newTop = padding;
                }
            }

            // Check if popup extends beyond right edge
            if (position.left + rect.width > viewportWidth - padding) {
                newLeft = Math.max(
                    padding,
                    viewportWidth - rect.width - padding
                );
            }

            // Check if popup extends beyond left edge
            if (newLeft < padding) {
                newLeft = padding;
            }

            setAdjustedPosition({ top: newTop, left: newLeft });
        };

        // Run on next frame to ensure popup is rendered
        requestAnimationFrame(adjustPosition);

        // Also adjust on window resize
        window.addEventListener("resize", adjustPosition);
        return () => window.removeEventListener("resize", adjustPosition);
    }, [isOpen, position]);

    // Close on click outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                popoverRef.current &&
                !popoverRef.current.contains(event.target)
            ) {
                onClose();
            }
        };

        if (isOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isOpen, onClose]);

    // Focus input when showing custom input
    useEffect(() => {
        if (showCustomInput && inputRef.current) {
            inputRef.current.focus();
        }
    }, [showCustomInput]);

    // Reset state when closing
    useEffect(() => {
        if (!isOpen) {
            setResult("");
            setError("");
            setCustomPrompt("");
            setShowCustomInput(false);
        }
    }, [isOpen]);

    const handleQuickAction = async (action) => {
        await processAIRequest(action.prompt);
    };

    const handleCustomPrompt = async () => {
        if (!customPrompt.trim()) return;
        await processAIRequest(customPrompt);
    };

    const processAIRequest = async (prompt) => {
        if (!selectedText?.trim()) {
            setError(__("No text selected"));
            return;
        }

        setLoading(true);
        setError("");
        setResult("");

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch("/admin/ai/modify-text", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    text: selectedText,
                    instruction: prompt,
                }),
            });

            const data = await response.json();

            if (data.success) {
                setResult(data.data.text || data.data);
            } else {
                setError(data.message || __("Failed to modify text"));
            }
        } catch (err) {
            setError(__("Network error. Please try again."));
            console.error("AI Text Assistant Error:", err);
        } finally {
            setLoading(false);
        }
    };

    const handleApply = () => {
        if (result) {
            onApply(result);
            onClose();
        }
    };

    if (!isOpen) return null;

    // Calculate dynamic max height based on viewport
    const getMaxHeight = () => {
        const viewportHeight = window.innerHeight;
        const padding = 32; // Top and bottom padding
        const availableHeight = viewportHeight - adjustedPosition.top - padding;
        return Math.min(500, Math.max(300, availableHeight));
    };

    // Use portal to render outside the toolbar's DOM tree
    return createPortal(
        <div
            ref={popoverRef}
            className="fixed z-[9999] bg-white rounded-lg shadow-2xl border border-gray-200 w-80 overflow-hidden flex flex-col"
            style={{
                top: adjustedPosition.top,
                left: adjustedPosition.left,
                maxHeight: `${getMaxHeight()}px`,
            }}
            onClick={(e) => e.stopPropagation()}
        >
            {/* Header */}
            <div className="flex items-center justify-between px-3 py-2 border-b border-gray-100 bg-primary/10 shrink-0">
                <div className="flex items-center gap-2">
                    <iconify-icon
                        icon="mdi:lightning-bolt"
                        width="16"
                        height="16"
                        class="text-primary"
                    ></iconify-icon>
                    <span className="text-sm font-medium text-gray-800">
                        {__("AI Assistant")}
                    </span>
                </div>
                <button
                    type="button"
                    onClick={onClose}
                    className="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <iconify-icon
                        icon="mdi:close"
                        width="18"
                        height="18"
                    ></iconify-icon>
                </button>
            </div>

            {/* Content */}
            <div className="p-3 flex-1 overflow-y-auto min-h-0">
                {/* Selected text preview */}
                {selectedText && !result && (
                    <div className="mb-3 p-2 bg-gray-50 rounded-md border border-gray-200">
                        <p className="text-xs text-gray-500 mb-1">
                            {__("Selected text")}:
                        </p>
                        <p className="text-sm text-gray-700 line-clamp-3">
                            {selectedText}
                        </p>
                    </div>
                )}

                {/* Loading state */}
                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <iconify-icon
                            icon="mdi:loading"
                            width="24"
                            height="24"
                            class="animate-spin text-primary"
                        ></iconify-icon>
                        <span className="ml-2 text-sm text-gray-600">
                            {__("Processing...")}
                        </span>
                    </div>
                )}

                {/* Error */}
                {error && !loading && (() => {
                    const errorLower = error.toLowerCase();
                    const isConfigError =
                        errorLower.includes("not configured") ||
                        errorLower.includes("api key") ||
                        errorLower.includes("contact the administrator");

                    return (
                        <div className="mb-3 p-3 bg-red-50 rounded-lg border border-red-200">
                            <div className="flex items-start gap-2">
                                <iconify-icon
                                    icon="mdi:alert-circle"
                                    width="18"
                                    height="18"
                                    class="text-red-500 shrink-0 mt-0.5"
                                ></iconify-icon>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium text-red-700 mb-1">
                                        {__("Error")}
                                    </p>
                                    <p className="text-sm text-red-600 break-words">
                                        {error}
                                    </p>
                                </div>
                            </div>
                            <div className="mt-3 flex gap-2">
                                {/* Show "Go to Settings" button for configuration errors */}
                                {isConfigError && (
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
                                        ></iconify-icon>
                                        {__("Go to Settings")}
                                    </a>
                                )}
                                <button
                                    type="button"
                                    onClick={() => setError("")}
                                    className={`btn-danger ${
                                        isConfigError ? "" : "flex-1"
                                    }`}
                                >
                                    {__("Dismiss")}
                                </button>
                            </div>
                        </div>
                    );
                })()}

                {/* Result preview */}
                {result && !loading && (
                    <div className="mb-3">
                        <p className="text-xs text-gray-500 mb-1">
                            {__("Result")}:
                        </p>
                        <div className="p-2 bg-green-50 rounded-md border border-green-200 mb-3">
                            <p className="text-sm text-gray-700">{result}</p>
                        </div>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={handleApply}
                                className="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md transition-colors"
                            >
                                {__("Apply")}
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setResult("");
                                    setShowCustomInput(false);
                                }}
                                className="px-3 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                            >
                                {__("Try again")}
                            </button>
                        </div>
                    </div>
                )}

                {/* Quick actions */}
                {!loading && !result && (
                    <>
                        <div className="grid grid-cols-2 gap-2 mb-3">
                            {QUICK_ACTIONS.map((action) => (
                                <button
                                    key={action.id}
                                    type="button"
                                    onClick={() => handleQuickAction(action)}
                                    className="flex items-center gap-2 p-2 text-left text-sm text-gray-700 bg-gray-50 hover:bg-primary/10 hover:text-primary rounded-md transition-colors border border-gray-200 hover:border-primary/30"
                                >
                                    <iconify-icon
                                        icon={action.icon}
                                        width="16"
                                        height="16"
                                        class="text-gray-500"
                                    ></iconify-icon>
                                    <span className="truncate">
                                        {action.label}
                                    </span>
                                </button>
                            ))}
                        </div>

                        {/* Custom prompt section */}
                        <div className="border-t border-gray-100 pt-3">
                            {!showCustomInput ? (
                                <button
                                    type="button"
                                    onClick={() => setShowCustomInput(true)}
                                    className="w-full flex items-center justify-center gap-2 p-2 text-sm text-primary hover:opacity-80 hover:bg-primary/10 rounded-md transition-colors"
                                >
                                    <iconify-icon
                                        icon="mdi:pencil"
                                        width="16"
                                        height="16"
                                    ></iconify-icon>
                                    {__("Custom instruction...")}
                                </button>
                            ) : (
                                <div className="space-y-2">
                                    <textarea
                                        ref={inputRef}
                                        value={customPrompt}
                                        onChange={(e) =>
                                            setCustomPrompt(e.target.value)
                                        }
                                        placeholder={__(
                                            "e.g., Make it sound more exciting..."
                                        )}
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                        rows={2}
                                        onKeyDown={(e) => {
                                            if (
                                                e.key === "Enter" &&
                                                !e.shiftKey
                                            ) {
                                                e.preventDefault();
                                                handleCustomPrompt();
                                            }
                                        }}
                                    />
                                    <div className="flex gap-2">
                                        <button
                                            type="button"
                                            onClick={handleCustomPrompt}
                                            disabled={!customPrompt.trim()}
                                            className="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-primary hover:opacity-90 disabled:bg-gray-300 disabled:cursor-not-allowed rounded-md transition-colors"
                                        >
                                            {__("Apply")}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowCustomInput(false)
                                            }
                                            className="px-3 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                                        >
                                            {__("Cancel")}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </>
                )}
            </div>
        </div>,
        document.body
    );
}

export default AITextAssistant;
