/**
 * BuilderHeader - Header component for LaraBuilder
 *
 * Three-zone layout: navigation/tools | document title | actions
 */

import { useState, useMemo } from "react";
import { __ } from "@lara-builder/i18n";
import EditorOptionsMenu from "./EditorOptionsMenu";
import AIContentModal from "./AIContentModal";
import SeoDrawer from "./SeoDrawer";
import { analyzeSeo, getScoreColor } from "../utils/seoAnalyzer";

const PREVIEW_MODES = [
    { id: "desktop", icon: "mdi:monitor", label: "Desktop" },
    { id: "tablet", icon: "mdi:tablet", label: "Tablet" },
    { id: "mobile", icon: "mdi:cellphone", label: "Mobile" },
];

function BuilderHeader({
    listUrl,
    isFormDirty,
    labels,
    isPostContext,
    isEmailContext,
    templateData,
    postData,
    postTypeModel,
    canUndo,
    canRedo,
    undo,
    redo,
    title,
    setTitle,
    titleInputRef,
    titleError,
    excerpt,
    setExcerpt,
    templateName,
    setTemplateName,
    templateNameInputRef,
    templateNameError,
    saving,
    onSave,
    editorMode,
    onEditorModeChange,
    onCopyAllBlocks,
    onPasteBlocks,
    onInsertAIContent,
    previewMode,
    setPreviewMode,
    status,
    slug,
    seoTitle,
    setSeoTitle,
    seoDescription,
    setSeoDescription,
    seoKeywords,
    setSeoKeywords,
    seoOgTitle,
    setSeoOgTitle,
    seoOgDescription,
    setSeoOgDescription,
    seoCanonical,
    setSeoCanonical,
    seoNoindex,
    setSeoNoindex,
    seoNofollow,
    setSeoNofollow,
    seoSchemaType,
    setSeoSchemaType,
    contentHtml,
    postType,
    contentLength,
    blockCount,
    showToast,
    onFocusTitle,
    featuredImage,
    removeFeaturedImage,
}) {
    const [aiModalOpen, setAiModalOpen] = useState(false);
    const [seoDrawerOpen, setSeoDrawerOpen] = useState(false);

    const seoAnalysis = useMemo(() => {
        if (!isPostContext) return null;

        return analyzeSeo({
            title,
            seoTitle,
            seoDescription,
            seoKeywords,
            seoOgTitle,
            seoOgDescription,
            seoCanonical,
            seoNoindex,
            seoNofollow,
            seoSchemaType,
            slug,
            excerpt,
            contentLength,
            blockCount,
        });
    }, [
        isPostContext,
        title,
        seoTitle,
        seoDescription,
        seoKeywords,
        seoOgTitle,
        seoOgDescription,
        seoCanonical,
        seoNoindex,
        seoNofollow,
        seoSchemaType,
        slug,
        excerpt,
        contentLength,
        blockCount,
    ]);

    const seoScoreColors = seoAnalysis
        ? getScoreColor(seoAnalysis.rating)
        : null;

    const saveStatusLabel = useMemo(() => {
        if (!isPostContext) {
            return null;
        }

        if (saving) {
            return __("Saving...");
        }

        if (isFormDirty) {
            return __("Unsaved");
        }

        return null;
    }, [isPostContext, saving, isFormDirty]);

    const saveStatusClassName = saving
        ? "text-gray-600 bg-gray-50 border-gray-200"
        : "text-amber-700 bg-amber-50 border-amber-100";

    const getBackIcon = () => {
        if (isEmailContext) return "lucide:mail";
        if (isPostContext) return postTypeModel?.icon || "lucide:file-text";
        return "lucide:file-text";
    };

    const isNewPost = isPostContext && !postData?.id;
    const pageLabel = isPostContext
        ? postTypeModel?.label_singular || labels.title.split(" ")[0]
        : labels.title.split(" ")[0];

    const getPrimaryButtonLabel = () => {
        if (!isPostContext) return labels.saveText;
        if (isNewPost || status === "draft" || status === "pending") {
            return __("Publish");
        }
        if (status === "scheduled") return __("Schedule");
        return __("Update");
    };

    const getPrimaryStatusOverride = () => {
        if (!isPostContext) return undefined;
        if (isNewPost || status === "draft" || status === "pending") {
            return "published";
        }
        return undefined;
    };

    const getSecondaryLabel = () => __("Save draft");

    const showDraftButton =
        isPostContext &&
        (isNewPost || status === "draft" || status === "pending");

    const handleSaveDraft = () => {
        if (!saving && title.trim()) {
            onSave("draft", { preferInPlaceNavigation: true });
        }
    };

    const canSaveDraft = Boolean(title.trim()) && !saving;

    const iconButtonClass =
        "inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed";

    const postToolButtons = isPostContext ? (
        <>
            <button
                type="button"
                onClick={() => setAiModalOpen(true)}
                className="inline-flex items-center justify-center w-8 h-8 shrink-0 text-white rounded-lg transition-opacity hover:opacity-90"
                style={{
                    backgroundColor: "var(--color-primary, #635bff)",
                }}
                title={__("Generate content with AI")}
            >
                <iconify-icon
                    icon="mdi:lightning-bolt"
                    width="16"
                    height="16"
                ></iconify-icon>
            </button>
            <button
                type="button"
                onClick={() => setSeoDrawerOpen(true)}
                className="relative inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                title={`${__("SEO")} — ${seoAnalysis?.score ?? 0}%`}
            >
                <iconify-icon
                    icon="mdi:chart-line"
                    width="16"
                    height="16"
                ></iconify-icon>
                {seoAnalysis && (
                    <span
                        className={`absolute -top-1 -right-1 min-w-[1rem] h-4 px-0.5 rounded-full text-[9px] font-bold text-white leading-none flex items-center justify-center ${seoScoreColors.badge}`}
                    >
                        {seoAnalysis.score}
                    </span>
                )}
            </button>
        </>
    ) : null;

    return (
        <header className="bg-white border-b border-gray-200 px-2 sm:px-3 py-2 flex items-center gap-2 sm:gap-3 shadow-sm flex-shrink-0 min-h-[3rem]">
            {/* Left: navigation + history */}
            <div className="flex items-center gap-1 shrink-0">
                {listUrl && (
                    <a
                        href={listUrl}
                        onClick={(e) => {
                            if (isFormDirty) {
                                const confirmed = window.confirm(
                                    __(
                                        "You have unsaved changes. Are you sure you want to leave?"
                                    )
                                );
                                if (!confirmed) {
                                    e.preventDefault();
                                }
                            }
                        }}
                        className={iconButtonClass}
                        title={__("Go back")}
                    >
                        <iconify-icon
                            icon={getBackIcon()}
                            width="18"
                            height="18"
                        ></iconify-icon>
                    </a>
                )}

                <div className="hidden sm:flex items-center">
                    <button
                        type="button"
                        onClick={undo}
                        disabled={!canUndo}
                        className={`${iconButtonClass} border-transparent`}
                        title={__("Undo (Ctrl+Z)")}
                    >
                        <iconify-icon
                            icon="mdi:undo"
                            width="17"
                            height="17"
                        ></iconify-icon>
                    </button>
                    <button
                        type="button"
                        onClick={redo}
                        disabled={!canRedo}
                        className={`${iconButtonClass} border-transparent -ml-1`}
                        title={__("Redo (Ctrl+Shift+Z)")}
                    >
                        <iconify-icon
                            icon="mdi:redo"
                            width="17"
                            height="17"
                        ></iconify-icon>
                    </button>
                </div>

                {saveStatusLabel && (
                    <span
                        className={`hidden lg:inline-flex text-xs border px-2 py-0.5 rounded-md font-medium ${saveStatusClassName}`}
                        title={saveStatusLabel}
                    >
                        {saveStatusLabel}
                    </span>
                )}
            </div>

            {/* Center: document title + content tools (desktop) */}
            <div className="flex-1 min-w-0 hidden md:flex items-center gap-1.5 max-w-3xl">
                {isPostContext ? (
                    <>
                        <input
                            ref={titleInputRef}
                            type="text"
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                            placeholder={__(":type title...").replace(
                                ":type",
                                postTypeModel?.label_singular || "Post"
                            )}
                            className={`form-control flex-1 min-w-0 text-sm py-1.5 ${
                                titleError
                                    ? "border-red-500 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-100"
                                    : ""
                            }`}
                            aria-invalid={titleError ? "true" : undefined}
                            title={titleError || title || undefined}
                        />
                        {postToolButtons}
                    </>
                ) : isEmailContext ? (
                    <input
                        ref={templateNameInputRef}
                        type="text"
                        value={templateName}
                        onChange={(e) => setTemplateName(e.target.value)}
                        placeholder={__("Template name...")}
                        className={`form-control w-full max-w-2xl text-sm py-1.5 ${
                            templateNameError
                                ? "border-red-500 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-100"
                                : ""
                        }`}
                        aria-invalid={templateNameError ? "true" : undefined}
                        title={templateNameError || templateName || undefined}
                    />
                ) : (
                    <span className="text-sm font-medium text-gray-700 truncate block">
                        {labels.title}
                    </span>
                )}
            </div>

            {/* Mobile: label + content tools (title field lives in sidebar) */}
            <div className="md:hidden flex items-center gap-1.5 min-w-0 flex-1">
                <p className="text-sm font-semibold text-gray-800 truncate min-w-0 flex-1">
                    {templateData?.uuid || postData?.id ? __("Edit") : __("Create")}{" "}
                    {pageLabel}
                </p>
                {postToolButtons}
            </div>

            {/* Right: preview + save */}
            <div className="flex items-center gap-1.5 shrink-0 ml-auto">
                {previewMode !== undefined && (
                    <div
                        className="hidden lg:flex items-center bg-gray-100 rounded-lg border border-gray-200 p-0.5"
                        role="group"
                        aria-label={__("Preview device")}
                    >
                        {PREVIEW_MODES.map((mode) => (
                            <button
                                key={mode.id}
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setPreviewMode(mode.id);
                                }}
                                className={`inline-flex items-center justify-center w-8 h-7 rounded-md transition-colors ${
                                    previewMode === mode.id
                                        ? "bg-primary text-white shadow-sm"
                                        : "text-gray-500 hover:bg-gray-200 hover:text-gray-700"
                                }`}
                                title={__(mode.label + " Preview")}
                            >
                                <iconify-icon
                                    icon={mode.icon}
                                    width="16"
                                    height="16"
                                ></iconify-icon>
                            </button>
                        ))}
                    </div>
                )}

                <div className="hidden sm:block w-px h-6 bg-gray-200" />

                {showDraftButton && (
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            handleSaveDraft();
                        }}
                        disabled={!canSaveDraft}
                        className="inline-flex px-2 sm:px-3 py-1.5 text-sm font-medium rounded-lg btn-default disabled:opacity-50 disabled:cursor-not-allowed"
                        title={
                            !title.trim()
                                ? __("Add a title to save")
                                : getSecondaryLabel()
                        }
                    >
                        {saving ? (
                            __("Saving...")
                        ) : (
                            <>
                                <span className="hidden sm:inline">
                                    {getSecondaryLabel()}
                                </span>
                                <span className="sm:hidden">{__("Draft")}</span>
                            </>
                        )}
                    </button>
                )}

                {isPostContext ? (
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onSave(getPrimaryStatusOverride(), {
                                preferInPlaceNavigation: true,
                            });
                        }}
                        disabled={saving}
                        className={`px-3 sm:px-4 py-1.5 text-sm font-medium rounded-lg ${
                            saving
                                ? "btn-default cursor-not-allowed"
                                : "btn-primary"
                        }`}
                    >
                        {saving ? (
                            <span className="inline-flex items-center gap-2">
                                <svg
                                    className="animate-spin h-4 w-4"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        className="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        strokeWidth="4"
                                        fill="none"
                                    />
                                    <path
                                        className="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                <span className="hidden sm:inline">
                                    {__("Saving...")}
                                </span>
                            </span>
                        ) : (
                            getPrimaryButtonLabel()
                        )}
                    </button>
                ) : (
                    <button
                        type="button"
                        onClick={onSave}
                        disabled={saving}
                        className={`px-3 sm:px-4 py-1.5 text-sm font-medium rounded-lg ${
                            saving
                                ? "btn-default cursor-not-allowed"
                                : "btn-primary"
                        }`}
                    >
                        {saving ? __("Saving...") : labels.saveText}
                    </button>
                )}

                <EditorOptionsMenu
                    editorMode={editorMode}
                    onEditorModeChange={onEditorModeChange}
                    onCopyAllBlocks={onCopyAllBlocks}
                    onPasteBlocks={onPasteBlocks}
                />
            </div>

            <AIContentModal
                isOpen={aiModalOpen}
                onClose={() => setAiModalOpen(false)}
                onInsertContent={onInsertAIContent}
                isPostContext={isPostContext}
                setTitle={setTitle}
                setExcerpt={setExcerpt}
            />

            {isPostContext && (
                <SeoDrawer
                    isOpen={seoDrawerOpen}
                    onClose={() => setSeoDrawerOpen(false)}
                    title={title}
                    slug={slug}
                    excerpt={excerpt}
                    contentHtml={contentHtml}
                    seoTitle={seoTitle}
                    setSeoTitle={setSeoTitle}
                    seoDescription={seoDescription}
                    setSeoDescription={setSeoDescription}
                    seoKeywords={seoKeywords}
                    setSeoKeywords={setSeoKeywords}
                    seoOgTitle={seoOgTitle}
                    setSeoOgTitle={setSeoOgTitle}
                    seoOgDescription={seoOgDescription}
                    setSeoOgDescription={setSeoOgDescription}
                    seoCanonical={seoCanonical}
                    setSeoCanonical={setSeoCanonical}
                    seoNoindex={seoNoindex}
                    setSeoNoindex={setSeoNoindex}
                    seoNofollow={seoNofollow}
                    setSeoNofollow={setSeoNofollow}
                    seoSchemaType={seoSchemaType}
                    setSeoSchemaType={setSeoSchemaType}
                    postData={postData}
                    postType={postType}
                    contentLength={contentLength}
                    blockCount={blockCount}
                    showToast={showToast}
                    onFocusTitle={onFocusTitle}
                    featuredImage={featuredImage}
                    removeFeaturedImage={removeFeaturedImage}
                />
            )}
        </header>
    );
}

export default BuilderHeader;
