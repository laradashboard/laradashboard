/**
 * SEO Analyzer - Client-side SEO compliance scoring
 *
 * Inspired by Rank Math / All in One SEO checklists.
 * Extensible via BuilderHooks.FILTER_SEO_CHECKS and FILTER_SEO_ANALYSIS.
 */

import { LaraHooks } from "../hooks-system/LaraHooks";
import { BuilderHooks } from "../hooks-system/HookNames";

const STATUS = {
    GOOD: "good",
    OK: "ok",
    BAD: "bad",
};

/**
 * @typedef {Object} SeoCheck
 * @property {string} id
 * @property {string} label
 * @property {string} message
 * @property {'good'|'ok'|'bad'} status
 * @property {number} weight
 */

/**
 * @param {Object} params
 * @param {string} params.title
 * @param {string} params.seoTitle
 * @param {string} params.seoDescription
 * @param {string} params.seoKeywords
 * @param {string} params.slug
 * @param {string} params.excerpt
 * @param {number} params.contentLength
 * @param {number} params.blockCount
 */
export function analyzeSeo(params) {
    const title = (params.title || "").trim();
    const seoTitle = (params.seoTitle || "").trim();
    const seoDescription = (params.seoDescription || "").trim();
    const seoKeywords = (params.seoKeywords || "").trim();
    const slug = (params.slug || "").trim();
    const excerpt = (params.excerpt || "").trim();
    const contentLength = params.contentLength || 0;
    const blockCount = params.blockCount || 0;
    const seoOgTitle = (params.seoOgTitle || "").trim();
    const seoOgDescription = (params.seoOgDescription || "").trim();
    const seoCanonical = (params.seoCanonical || "").trim();
    const seoNoindex = Boolean(params.seoNoindex);
    const seoNofollow = Boolean(params.seoNofollow);
    const seoSchemaType = (params.seoSchemaType || "").trim();

    const effectiveTitle = seoTitle || title;
    const effectiveDescription =
        seoDescription || excerpt || stripHtml(params.contentPreview || "");
    const effectiveOgTitle = seoOgTitle || effectiveTitle;
    const effectiveOgDescription = seoOgDescription || effectiveDescription;
    const primaryKeyword =
        seoKeywords
            .split(",")
            .map((k) => k.trim().toLowerCase())
            .filter(Boolean)[0] || "";

    const lowerTitle = effectiveTitle.toLowerCase();
    const lowerDescription = effectiveDescription.toLowerCase();

    /** @type {SeoCheck[]} */
    let checks = [
        checkPresence("title", "SEO title", effectiveTitle, 15),
        checkTitleLength(effectiveTitle),
        checkPresence("description", "Meta description", effectiveDescription, 15),
        checkDescriptionLength(effectiveDescription),
        checkKeywordSet(primaryKeyword),
        checkKeywordInText("keyword_title", "Focus keyword in title", primaryKeyword, lowerTitle, 15),
        checkKeywordInText(
            "keyword_description",
            "Focus keyword in description",
            primaryKeyword,
            lowerDescription,
            10
        ),
        checkContentLength(contentLength, blockCount),
        checkSlug(slug),
        checkOgTitleLength(effectiveOgTitle),
        checkSchemaType(seoSchemaType),
        checkCanonicalUrl(seoCanonical),
        checkSearchVisibility(seoNoindex),
    ];

    checks = LaraHooks.applyFilters(BuilderHooks.FILTER_SEO_CHECKS, checks, {
        ...params,
        effectiveTitle,
        effectiveDescription,
        primaryKeyword,
    });

    const totalWeight = checks.reduce((sum, c) => sum + (c.weight || 0), 0);
    const earnedWeight = checks.reduce((sum, c) => {
        if (c.status === STATUS.GOOD) return sum + c.weight;
        if (c.status === STATUS.OK) return sum + c.weight * 0.6;

        return sum;
    }, 0);

    const score = totalWeight > 0 ? Math.round((earnedWeight / totalWeight) * 100) : 0;

    let rating = "poor";
    if (score >= 80) rating = "good";
    else if (score >= 50) rating = "fair";

    const result = {
        score,
        rating,
        checks,
        effectiveTitle,
        effectiveDescription,
        effectiveOgTitle,
        effectiveOgDescription,
        primaryKeyword,
        titleLength: effectiveTitle.length,
        descriptionLength: effectiveDescription.length,
    };

    const filtered = LaraHooks.applyFilters(
        BuilderHooks.FILTER_SEO_ANALYSIS,
        result,
        params
    );

    return filtered || result;
}

function stripHtml(html) {
    if (!html) return "";

    return html.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}

function checkPresence(id, label, value, weight) {
    if (value) {
        return {
            id,
            label,
            message: label + " is set.",
            status: STATUS.GOOD,
            weight,
        };
    }

    return {
        id,
        label,
        message: "Add a " + label.toLowerCase() + ".",
        status: STATUS.BAD,
        weight,
    };
}

function checkTitleLength(title) {
    const len = title.length;

    if (!title) {
        return {
            id: "title_length",
            label: "Title length",
            message: "Add a title between 30–60 characters.",
            status: STATUS.BAD,
            weight: 10,
        };
    }

    if (len >= 30 && len <= 60) {
        return {
            id: "title_length",
            label: "Title length",
            message: len + " characters — ideal range (30–60).",
            status: STATUS.GOOD,
            weight: 10,
        };
    }

    if (len >= 20 && len <= 70) {
        return {
            id: "title_length",
            label: "Title length",
            message: len + " characters — acceptable, aim for 30–60.",
            status: STATUS.OK,
            weight: 10,
        };
    }

    return {
        id: "title_length",
        label: "Title length",
        message: len + " characters — adjust to 30–60 for best results.",
        status: STATUS.BAD,
        weight: 10,
    };
}

function checkDescriptionLength(description) {
    const len = description.length;

    if (!description) {
        return {
            id: "description_length",
            label: "Description length",
            message: "Write a meta description (120–160 characters).",
            status: STATUS.BAD,
            weight: 10,
        };
    }

    if (len >= 120 && len <= 160) {
        return {
            id: "description_length",
            label: "Description length",
            message: len + " characters — ideal range (120–160).",
            status: STATUS.GOOD,
            weight: 10,
        };
    }

    if (len >= 70 && len <= 180) {
        return {
            id: "description_length",
            label: "Description length",
            message: len + " characters — close; aim for 120–160.",
            status: STATUS.OK,
            weight: 10,
        };
    }

    return {
        id: "description_length",
        label: "Description length",
        message: len + " characters — adjust to 120–160.",
        status: STATUS.BAD,
        weight: 10,
    };
}

function checkKeywordSet(keyword) {
    if (keyword) {
        return {
            id: "keyword_set",
            label: "Focus keyword",
            message: 'Primary keyword: "' + keyword + '".',
            status: STATUS.GOOD,
            weight: 10,
        };
    }

    return {
        id: "keyword_set",
        label: "Focus keyword",
        message: "Add a primary focus keyword.",
        status: STATUS.BAD,
        weight: 10,
    };
}

function checkKeywordInText(id, label, keyword, haystack, weight) {
    if (!keyword) {
        return {
            id,
            label,
            message: "Set a focus keyword first.",
            status: STATUS.OK,
            weight,
        };
    }

    if (haystack.includes(keyword)) {
        return {
            id,
            label,
            message: "Focus keyword appears in the " + label.split(" ").slice(-1)[0] + ".",
            status: STATUS.GOOD,
            weight,
        };
    }

    return {
        id,
        label,
        message: "Include your focus keyword in the " + label.split(" ").slice(-1)[0] + ".",
        status: STATUS.BAD,
        weight,
    };
}

function checkContentLength(contentLength, blockCount) {
    if (contentLength >= 300 || blockCount >= 2) {
        return {
            id: "content_length",
            label: "Content depth",
            message: "Content has enough substance for search engines.",
            status: STATUS.GOOD,
            weight: 10,
        };
    }

    if (contentLength >= 100 || blockCount >= 1) {
        return {
            id: "content_length",
            label: "Content depth",
            message: "Add more content — aim for at least 300 characters.",
            status: STATUS.OK,
            weight: 10,
        };
    }

    return {
        id: "content_length",
        label: "Content depth",
        message: "Add meaningful content to your page.",
        status: STATUS.BAD,
        weight: 10,
    };
}

function checkOgTitleLength(title) {
    const len = title.length;

    if (!title) {
        return {
            id: "og_title",
            label: "Social title",
            message: "Social preview will use the SEO title.",
            status: STATUS.OK,
            weight: 5,
        };
    }

    if (len <= 70) {
        return {
            id: "og_title",
            label: "Social title",
            message: len + " characters — good for social sharing.",
            status: STATUS.GOOD,
            weight: 5,
        };
    }

    return {
        id: "og_title",
        label: "Social title",
        message: len + " characters — shorten to 70 or less.",
        status: STATUS.BAD,
        weight: 5,
    };
}

function checkSchemaType(schemaType) {
    if (schemaType) {
        return {
            id: "schema_type",
            label: "Schema type",
            message: schemaType + " structured data type selected.",
            status: STATUS.GOOD,
            weight: 5,
        };
    }

    return {
        id: "schema_type",
        label: "Schema type",
        message: "Choose a schema type in advanced settings.",
        status: STATUS.OK,
        weight: 5,
    };
}

function checkCanonicalUrl(canonical) {
    if (!canonical) {
        return {
            id: "canonical",
            label: "Canonical URL",
            message: "Default canonical URL will be used.",
            status: STATUS.OK,
            weight: 5,
        };
    }

    try {
        new URL(canonical);

        return {
            id: "canonical",
            label: "Canonical URL",
            message: "Custom canonical URL is set.",
            status: STATUS.GOOD,
            weight: 5,
        };
    } catch {
        return {
            id: "canonical",
            label: "Canonical URL",
            message: "Enter a valid absolute URL.",
            status: STATUS.BAD,
            weight: 5,
        };
    }
}

function checkSearchVisibility(noindex) {
    if (noindex) {
        return {
            id: "search_visibility",
            label: "Search visibility",
            message: "Page is set to noindex — it will not appear in search.",
            status: STATUS.OK,
            weight: 5,
        };
    }

    return {
        id: "search_visibility",
        label: "Search visibility",
        message: "Page is indexable for search engines.",
        status: STATUS.GOOD,
        weight: 5,
    };
}

function checkSlug(slug) {
    if (!slug) {
        return {
            id: "slug",
            label: "URL slug",
            message: "Set a readable URL slug.",
            status: STATUS.BAD,
            weight: 5,
        };
    }

    if (/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug) && slug.length <= 75) {
        return {
            id: "slug",
            label: "URL slug",
            message: "Slug looks clean and readable.",
            status: STATUS.GOOD,
            weight: 5,
        };
    }

    return {
        id: "slug",
        label: "URL slug",
        message: "Use lowercase words separated by hyphens.",
        status: STATUS.OK,
        weight: 5,
    };
}

export function getScoreColor(rating) {
    switch (rating) {
        case "good":
            return {
                ring: "text-green-500",
                bg: "bg-green-50",
                text: "text-green-700",
                badge: "bg-green-500",
            };
        case "fair":
            return {
                ring: "text-amber-500",
                bg: "bg-amber-50",
                text: "text-amber-700",
                badge: "bg-amber-500",
            };
        default:
            return {
                ring: "text-red-500",
                bg: "bg-red-50",
                text: "text-red-700",
                badge: "bg-red-500",
            };
    }
}

export function getCharCountStatus(length, min, max) {
    if (length >= min && length <= max) return "text-green-600";
    if (length >= min * 0.7 && length <= max * 1.15) return "text-amber-600";

    return "text-red-600";
}
