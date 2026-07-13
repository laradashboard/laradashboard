/**
 * usePostState - Hook for managing post/page state
 *
 * Handles title, slug, status, taxonomies, SEO, and dirty state tracking for post context.
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
    const [featuredImageId, setFeaturedImageId] = useState(
        postData?.featured_image_id ? String(postData.featured_image_id) : ""
    );
    const [removeFeaturedImage, setRemoveFeaturedImage] = useState(false);
    const [seoTitle, setSeoTitle] = useState(postData?.seo_title || "");
    const [seoDescription, setSeoDescription] = useState(
        postData?.seo_description || ""
    );
    const [seoKeywords, setSeoKeywords] = useState(postData?.seo_keywords || "");
    const [seoOgTitle, setSeoOgTitle] = useState(postData?.seo_og_title || "");
    const [seoOgDescription, setSeoOgDescription] = useState(
        postData?.seo_og_description || ""
    );
    const [seoCanonical, setSeoCanonical] = useState(postData?.seo_canonical || "");
    const [seoNoindex, setSeoNoindex] = useState(Boolean(postData?.seo_noindex));
    const [seoNofollow, setSeoNofollow] = useState(Boolean(postData?.seo_nofollow));
    const [seoSchemaType, setSeoSchemaType] = useState(
        postData?.seo_schema_type || ""
    );

    // Track saved post data for dirty detection (use state so changes trigger re-render)
    const [savedPostData, setSavedPostData] = useState(() => ({
        title: postData?.title || "",
        slug: postData?.slug || "",
        status: postData?.status || "draft",
        excerpt: postData?.excerpt || "",
        publishedAt: postData?.published_at || "",
        parentId: String(postData?.parent_id || ""),
        featuredImage: postData?.featured_image_url || "",
        featuredImageId: postData?.featured_image_id ? String(postData.featured_image_id) : "",
        seoTitle: postData?.seo_title || "",
        seoDescription: postData?.seo_description || "",
        seoKeywords: postData?.seo_keywords || "",
        seoOgTitle: postData?.seo_og_title || "",
        seoOgDescription: postData?.seo_og_description || "",
        seoCanonical: postData?.seo_canonical || "",
        seoNoindex: Boolean(postData?.seo_noindex),
        seoNofollow: Boolean(postData?.seo_nofollow),
        seoSchemaType: postData?.seo_schema_type || "",
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
            featuredImageId !== savedPostData.featuredImageId ||
            seoTitle !== savedPostData.seoTitle ||
            seoDescription !== savedPostData.seoDescription ||
            seoKeywords !== savedPostData.seoKeywords ||
            seoOgTitle !== savedPostData.seoOgTitle ||
            seoOgDescription !== savedPostData.seoOgDescription ||
            seoCanonical !== savedPostData.seoCanonical ||
            seoNoindex !== savedPostData.seoNoindex ||
            seoNofollow !== savedPostData.seoNofollow ||
            seoSchemaType !== savedPostData.seoSchemaType ||
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
        featuredImageId,
        seoTitle,
        seoDescription,
        seoKeywords,
        seoOgTitle,
        seoOgDescription,
        seoCanonical,
        seoNoindex,
        seoNofollow,
        seoSchemaType,
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
    const markPostSaved = (overrides = {}) => {
        setSavedPostData({
            title: overrides.title ?? title,
            slug: overrides.slug ?? slug,
            status: overrides.status ?? status,
            excerpt: overrides.excerpt ?? excerpt,
            publishedAt: overrides.publishedAt ?? publishedAt,
            parentId: overrides.parentId ?? parentId,
            featuredImage: overrides.featuredImage ?? featuredImage,
            featuredImageId: overrides.featuredImageId ?? featuredImageId,
            seoTitle: overrides.seoTitle ?? seoTitle,
            seoDescription: overrides.seoDescription ?? seoDescription,
            seoKeywords: overrides.seoKeywords ?? seoKeywords,
            seoOgTitle: overrides.seoOgTitle ?? seoOgTitle,
            seoOgDescription: overrides.seoOgDescription ?? seoOgDescription,
            seoCanonical: overrides.seoCanonical ?? seoCanonical,
            seoNoindex: overrides.seoNoindex ?? seoNoindex,
            seoNofollow: overrides.seoNofollow ?? seoNofollow,
            seoSchemaType: overrides.seoSchemaType ?? seoSchemaType,
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
        featuredImageId,
        removeFeaturedImage,
        seoTitle,
        seoDescription,
        seoKeywords,
        seoOgTitle,
        seoOgDescription,
        seoCanonical,
        seoNoindex,
        seoNofollow,
        seoSchemaType,
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
        setFeaturedImageId,
        setRemoveFeaturedImage,
        setSeoTitle,
        setSeoDescription,
        setSeoKeywords,
        setSeoOgTitle,
        setSeoOgDescription,
        setSeoCanonical,
        setSeoNoindex,
        setSeoNofollow,
        setSeoSchemaType,
        // Actions
        generateSlug,
        markPostSaved,
    };
}

export default usePostState;
