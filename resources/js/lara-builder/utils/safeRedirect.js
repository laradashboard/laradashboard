/**
 * Validate post-save redirect URLs (same-origin or relative paths only).
 */
export function isSafeRedirectUrl(url) {
    if (typeof url !== "string") {
        return false;
    }

    const trimmed = url.trim();

    if (trimmed === "") {
        return false;
    }

    if (/[\x00-\x1F\x7F]/.test(trimmed)) {
        return false;
    }

    if (trimmed.startsWith("/") && !trimmed.startsWith("//")) {
        return true;
    }

    try {
        const parsed = new URL(trimmed, window.location.origin);

        if (parsed.origin !== window.location.origin) {
            return false;
        }

        return parsed.protocol === "http:" || parsed.protocol === "https:";
    } catch {
        return false;
    }
}
