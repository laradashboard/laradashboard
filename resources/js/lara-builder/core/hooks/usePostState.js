/**
 * usePostState - Hook for managing post/page state
 *
 * Handles title, slug, status, taxonomies, and dirty state tracking for post context.
 */

import { useState, useCallback, useMemo } from "react";

/**
 * @param {Object} options
 * @param {Object} options.postData - Initial post data
 * @param {Object} options.initialSelectedTerms - Initial taxonomy selections
 * @param {boolean} options.isPostContext - Whether we're in post context
 * @returns {Object} Post state and setters
 */
export function usePostState({ postData, initialSelectedTerms, isPostContext }) {
    const [title, setTitle] = useState(postData?.title || "");
    const [slug, setSlug] = useState(postData?.slug || "");
    const [status, setStatus] = useState(postData?.status || "draft");
    const [excerpt, setExcerpt] = useState(postData?.excerpt || "");
    const [publishedAt, setPublishedAt] = useState(postData?.published_at || "");
    const [parentId, setParentId] = useState(String(postData?.parent_id || ""));
    const [selectedTerms, setSelectedTerms] = useState(initialSelectedTerms || {});
    const [featuredImage, setFeaturedImage] = useState(
        postData?.featured_image_url || ""
    );
    const [removeFeaturedImage, setRemoveFeaturedImage] = useState(false);

    // Track saved post data for dirty detection (use state so changes trigger re-render)
    const [savedPostData, setSavedPostData] = useState(() => ({
        title: postData?.title || "",
        slug: postData?.slug || "",
        status: postData?.status || "draft",
        excerpt: postData?.excerpt || "",
        publishedAt: postData?.published_at || "",
        parentId: String(postData?.parent_id || ""),
        featuredImage: postData?.featured_image_url || "",
    }));

    // Calculate post-specific dirty state
    const postDirty = useMemo(() => {
        if (!isPostContext) return false;
        return (
            title !== savedPostData.title ||
            slug !== savedPostData.slug ||
            status !== savedPostData.status ||
            excerpt !== savedPostData.excerpt ||
            publishedAt !== savedPostData.publishedAt ||
            parentId !== savedPostData.parentId ||
            featuredImage !== savedPostData.featuredImage ||
            removeFeaturedImage
        );
    }, [
        isPostContext,
        title,
        slug,
        status,
        excerpt,
        publishedAt,
        parentId,
        featuredImage,
        removeFeaturedImage,
        savedPostData,
    ]);

    // Auto-generate slug from title
    const generateSlug = useCallback(() => {
        const generatedSlug = title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, "")
            .replace(/\s+/g, "-")
            .replace(/-+/g, "-")
            .trim();
        setSlug(generatedSlug);
    }, [title]);

    // Mark as saved - reset dirty tracking
    const markPostSaved = () => {
        setSavedPostData({
            title,
            slug,
            status,
            excerpt,
            publishedAt,
            parentId,
            featuredImage,
        });
        setRemoveFeaturedImage(false);
    };

    return {
        // State
        title,
        slug,
        status,
        excerpt,
        publishedAt,
        parentId,
        selectedTerms,
        featuredImage,
        removeFeaturedImage,
        postDirty,
        // Setters
        setTitle,
        setSlug,
        setStatus,
        setExcerpt,
        setPublishedAt,
        setParentId,
        setSelectedTerms,
        setFeaturedImage,
        setRemoveFeaturedImage,
        // Actions
        generateSlug,
        markPostSaved,
    };
}

export default usePostState;
