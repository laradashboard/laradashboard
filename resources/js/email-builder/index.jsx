import React from 'react';
import { createRoot } from 'react-dom/client';
import EmailBuilder from './EmailBuilder';

// Get the mount element
const mountElement = document.getElementById('email-builder-root');

if (mountElement) {
    // Get data from the element's data attributes
    const initialData = mountElement.dataset.initialData
        ? JSON.parse(mountElement.dataset.initialData)
        : null;

    const templateData = mountElement.dataset.templateData
        ? JSON.parse(mountElement.dataset.templateData)
        : null;

    const listUrl = mountElement.dataset.listUrl || '/email-templates';
    const uploadUrl = mountElement.dataset.uploadUrl;
    const videoUploadUrl = mountElement.dataset.videoUploadUrl;
    const isEdit = !!templateData?.uuid;

    // Create the save handler
    const handleSave = async (data) => {
        const saveUrl = mountElement.dataset.saveUrl;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(saveUrl, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message || 'Failed to save template');
        }

        return responseData;
    };

    // Create the image upload handler
    const handleImageUpload = async (file) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to upload image');
        }

        return response.json();
    };

    // Create the video upload handler
    const handleVideoUpload = async (videoFile, thumbnailFile = null) => {
        if (!videoUploadUrl) {
            throw new Error('Video upload URL is not configured');
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const formData = new FormData();
        formData.append('video', videoFile);
        if (thumbnailFile) {
            formData.append('thumbnail', thumbnailFile);
        }

        try {
            const response = await fetch(videoUploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to upload video');
            }

            return data;
        } catch (error) {
            console.error('Video upload error:', error);
            throw error;
        }
    };

    // Mount the React app
    const root = createRoot(mountElement);
    root.render(
        <React.StrictMode>
            <EmailBuilder
                initialData={initialData}
                templateData={templateData}
                listUrl={listUrl}
                onSave={handleSave}
                onImageUpload={handleImageUpload}
                onVideoUpload={handleVideoUpload}
            />
        </React.StrictMode>
    );
}
