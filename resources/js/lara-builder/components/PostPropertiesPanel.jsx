/**
 * PostPropertiesPanel - Properties panel for post editing
 *
 * Shows post-specific fields when no block is selected, or
 * reuses the shared PropertiesPanel block editors when a block is selected.
 */

import { useState } from "react";
import PropertiesPanel from "./PropertiesPanel";
import LayoutStylesSection from "./LayoutStylesSection";
import TaxonomySection from "./TaxonomySection";
import { __ } from "@lara-builder/i18n";
import { mediaLibrary } from "../services/MediaLibraryService";

const PostPropertiesPanel = ({
    selectedBlock,
    onUpdate,
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
    removeFeaturedImage,
    setRemoveFeaturedImage,
    taxonomies,
    parentPosts,
    postTypeModel,
    statuses,
    postData,
    postType,
}) => {
    const [showSlugEdit, setShowSlugEdit] = useState(false);
    const [copied, setCopied] = useState(false);

    const url = `${window.location.origin}/admin/posts/${postType || "page"}/${
        postData?.id || slug || postData?.slug
    }`;

    // Handle copy URL with visual feedback
    const handleCopyUrl = () => {
        navigator.clipboard.writeText(url);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    // Handle featured image selection from media library
    const handleSelectFeaturedImage = async () => {
        try {
            const file = await mediaLibrary.selectImage();
            if (file) {
                setFeaturedImage(file.url);
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
            {/* Post Details Section */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__(":type Details").replace(
                            ":type",
                            postTypeModel.label_singular
                        )}
                    </span>
                </div>

                {/* Title (shown on mobile, hidden on desktop where it's in header) */}
                <div className="mb-4 md:hidden">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__("Title")}
                    </label>
                    <input
                        type="text"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        className="form-control"
                        placeholder={__("Enter title...")}
                    />
                </div>

                {/* Slug */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__("Slug")}
                    </label>
                    <div className="flex gap-2">
                        {showSlugEdit ? (
                            <input
                                type="text"
                                value={slug}
                                onChange={(e) => setSlug(e.target.value)}
                                className="form-control flex-1"
                                placeholder="post-slug"
                            />
                        ) : (
                            <div className="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-600 truncate">
                                {slug || (
                                    <span className="text-gray-400 italic">
                                        {__("auto-generated")}
                                    </span>
                                )}
                            </div>
                        )}
                        <button
                            type="button"
                            onClick={() => setShowSlugEdit(!showSlugEdit)}
                            className="btn-default px-3 py-2 text-xs"
                        >
                            {showSlugEdit ? __("OK") : __("Edit")}
                        </button>
                        <button
                            type="button"
                            onClick={generateSlug}
                            className="btn-default px-3 py-2 text-xs"
                            title={__("Generate from title")}
                        >
                            <iconify-icon
                                icon="mdi:refresh"
                                width="16"
                                height="16"
                            ></iconify-icon>
                        </button>
                    </div>

                    {/* View URL */}
                    {(postData?.id || slug) && (
                        <div className="mt-2">
                            <label className="block text-xs font-medium text-gray-500 mb-1">
                                {__("Permalink")}
                            </label>
                            <div className="flex items-center gap-2">
                                <div className="flex-1 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs text-gray-500 truncate font-mono">
                                    {url}
                                </div>
                                {postData?.id && slug && (
                                    <a
                                        href={url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="btn-default px-2 py-1.5 text-xs"
                                        title={__("View page")}
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
                                    className={`btn-default px-2 py-1.5 text-xs ${
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
                        </div>
                    )}
                </div>
            </div>

            {/* Status Section */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__("Status & Visibility")}
                    </span>
                </div>

                {/* Status */}
                <div className="mb-4">
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
                </div>

                {/* Publish Date (for scheduled posts) */}
                {status === "scheduled" && (
                    <div className="mb-4">
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
