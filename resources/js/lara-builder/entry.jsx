/**
 * LaraBuilder Entry Point
 *
 * This file initializes the LaraBuilder when mounted on a DOM element.
 * It reads configuration from data attributes and renders the builder.
 */

import * as React from "react";
import * as ReactDOM from "react-dom";
import * as ReactJSXRuntime from "react/jsx-runtime";
import { createRoot } from "react-dom/client";
import LaraBuilder from "./core/LaraBuilder";
import EmailPropertiesPanel from "./components/EmailPropertiesPanel";

// Expose React globally for module blocks to use
// This ensures module blocks use the same React instance as the main app
if (typeof window !== "undefined") {
    window.React = React;
    window.ReactDOM = ReactDOM;
    window.ReactJSXRuntime = ReactJSXRuntime;
}

// Import adapters to register them
import "./adapters";

// Import and register blocks from modular architecture
import { blockRegistry } from "./registry/BlockRegistry";
import { registerModularBlocks } from "./blocks/blockLoader";

// Import and initialize translations
import { initTranslations } from "./i18n";

// Register all modular blocks (new architecture with block.json, editor.jsx)
// Each block includes component + propertyEditor
registerModularBlocks(blockRegistry);

/**
 * Initialize LaraBuilder on a DOM element
 */
function initLaraBuilder(elementId = "lara-builder-root") {
    const mountElement = document.getElementById(elementId);

    if (!mountElement) {
        console.warn(`LaraBuilder: Mount element #${elementId} not found`);
        return null;
    }

    // Parse data attributes
    const context = mountElement.dataset.context || "post";
    const initialData = mountElement.dataset.initialData
        ? JSON.parse(mountElement.dataset.initialData)
        : null;
    const templateData = mountElement.dataset.templateData
        ? JSON.parse(mountElement.dataset.templateData)
        : null;
    const saveUrl = mountElement.dataset.saveUrl;
    const listUrl = mountElement.dataset.listUrl;
    const uploadUrl = mountElement.dataset.uploadUrl;
    const videoUploadUrl = mountElement.dataset.videoUploadUrl;
    const showHeader = mountElement.dataset.showHeader !== "false";
    // Generic redirect URL - any module can pass this to redirect after save
    const redirectUrl = mountElement.dataset.redirectUrl || null;

    // Initialize translations from data attribute or global window object
    const translations = mountElement.dataset.translations
        ? JSON.parse(mountElement.dataset.translations)
        : window.__translations || {};
    initTranslations(translations);

    const isEdit = !!templateData?.uuid;

    // Create the save handler
    const handleSave = async (data) => {
        if (!saveUrl) {
            throw new Error("Save URL is not configured");
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        const response = await fetch(saveUrl, {
            method: isEdit ? "PUT" : "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify(data),
        });

        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message || "Failed to save");
        }

        // If a redirect URL was provided, use it after save
        // This allows any module to pass a return URL
        if (redirectUrl && responseData.id) {
            responseData.redirect = redirectUrl;
        }

        return responseData;
    };

    // Create the image upload handler
    const handleImageUpload = async (file) => {
        if (!uploadUrl) {
            throw new Error("Upload URL is not configured");
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        const formData = new FormData();
        formData.append("image", file);

        const response = await fetch(uploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Failed to upload image");
        }

        return response.json();
    };

    // Create the video upload handler
    const handleVideoUpload = async (videoFile, thumbnailFile = null) => {
        if (!videoUploadUrl) {
            throw new Error("Video upload URL is not configured");
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        const formData = new FormData();
        formData.append("video", videoFile);
        if (thumbnailFile) {
            formData.append("thumbnail", thumbnailFile);
        }

        const response = await fetch(videoUploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Failed to upload video");
        }

        return data;
    };

    // Mount the React app
    const root = createRoot(mountElement);
    root.render(
        <LaraBuilder
            context={context}
            initialData={initialData}
            templateData={templateData}
            listUrl={listUrl}
            onSave={handleSave}
            onImageUpload={handleImageUpload}
            onVideoUpload={handleVideoUpload}
            showHeader={showHeader}
            PropertiesPanelComponent={EmailPropertiesPanel}
        />
    );

    return root;
}

// Auto-initialize if the default element exists
document.addEventListener("DOMContentLoaded", () => {
    // Initialize LaraBuilder (new element ID)
    if (document.getElementById("lara-builder-root")) {
        initLaraBuilder("lara-builder-root");
    }

    // Backward compatibility: Initialize on email-builder-root as well
    if (document.getElementById("email-builder-root")) {
        initLaraBuilder("email-builder-root");
    }
});

// Export for manual initialization
export { initLaraBuilder };
export default initLaraBuilder;
