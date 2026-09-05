/**
 * Parse failed builder save responses into user-facing messages.
 */

import { __ } from "@lara-builder/i18n";

export async function parseSaveErrorResponse(response) {
    const status = response.status;
    let errorData = {};

    try {
        errorData = await response.json();
    } catch {
        if (status === 419) {
            return __(
                "Your session expired. Please refresh the page and try again."
            );
        }

        return `${__("Failed to save")} (${status})`;
    }

    if (errorData.errors && typeof errorData.errors === "object") {
        const messages = Object.values(errorData.errors).flat();

        if (messages.length > 0) {
            return messages.join(" ");
        }
    }

    if (errorData.message) {
        return errorData.message;
    }

    if (status === 419) {
        return __(
            "Your session expired. Please refresh the page and try again."
        );
    }

    if (status === 422) {
        return __("The given data was invalid.");
    }

    return `${__("Failed to save")} (${status})`;
}
