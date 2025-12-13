/**
 * Post Builder Entry Point
 *
 * Mounts LaraBuilder for post/page editing with post-specific properties panel.
 */

import * as React from "react";
import * as ReactDOM from "react-dom";
import * as ReactJSXRuntime from "react/jsx-runtime";
import { createRoot } from "react-dom/client";
import LaraBuilder from "./core/LaraBuilder";
import PostPropertiesPanel from "./components/PostPropertiesPanel";

// Expose React globally for module blocks to use
// This ensures module blocks use the same React instance as the main app
if (typeof window !== "undefined") {
    window.React = React;
    window.ReactDOM = ReactDOM;
    window.ReactJSXRuntime = ReactJSXRuntime;
}

// Import and register blocks from modular architecture
import { blockRegistry } from "./registry/BlockRegistry";
import { registerModularBlocks } from "./blocks/blockLoader";

// Import adapters to ensure they are registered
import "./adapters";

// Import and initialize translations
import { initTranslations } from "./i18n";

// Register all modular blocks (new architecture with block.json, editor.jsx)
// Each block includes component + propertyEditor
registerModularBlocks(blockRegistry);

// Find the mount point
const container = document.getElementById("lara-builder-root");

if (container) {
    // Get configuration from data attributes
    const context = container.dataset.context || "page";
    const initialData = container.dataset.initialData
        ? JSON.parse(container.dataset.initialData)
        : null;
    const postData = container.dataset.postData
        ? JSON.parse(container.dataset.postData)
        : null;
    const saveUrl = container.dataset.saveUrl;
    const listUrl = container.dataset.listUrl;
    const uploadUrl = container.dataset.uploadUrl;
    const videoUploadUrl = container.dataset.videoUploadUrl;
    const taxonomies = container.dataset.taxonomies
        ? JSON.parse(container.dataset.taxonomies)
        : [];
    const selectedTerms = container.dataset.selectedTerms
        ? JSON.parse(container.dataset.selectedTerms)
        : {};
    const parentPosts = container.dataset.parentPosts
        ? JSON.parse(container.dataset.parentPosts)
        : {};
    const postType = container.dataset.postType || "post";
    const postTypeModel = container.dataset.postTypeModel
        ? JSON.parse(container.dataset.postTypeModel)
        : {};
    const statuses = container.dataset.statuses
        ? JSON.parse(container.dataset.statuses)
        : {};

    // Initialize translations from data attribute
    const translations = container.dataset.translations
        ? JSON.parse(container.dataset.translations)
        : {};
    initTranslations(translations);

    // Image upload handler
    const handleImageUpload = async (file) => {
        if (!uploadUrl) {
            throw new Error("Upload URL not configured");
        }

        const formData = new FormData();
        formData.append("image", file);

        const response = await fetch(uploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content,
                Accept: "application/json",
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || "Failed to upload image");
        }

        return await response.json();
    };

    // Video upload handler
    const handleVideoUpload = async (videoFile, thumbnailFile) => {
        if (!videoUploadUrl) {
            throw new Error("Video upload URL not configured");
        }

        const formData = new FormData();
        formData.append("video", videoFile);
        if (thumbnailFile) {
            formData.append("thumbnail", thumbnailFile);
        }

        const response = await fetch(videoUploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content,
                Accept: "application/json",
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || "Failed to upload video");
        }

        return await response.json();
    };

    // Save handler
    const handleSave = async (data) => {
        if (!saveUrl) {
            throw new Error("Save URL not configured");
        }

        const response = await fetch(saveUrl, {
            method: postData?.id ? "PUT" : "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content,
                Accept: "application/json",
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || "Failed to save");
        }

        return await response.json();
    };

    // Mount the app
    const root = createRoot(container);
    root.render(
        <LaraBuilder
            context={context}
            initialData={initialData}
            postData={postData}
            onSave={handleSave}
            onImageUpload={handleImageUpload}
            onVideoUpload={handleVideoUpload}
            listUrl={listUrl}
            taxonomies={taxonomies}
            selectedTerms={selectedTerms}
            parentPosts={parentPosts}
            postType={postType}
            postTypeModel={postTypeModel}
            statuses={statuses}
            PropertiesPanelComponent={PostPropertiesPanel}
        />
    );
}
