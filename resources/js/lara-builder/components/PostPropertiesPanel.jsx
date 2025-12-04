/**
 * PostPropertiesPanel - Properties panel for post editing
 *
 * Shows post-specific fields when no block is selected, or
 * reuses the shared PropertiesPanel block editors when a block is selected.
 */

import { useState } from 'react';
import PropertiesPanel from './PropertiesPanel';

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
}) => {
    const [featuredImageUploading, setFeaturedImageUploading] = useState(false);
    const [featuredImageError, setFeaturedImageError] = useState(null);
    const [showSlugEdit, setShowSlugEdit] = useState(false);
    const [expandedTaxonomies, setExpandedTaxonomies] = useState({});

    // Handle featured image upload
    const handleFeaturedImageUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file || !onImageUpload) return;

        setFeaturedImageUploading(true);
        setFeaturedImageError(null);

        try {
            const result = await onImageUpload(file);
            if (result.url) {
                setFeaturedImage(result.url);
                setRemoveFeaturedImage(false);
            }
        } catch (err) {
            setFeaturedImageError(err.message || 'Upload failed');
        } finally {
            setFeaturedImageUploading(false);
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

    // Toggle taxonomy expansion
    const toggleTaxonomyExpanded = (taxonomyName) => {
        setExpandedTaxonomies((prev) => ({
            ...prev,
            [taxonomyName]: !prev[taxonomyName],
        }));
    };

    // Render hierarchical terms
    const renderHierarchicalTerms = (terms, taxonomyName, parentTermId = null, level = 0) => {
        const filteredTerms = terms.filter((term) => term.parent_id === parentTermId);

        if (filteredTerms.length === 0) return null;

        return filteredTerms.map((term) => {
            const isSelected = (selectedTerms[taxonomyName] || []).includes(term.id);
            const children = renderHierarchicalTerms(terms, taxonomyName, term.id, level + 1);

            return (
                <div key={term.id} className="mb-1" style={{ marginLeft: `${level * 16}px` }}>
                    <label className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                        <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={() => handleTermToggle(taxonomyName, term.id)}
                            className="form-checkbox h-4 w-4 text-primary rounded"
                        />
                        <span className="text-sm text-gray-700">{term.name}</span>
                    </label>
                    {children}
                </div>
            );
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
                        {postTypeModel.label_singular} Details
                    </span>
                </div>

                {/* Title (shown on mobile, hidden on desktop where it's in header) */}
                <div className="mb-4 md:hidden">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        type="text"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        className="form-control"
                        placeholder="Enter title..."
                    />
                </div>

                {/* Slug */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Slug</label>
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
                                {slug || <span className="text-gray-400 italic">auto-generated</span>}
                            </div>
                        )}
                        <button
                            type="button"
                            onClick={() => setShowSlugEdit(!showSlugEdit)}
                            className="btn-default px-3 py-2 text-xs"
                        >
                            {showSlugEdit ? 'OK' : 'Edit'}
                        </button>
                        <button
                            type="button"
                            onClick={generateSlug}
                            className="btn-default px-3 py-2 text-xs"
                            title="Generate from title"
                        >
                            <iconify-icon icon="mdi:refresh" width="16" height="16"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>

            {/* Status Section */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Status & Visibility
                    </span>
                </div>

                {/* Status */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
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
                {status === 'scheduled' && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Publish Date
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
                            Featured Image
                        </span>
                    </div>

                    {featuredImage && !removeFeaturedImage ? (
                        <div className="mb-3">
                            <div className="relative">
                                <img
                                    src={featuredImage}
                                    alt="Featured"
                                    className="w-full h-32 object-cover rounded-md border border-gray-200"
                                />
                                <button
                                    type="button"
                                    onClick={() => {
                                        setFeaturedImage('');
                                        setRemoveFeaturedImage(true);
                                    }}
                                    className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600"
                                    title="Remove image"
                                >
                                    <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    ) : (
                        <label
                            className={`flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors ${
                                featuredImageUploading ? 'opacity-50 cursor-not-allowed' : ''
                            }`}
                        >
                            {featuredImageUploading ? (
                                <div className="flex flex-col items-center">
                                    <iconify-icon
                                        icon="mdi:loading"
                                        width="24"
                                        height="24"
                                        class="text-gray-400 animate-spin"
                                    ></iconify-icon>
                                    <span className="text-sm text-gray-500 mt-2">Uploading...</span>
                                </div>
                            ) : (
                                <div className="flex flex-col items-center">
                                    <iconify-icon
                                        icon="mdi:image-plus"
                                        width="32"
                                        height="32"
                                        class="text-gray-400"
                                    ></iconify-icon>
                                    <span className="text-sm text-gray-500 mt-2">Upload featured image</span>
                                </div>
                            )}
                            <input
                                type="file"
                                accept="image/*"
                                onChange={handleFeaturedImageUpload}
                                disabled={featuredImageUploading}
                                className="hidden"
                            />
                        </label>
                    )}

                    {featuredImageError && (
                        <p className="text-xs text-red-500 mt-2">{featuredImageError}</p>
                    )}
                </div>
            )}

            {/* Excerpt Section */}
            {postTypeModel.supports_excerpt && (
                <div className="mb-6">
                    <div className="mb-4 pb-2 border-b border-gray-200">
                        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Excerpt
                        </span>
                    </div>

                    <textarea
                        value={excerpt}
                        onChange={(e) => setExcerpt(e.target.value)}
                        rows={3}
                        className="form-control-textarea"
                        placeholder="A short summary of the content..."
                    />
                    <p className="text-xs text-gray-400 mt-1">
                        Leave empty to auto-generate from content
                    </p>
                </div>
            )}

            {/* Parent Post (for hierarchical post types) */}
            {postTypeModel.hierarchical && Object.keys(parentPosts).length > 0 && (
                <div className="mb-6">
                    <div className="mb-4 pb-2 border-b border-gray-200">
                        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Parent {postTypeModel.label_singular}
                        </span>
                    </div>

                    <select
                        value={parentId}
                        onChange={(e) => setParentId(e.target.value)}
                        className="form-control"
                    >
                        <option value="">None</option>
                        {Object.entries(parentPosts).map(([id, postTitle]) => (
                            <option key={id} value={id}>
                                {postTitle}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {/* Taxonomies */}
            {taxonomies.length > 0 && (
                <div className="mb-6">
                    <div className="mb-4 pb-2 border-b border-gray-200">
                        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Taxonomies
                        </span>
                    </div>

                    {taxonomies.map((taxonomy) => (
                        <div key={taxonomy.name} className="mb-4">
                            <button
                                type="button"
                                onClick={() => toggleTaxonomyExpanded(taxonomy.name)}
                                className="flex items-center justify-between w-full text-left text-sm font-medium text-gray-700 mb-2 hover:text-gray-900"
                            >
                                <span>{taxonomy.label}</span>
                                <iconify-icon
                                    icon={expandedTaxonomies[taxonomy.name] ? 'mdi:chevron-up' : 'mdi:chevron-down'}
                                    width="18"
                                    height="18"
                                ></iconify-icon>
                            </button>

                            {(expandedTaxonomies[taxonomy.name] === undefined
                                ? true
                                : expandedTaxonomies[taxonomy.name]) && (
                                <div className="max-h-48 overflow-y-auto border border-gray-200 rounded-md p-2">
                                    {taxonomy.terms && taxonomy.terms.length > 0 ? (
                                        taxonomy.hierarchical ? (
                                            renderHierarchicalTerms(taxonomy.terms, taxonomy.name)
                                        ) : (
                                            taxonomy.terms.map((term) => {
                                                const isSelected = (selectedTerms[taxonomy.name] || []).includes(
                                                    term.id
                                                );
                                                return (
                                                    <label
                                                        key={term.id}
                                                        className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={isSelected}
                                                            onChange={() =>
                                                                handleTermToggle(taxonomy.name, term.id)
                                                            }
                                                            className="form-checkbox h-4 w-4 text-primary rounded"
                                                        />
                                                        <span className="text-sm text-gray-700">{term.name}</span>
                                                    </label>
                                                );
                                            })
                                        )
                                    ) : (
                                        <p className="text-xs text-gray-400 py-2">No {taxonomy.label.toLowerCase()} found</p>
                                    )}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            )}

            {/* Canvas/Content Settings */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Content Settings
                    </span>
                </div>

                {/* Width */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Content Width</label>
                    <select
                        value={canvasSettings?.width || '100%'}
                        onChange={(e) => onCanvasSettingsUpdate({ ...canvasSettings, width: e.target.value })}
                        className="form-control"
                    >
                        <option value="100%">Full Width</option>
                        <option value="800px">800px (Narrow)</option>
                        <option value="1000px">1000px (Standard)</option>
                        <option value="1200px">1200px (Wide)</option>
                    </select>
                </div>

                {/* Content Padding */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Content Padding</label>
                    <select
                        value={canvasSettings?.contentPadding || '24px'}
                        onChange={(e) => onCanvasSettingsUpdate({ ...canvasSettings, contentPadding: e.target.value })}
                        className="form-control"
                    >
                        <option value="0px">None</option>
                        <option value="16px">16px (Compact)</option>
                        <option value="24px">24px (Small)</option>
                        <option value="32px">32px (Medium)</option>
                        <option value="40px">40px (Large)</option>
                    </select>
                </div>
            </div>

            <div className="mt-6 pt-4 border-t border-gray-200">
                <p className="text-xs text-gray-400 text-center">Click on a block to edit its properties</p>
            </div>
        </div>
    );
};

export default PostPropertiesPanel;
