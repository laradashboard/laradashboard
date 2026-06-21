import { useEffect, useMemo, useRef, useState } from "react";
import { __ } from "@lara-builder/i18n";
import { LaraHooks } from "../hooks-system/LaraHooks";
import { BuilderHooks } from "../hooks-system/HookNames";
import SideDrawer from "./SideDrawer";
import {
    analyzeSeo,
    getCharCountStatus,
    getScoreColor,
} from "../utils/seoAnalyzer";

const SCHEMA_TYPES = [
    { value: "", label: __("Default (WebPage)") },
    { value: "Article", label: "Article" },
    { value: "BlogPosting", label: "BlogPosting" },
    { value: "WebPage", label: "WebPage" },
    { value: "NewsArticle", label: "NewsArticle" },
];

const getDefaultSeoFields = () => [
    {
        id: "seo_title",
        name: "seoTitle",
        label: __("SEO Title"),
        type: "text",
        placeholder: __("Custom title for search engines"),
        help: __("Leave empty to use the post title."),
        maxLength: 255,
        recommendedMin: 30,
        recommendedMax: 60,
        position: 10,
    },
    {
        id: "seo_description",
        name: "seoDescription",
        label: __("Meta Description"),
        type: "textarea",
        placeholder: __("Brief description for search results"),
        help: __("Recommended: 120–160 characters."),
        maxLength: 500,
        recommendedMin: 120,
        recommendedMax: 160,
        rows: 4,
        position: 20,
    },
    {
        id: "seo_keywords",
        name: "seoKeywords",
        label: __("Focus Keywords"),
        type: "text",
        placeholder: __("e.g. laravel, wordpress, cms"),
        help: __("Comma-separated. The first keyword is used as the primary focus."),
        maxLength: 500,
        position: 30,
    },
];

function ScoreRing({ score, rating }) {
    const colors = getScoreColor(rating);
    const circumference = 2 * Math.PI * 36;
    const offset = circumference - (score / 100) * circumference;

    return (
        <div className="relative w-20 h-20 flex-shrink-0">
            <svg className="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
                <circle
                    cx="40"
                    cy="40"
                    r="36"
                    fill="none"
                    stroke="#e5e7eb"
                    strokeWidth="6"
                />
                <circle
                    cx="40"
                    cy="40"
                    r="36"
                    fill="none"
                    className={colors.ring}
                    stroke="currentColor"
                    strokeWidth="6"
                    strokeLinecap="round"
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                />
            </svg>
            <div className="absolute inset-0 flex items-center justify-center">
                <span className="text-xl font-bold text-gray-800">{score}</span>
            </div>
        </div>
    );
}

function CheckIcon({ status }) {
    const icon =
        status === "good"
            ? "mdi:check-circle"
            : status === "ok"
              ? "mdi:alert-circle"
              : "mdi:close-circle";
    const color =
        status === "good"
            ? "text-green-500"
            : status === "ok"
              ? "text-amber-500"
              : "text-red-500";

    return (
        <iconify-icon
            icon={icon}
            width="18"
            height="18"
            class={color}
        ></iconify-icon>
    );
}

function SeoDrawer({
    isOpen,
    onClose,
    title,
    slug,
    excerpt,
    contentHtml = "",
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
    postData,
    postType,
    contentLength = 0,
    blockCount = 0,
    showToast,
    onFocusTitle,
    featuredImage = "",
    removeFeaturedImage = false,
}) {
    const [checklistOpen, setChecklistOpen] = useState(true);
    const [advancedOpen, setAdvancedOpen] = useState(false);
    const [aiLoading, setAiLoading] = useState(false);
    const [aiError, setAiError] = useState("");
    const scrollContainerRef = useRef(null);
    const advancedSectionRef = useRef(null);

    useEffect(() => {
        if (!advancedOpen) {
            return;
        }

        const frame = requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const container = scrollContainerRef.current;
                const section = advancedSectionRef.current;

                if (!container || !section) {
                    return;
                }

                const containerRect = container.getBoundingClientRect();
                const sectionRect = section.getBoundingClientRect();
                const overflow = sectionRect.bottom - containerRect.bottom;

                if (overflow > 0) {
                    container.scrollTo({
                        top: container.scrollTop + overflow + 24,
                        behavior: "smooth",
                    });
                }
            });
        });

        return () => cancelAnimationFrame(frame);
    }, [advancedOpen]);

    const values = { seoTitle, seoDescription, seoKeywords };
    const setters = {
        seoTitle: setSeoTitle,
        seoDescription: setSeoDescription,
        seoKeywords: setSeoKeywords,
    };

    const fields = useMemo(() => {
        const defaults = getDefaultSeoFields();
        const filtered = LaraHooks.applyFilters(
            BuilderHooks.FILTER_SEO_FIELDS,
            defaults,
            values
        );

        return [...filtered].sort(
            (a, b) => (a.position || 100) - (b.position || 100)
        );
    }, [seoTitle, seoDescription, seoKeywords]);

    const analysis = useMemo(
        () =>
            analyzeSeo({
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
            }),
        [
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
        ]
    );

    const scoreColors = getScoreColor(analysis.rating);
    const previewUrl =
        seoCanonical ||
        postData?.frontend_url ||
        (slug
            ? `${window.location.origin}/${postType === "page" ? "" : "post/"}${slug}`
            : window.location.origin);
    const siteName = document.title.split(" - ").pop() || "Site";

    const ratingLabel =
        analysis.rating === "good"
            ? __("Good SEO")
            : analysis.rating === "fair"
              ? __("Fair SEO")
              : __("Needs improvement");

    const featuredImagePreview = useMemo(() => {
        if (removeFeaturedImage) {
            return null;
        }

        if (featuredImage) {
            return featuredImage;
        }

        return postData?.featured_image_url || null;
    }, [featuredImage, removeFeaturedImage, postData?.featured_image_url]);

    const handleGenerateSeo = async () => {
        const hasContext =
            title.trim() ||
            excerpt.trim() ||
            stripHtml(contentHtml).trim();

        if (!hasContext) {
            const message = __("Add a title or content before generating SEO.");
            if (showToast) {
                showToast("error", __("Validation Error"), message);
            }
            if (onFocusTitle) {
                onFocusTitle();
            }
            return;
        }

        setAiLoading(true);
        setAiError("");

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch("/admin/ai/generate-seo", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    title,
                    content: contentHtml,
                    excerpt,
                    slug,
                    post_type: postType || "post",
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || __("Failed to generate SEO metadata.")
                );
            }

            const meta = data.data || {};
            setSeoTitle(meta.seo_title || "");
            setSeoDescription(meta.seo_description || "");
            setSeoKeywords(meta.seo_keywords || "");
            setSeoOgTitle(meta.seo_og_title || "");
            setSeoOgDescription(meta.seo_og_description || "");
            if (meta.seo_schema_type) {
                setSeoSchemaType(meta.seo_schema_type);
            }

            if (showToast) {
                showToast(
                    "success",
                    __("SEO Generated"),
                    __("SEO metadata has been filled in. Review and save when ready.")
                );
            }
        } catch (error) {
            const message =
                error.message || __("Failed to generate SEO metadata.");
            setAiError(message);
            if (showToast) {
                showToast("error", __("Generation Failed"), message);
            }
        } finally {
            setAiLoading(false);
        }
    };

    return (
        <SideDrawer
            isOpen={isOpen}
            onClose={onClose}
            title={__("SEO")}
            subtitle={__("Optimize how this page appears in search")}
            icon={
                <iconify-icon
                    icon="mdi:chart-line"
                    width="20"
                    height="20"
                ></iconify-icon>
            }
            iconClassName="bg-emerald-50 text-emerald-600"
            contentRef={scrollContainerRef}
            footer={
                <div className="px-5 py-4 border-t border-gray-200 bg-gray-50">
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-primary w-full"
                    >
                        {__("Done")}
                    </button>
                </div>
            }
        >
                    <div className="p-5 border-b border-gray-100">
                        <div className="flex items-center gap-4">
                            <ScoreRing
                                score={analysis.score}
                                rating={analysis.rating}
                            />
                            <div className="flex-1 min-w-0">
                                <p
                                    className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${scoreColors.bg} ${scoreColors.text}`}
                                >
                                    {ratingLabel}
                                </p>
                                <p className="text-sm text-gray-600 mt-2">
                                    {__(
                                        "Complete the checklist below to improve your score."
                                    )}
                                </p>
                                <button
                                    type="button"
                                    onClick={handleGenerateSeo}
                                    disabled={aiLoading}
                                    className="mt-3 inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed"
                                    style={{
                                        backgroundColor:
                                            "var(--color-primary, #635bff)",
                                    }}
                                >
                                    {aiLoading ? (
                                        <>
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
                                            {__("Generating...")}
                                        </>
                                    ) : (
                                        <>
                                            <iconify-icon
                                                icon="mdi:lightning-bolt"
                                                width="16"
                                                height="16"
                                            ></iconify-icon>
                                            {__("Generate SEO with AI")}
                                        </>
                                    )}
                                </button>
                                {aiError && (
                                    <p className="text-xs text-red-600 mt-2">
                                        {aiError}
                                    </p>
                                )}
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={() => setChecklistOpen(!checklistOpen)}
                            className="mt-4 w-full flex items-center justify-between text-sm font-medium text-gray-700"
                        >
                            <span>
                                {__("SEO Checklist")} (
                                {
                                    analysis.checks.filter(
                                        (c) => c.status === "good"
                                    ).length
                                }
                                /{analysis.checks.length})
                            </span>
                            <iconify-icon
                                icon={
                                    checklistOpen
                                        ? "mdi:chevron-up"
                                        : "mdi:chevron-down"
                                }
                                width="18"
                                height="18"
                            ></iconify-icon>
                        </button>

                        {checklistOpen && (
                            <ul className="mt-3 space-y-2">
                                {analysis.checks.map((check) => (
                                    <li
                                        key={check.id}
                                        className="flex items-start gap-2 text-sm"
                                    >
                                        <CheckIcon status={check.status} />
                                        <div>
                                            <span className="font-medium text-gray-800">
                                                {check.label}
                                            </span>
                                            <p className="text-gray-500 text-xs mt-0.5">
                                                {check.message}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    <div className="p-5 border-b border-gray-100">
                        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            {__("Search Preview")}
                        </h3>
                        <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p className="text-xs text-green-700 truncate mb-1">
                                {previewUrl}
                            </p>
                            <p className="text-lg text-blue-700 leading-snug hover:underline cursor-default truncate">
                                {analysis.effectiveTitle || __("Untitled")}
                            </p>
                            <p className="text-sm text-gray-600 mt-1 line-clamp-2">
                                {analysis.effectiveDescription ||
                                    __(
                                        "Add a meta description to improve click-through rate."
                                    )}
                            </p>
                        </div>
                    </div>

                    <div className="p-5 border-b border-gray-100">
                        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            {__("Social Preview")}
                        </h3>
                        <div className="rounded-lg border border-gray-200 overflow-hidden">
                            <div className="bg-gray-100 h-28 flex items-center justify-center text-gray-400 text-xs">
                                {featuredImagePreview ? (
                                    <img
                                        src={featuredImagePreview}
                                        alt=""
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    __("Featured image will appear here")
                                )}
                            </div>
                            <div className="p-3 bg-white">
                                <p className="text-[10px] uppercase text-gray-400 tracking-wide">
                                    {siteName}
                                </p>
                                <p className="text-sm font-semibold text-gray-900 line-clamp-2 mt-1">
                                    {analysis.effectiveOgTitle ||
                                        title ||
                                        __("Untitled")}
                                </p>
                                <p className="text-xs text-gray-500 line-clamp-2 mt-1">
                                    {analysis.effectiveOgDescription ||
                                        excerpt ||
                                        __("Add a meta description.")}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="p-5 space-y-5 border-b border-gray-100">
                        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {__("SEO Settings")}
                        </h3>

                        {fields.map((field) => {
                            const value = values[field.name] ?? "";
                            const onChange = setters[field.name];
                            if (!onChange) return null;

                            const charCount = value.length;
                            const showCounter =
                                field.recommendedMin && field.recommendedMax;

                            return (
                                <div key={field.id || field.name}>
                                    <div className="flex items-center justify-between mb-1">
                                        <label className="block text-sm font-medium text-gray-700">
                                            {field.label}
                                        </label>
                                        {showCounter && (
                                            <span
                                                className={`text-xs font-medium ${getCharCountStatus(
                                                    charCount,
                                                    field.recommendedMin,
                                                    field.recommendedMax
                                                )}`}
                                            >
                                                {charCount}/
                                                {field.recommendedMax}
                                            </span>
                                        )}
                                    </div>

                                    {field.type === "textarea" ? (
                                        <textarea
                                            value={value}
                                            onChange={(e) =>
                                                onChange(e.target.value)
                                            }
                                            rows={field.rows || 4}
                                            maxLength={field.maxLength}
                                            className="form-control-textarea"
                                            placeholder={field.placeholder}
                                        />
                                    ) : (
                                        <input
                                            type="text"
                                            value={value}
                                            onChange={(e) =>
                                                onChange(e.target.value)
                                            }
                                            maxLength={field.maxLength}
                                            className="form-control"
                                            placeholder={field.placeholder}
                                        />
                                    )}

                                    {field.help && (
                                        <p className="text-xs text-gray-400 mt-1">
                                            {field.help}
                                        </p>
                                    )}
                                </div>
                            );
                        })}
                    </div>

                    <div ref={advancedSectionRef} className="p-5">
                        <button
                            type="button"
                            onClick={() => setAdvancedOpen(!advancedOpen)}
                            className="w-full flex items-center justify-between text-sm font-medium text-gray-700"
                        >
                            <span className="inline-flex items-center gap-2">
                                <iconify-icon
                                    icon="mdi:tune-variant"
                                    width="18"
                                    height="18"
                                    class="text-gray-500"
                                ></iconify-icon>
                                {__("Advanced SEO Settings")}
                            </span>
                            <iconify-icon
                                icon={
                                    advancedOpen
                                        ? "mdi:chevron-up"
                                        : "mdi:chevron-down"
                                }
                                width="18"
                                height="18"
                            ></iconify-icon>
                        </button>

                        {advancedOpen && (
                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {__("Open Graph Title")}
                                    </label>
                                    <input
                                        type="text"
                                        value={seoOgTitle}
                                        onChange={(e) =>
                                            setSeoOgTitle(e.target.value)
                                        }
                                        maxLength={255}
                                        className="form-control"
                                        placeholder={__(
                                            "Custom title for social sharing"
                                        )}
                                    />
                                    <p className="text-xs text-gray-400 mt-1">
                                        {__(
                                            "Leave empty to use the SEO title."
                                        )}
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {__("Open Graph Description")}
                                    </label>
                                    <textarea
                                        value={seoOgDescription}
                                        onChange={(e) =>
                                            setSeoOgDescription(e.target.value)
                                        }
                                        rows={3}
                                        maxLength={500}
                                        className="form-control-textarea"
                                        placeholder={__(
                                            "Custom description for social sharing"
                                        )}
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {__("Canonical URL")}
                                    </label>
                                    <input
                                        type="url"
                                        value={seoCanonical}
                                        onChange={(e) =>
                                            setSeoCanonical(e.target.value)
                                        }
                                        maxLength={500}
                                        className="form-control"
                                        placeholder="https://example.com/my-page"
                                    />
                                    <p className="text-xs text-gray-400 mt-1">
                                        {__(
                                            "Override the default canonical URL if this page has duplicates."
                                        )}
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {__("Schema Type")}
                                    </label>
                                    <select
                                        value={seoSchemaType}
                                        onChange={(e) =>
                                            setSeoSchemaType(e.target.value)
                                        }
                                        className="form-control"
                                    >
                                        {SCHEMA_TYPES.map((option) => (
                                            <option
                                                key={option.value || "default"}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3 space-y-3">
                                    <p className="text-sm font-medium text-gray-700">
                                        {__("Robots Meta")}
                                    </p>
                                    <label className="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={seoNoindex}
                                            onChange={(e) =>
                                                setSeoNoindex(e.target.checked)
                                            }
                                            className="rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        {__(
                                            "Noindex — hide from search engine results"
                                        )}
                                    </label>
                                    <label className="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={seoNofollow}
                                            onChange={(e) =>
                                                setSeoNofollow(e.target.checked)
                                            }
                                            className="rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        {__(
                                            "Nofollow — do not follow links on this page"
                                        )}
                                    </label>
                                </div>
                            </div>
                        )}
                    </div>
        </SideDrawer>
    );
}

function stripHtml(html) {
    if (!html) return "";

    return html.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}

export default SeoDrawer;
