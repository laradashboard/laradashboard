/**
 * PostPropertiesPanel - Properties panel for post editing
 *
 * Shows post-specific fields when no block is selected, or
 * reuses the shared PropertiesPanel block editors when a block is selected.
 */

import { useState, useMemo } from "react";
import PropertiesPanel from "./PropertiesPanel";
import LayoutStylesSection from "./LayoutStylesSection";
import TaxonomySection from "./TaxonomySection";
import { __ } from "@lara-builder/i18n";
import { mediaLibrary } from "../services/MediaLibraryService";

const PostPropertiesPanel = ({
    selectedBlock,
    onUpdate,
    onReplaceBlock,
    onImageUpload,
    onVideoUpload,
    canvasSettings,
    onCanvasSettingsUpdate,
    // Post-specific props
    title,
    setTitle,
    slug,
    setSlug,
    generateSlug,
    status,
    setStatus,
    excerpt,
    setExcerpt,
    publishedAt,
    setPublishedAt,
    parentId,
    setParentId,
    selectedTerms,
    setSelectedTerms,
    featuredImage,
    setFeaturedImage,
    setFeaturedImageId,
    removeFeaturedImage,
    setRemoveFeaturedImage,
    taxonomies,
    parentPosts,
    postTypeModel,
    statuses,
    postData,
    postType,
    titleInputRef,
    titleError,
    lastSavedAt,
    saving,
    isFormDirty,
}) => {
    const [copied, setCopied] = useState(false);

    const frontendUrl = postData?.frontend_url;
    const previewUrl =
        postData?.preview_url ||
        (postData?.id
            ? `${window.location.origin}/admin/posts/${postType || "page"}/${postData.id}`
            : null);

    const slugPreviewUrl = useMemo(() => {
        if (!slug) {
            return null;
        }

        const basePath =
            postType === "page" ? "" : "post/";

        return `${window.location.origin}/${basePath}${slug}`;
    }, [slug, postType]);

    const displayUrl = frontendUrl || slugPreviewUrl || previewUrl;

    const lastEditedLabel = useMemo(() => {
        if (saving) {
            return __("Saving...");
        }

        if (isFormDirty) {
            return __("Unsaved changes.");
        }

        if (!lastSavedAt) {
            return null;
        }

        const seconds = Math.floor((Date.now() - lastSavedAt.getTime()) / 1000);

        if (seconds < 60) {
            return __("Last edited a moment ago.");
        }

        if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);

            return minutes === 1
                ? __("Last edited a minute ago.")
                : __("Last edited :count minutes ago.").replace(
                      ":count",
                      String(minutes)
                  );
        }

        return __("Last edited :time.").replace(
            ":time",
            lastSavedAt.toLocaleString([], {
                dateStyle: "medium",
                timeStyle: "short",
            })
        );
    }, [lastSavedAt, saving, isFormDirty]);

    // Handle copy URL with visual feedback
    const handleCopyUrl = () => {
        if (displayUrl) {
            navigator.clipboard.writeText(displayUrl);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    // Handle featured image selection from media library
    const handleSelectFeaturedImage = async () => {
        try {
            const file = await mediaLibrary.selectImage();
            if (file) {
                setFeaturedImage(file.url);
                setFeaturedImageId(String(file.id));
                setRemoveFeaturedImage(false);
            }
        } catch (error) {
            // Selection cancelled - do nothing
        }
    };

    // Toggle taxonomy term selection
    const handleTermToggle = (taxonomyName, termId) => {
        setSelectedTerms((prev) => {
            const currentTerms = prev[taxonomyName] || [];
            const newTerms = currentTerms.includes(termId)
                ? currentTerms.filter((id) => id !== termId)
                : [...currentTerms, termId];
            return { ...prev, [taxonomyName]: newTerms };
        });
    };

    // If a block is selected, delegate to the shared PropertiesPanel for block editing
    if (selectedBlock) {
        return (
            <PropertiesPanel
                selectedBlock={selectedBlock}
                onUpdate={onUpdate}
                onReplaceBlock={onReplaceBlock}
                onImageUpload={onImageUpload}
                onVideoUpload={onVideoUpload}
                canvasSettings={canvasSettings}
                onCanvasSettingsUpdate={onCanvasSettingsUpdate}
            />
        );
    }

    // Show post settings when no block is selected
    return (
        <div className="h-full overflow-y-auto px-1">
            <div className="mb-6">
                <div className="mb-3 pb-2 border-b border-gray-200 flex items-center justify-between gap-2">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {postTypeModel.label_singular}
                    </span>
                    {lastEditedLabel && (
                        <span className="text-xs text-gray-400 truncate">
                            {lastEditedLabel}
                        </span>
                    )}
                </div>

                {/* Title (mobile only — desktop uses header) */}
                <div className="mb-3 md:hidden">
                    <label
                        htmlFor="lara-builder-sidebar-title"
                        className="block text-sm font-medium text-gray-700 mb-1"
                    >
                        {__("Title")}
                    </label>
                    <input
                        ref={titleInputRef}
                        id="lara-builder-sidebar-title"
                        type="text"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        className={`form-control ${
                            titleError
                                ? "border-red-500 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-100"
                                : ""
                        }`}
                        placeholder={__("Enter title...")}
                        aria-invalid={titleError ? "true" : undefined}
                        title={titleError || undefined}
                    />
                </div>

                <div className="space-y-3">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            {__("Status")}
                        </label>
                        <select
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                            className="form-control"
                        >
                            {Object.entries(statuses).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                        {(status === "published" || status === "private") && (
                            <p className="text-xs text-gray-400 mt-1">
                                {__(
                                    "Use Update to save while live. Set Draft to unpublish."
                                )}
                            </p>
                        )}
                    </div>

                    {status === "scheduled" && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                {__("Publish Date")}
                            </label>
                            <input
                                type="datetime-local"
                                value={publishedAt}
                                onChange={(e) => setPublishedAt(e.target.value)}
                                className="form-control"
                            />
                        </div>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            {__("Slug")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="text"
                                value={slug}
                                onChange={(e) => setSlug(e.target.value)}
                                className="form-control flex-1 min-w-0"
                                placeholder={__("auto-generated from title")}
                            />
                            <button
                                type="button"
                                onClick={generateSlug}
                                className="btn-default px-3 py-2 shrink-0"
                                title={__("Generate from title")}
                            >
                                <iconify-icon
                                    icon="mdi:refresh"
                                    width="16"
                                    height="16"
                                ></iconify-icon>
                            </button>
                        </div>
                    </div>

                    {displayUrl && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                {__("Permalink")}
                            </label>
                            <div className="flex items-center gap-2">
                                <div className="flex-1 min-w-0 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-xs text-gray-500 truncate font-mono">
                                    {displayUrl}
                                </div>
                                {postData?.id && frontendUrl && (
                                    <a
                                        href={displayUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="btn-default px-2 py-2 shrink-0"
                                        title={__("View")}
                                    >
                                        <iconify-icon
                                            icon="mdi:open-in-new"
                                            width="14"
                                            height="14"
                                        ></iconify-icon>
                                    </a>
                                )}
                                <button
                                    type="button"
                                    onClick={handleCopyUrl}
                                    className={`btn-default px-2 py-2 shrink-0 ${
                                        copied ? "text-green-600" : ""
                                    }`}
                                    title={
                                        copied ? __("Copied!") : __("Copy URL")
                                    }
                                >
                                    <iconify-icon
                                        icon={
                                            copied
                                                ? "mdi:check"
                                                : "mdi:content-copy"
                                        }
                                        width="14"
                                        height="14"
                                    ></iconify-icon>
                                </button>
                            </div>
                            {previewUrl &&
                                frontendUrl &&
                                previewUrl !== frontendUrl && (
                                    <a
                                        href={previewUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-block mt-1 text-xs text-gray-400 hover:text-gray-600"
                                    >
                                        {__("Preview")} &rarr;
                                    </a>
                                )}
                        </div>
                    )}
                </div>
            </div>

            {/* Featured Image Section */}
            {postTypeModel.supports_thumbnail && (
                <div className="mb-6">
                    <div className="mb-4 pb-2 border-b border-gray-200">
                        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {__("Featured Image")}
                        </span>
                    </div>

                    <div className="space-y-2">
                        {featuredImage && !removeFeaturedImage ? (
                            <div className="relative group">
                                <img
                                    src={featuredImage}
                                    alt="Featured"
                                    className="w-full h-32 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
                                />
                                <button
                                    type="button"
                                    onClick={() => {
                                        setFeaturedImage("");
                                        setFeaturedImageId("");
                                        setRemoveFeaturedImage(true);
                                    }}
                                    className="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    title={__("Remove image")}
                                >
                                    <iconify-icon
                                        icon="mdi:close"
                                        width="14"
                                        height="14"
                                    ></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div
                                onClick={handleSelectFeaturedImage}
                                className="flex flex-col items-center justify-center w-full h-32 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <iconify-icon
                                    icon="mdi:image-plus"
                                    className="text-3xl text-gray-400 mb-2"
                                ></iconify-icon>
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {__("Click to select image")}
                                </p>
                            </div>
                        )}

                        {featuredImage && !removeFeaturedImage && (
                            <button
                                type="button"
                                onClick={handleSelectFeaturedImage}
                                className="btn btn-default w-full flex items-center justify-center gap-2"
                            >
                                <iconify-icon
                                    icon="mdi:image-edit"
                                    width="16"
                                    height="16"
                                ></iconify-icon>
                                {__("Change Image")}
                            </button>
                        )}
                    </div>
                </div>
            )}

            {/* Excerpt Section */}
            {postTypeModel.supports_excerpt && (
                <div className="mb-6">
                    <div className="mb-4 pb-2 border-b border-gray-200">
                        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {__("Excerpt")}
                        </span>
                    </div>

                    <textarea
                        value={excerpt}
                        onChange={(e) => setExcerpt(e.target.value)}
                        rows={3}
                        className="form-control-textarea"
                        placeholder={__("A short summary of the content...")}
                    />
                    <p className="text-xs text-gray-400 mt-1">
                        {__("Leave empty to auto-generate from content")}
                    </p>
                </div>
            )}

            {/* Parent Post (for hierarchical post types) */}
            {postTypeModel.hierarchical &&
                Object.keys(parentPosts).length > 0 && (
                    <div className="mb-6">
                        <div className="mb-4 pb-2 border-b border-gray-200">
                            <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {__("Parent :type").replace(
                                    ":type",
                                    postTypeModel.label_singular
                                )}
                            </span>
                        </div>

                        <select
                            value={parentId}
                            onChange={(e) => setParentId(e.target.value)}
                            className="form-control"
                        >
                            <option value="">{__("None")}</option>
                            {Object.entries(parentPosts).map(
                                ([id, postTitle]) => (
                                    <option key={id} value={id}>
                                        {postTitle}
                                    </option>
                                )
                            )}
                        </select>
                    </div>
                )}

            {/* Taxonomies */}
            {taxonomies.length > 0 && (
                <TaxonomySection
                    taxonomies={taxonomies}
                    selectedTerms={selectedTerms}
                    onTermToggle={handleTermToggle}
                    postType={postType}
                    postId={postData?.id}
                />
            )}

            {/* Canvas/Content Settings */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__("Content Settings")}
                    </span>
                </div>

                {/* Width */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__("Content Width")}
                    </label>
                    <select
                        value={canvasSettings?.width || "100%"}
                        onChange={(e) =>
                            onCanvasSettingsUpdate({
                                ...canvasSettings,
                                width: e.target.value,
                            })
                        }
                        className="form-control"
                    >
                        <option value="100%">{__("Full Width")}</option>
                        <option value="800px">{__("800px (Narrow)")}</option>
                        <option value="1000px">
                            {__("1000px (Standard)")}
                        </option>
                        <option value="1200px">{__("1200px (Wide)")}</option>
                    </select>
                </div>

                {/* Content Padding */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__("Content Padding")}
                    </label>
                    <select
                        value={canvasSettings?.contentPadding || "24px"}
                        onChange={(e) =>
                            onCanvasSettingsUpdate({
                                ...canvasSettings,
                                contentPadding: e.target.value,
                            })
                        }
                        className="form-control"
                    >
                        <option value="0px">{__("None")}</option>
                        <option value="16px">{__("16px (Compact)")}</option>
                        <option value="24px">{__("24px (Small)")}</option>
                        <option value="32px">{__("32px (Medium)")}</option>
                        <option value="40px">{__("40px (Large)")}</option>
                    </select>
                </div>
            </div>

            {/* Content Layout Styles - Same as blocks */}
            <LayoutStylesSection
                layoutStyles={canvasSettings?.layoutStyles || {}}
                onUpdate={(newLayoutStyles) =>
                    onCanvasSettingsUpdate({
                        ...canvasSettings,
                        layoutStyles: newLayoutStyles,
                    })
                }
                onImageUpload={onImageUpload}
                defaultCollapsed={false}
            />

            <div className="mt-6 pt-4 border-t border-gray-200">
                <p className="text-xs text-gray-400 text-center">
                    {__("Click on a block to edit its properties")}
                </p>
            </div>
        </div>
    );
};

export default PostPropertiesPanel;
