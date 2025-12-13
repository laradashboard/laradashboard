/**
 * MediaLibraryService
 *
 * A service to bridge React components with the Blade-based media modal.
 * Provides a clean API for opening the media library and handling selections.
 *
 * Usage:
 *   import { mediaLibrary } from '../services/MediaLibraryService';
 *
 *   // Open media library and get selected files
 *   const files = await mediaLibrary.open({ multiple: false, allowedTypes: 'images' });
 *
 *   // Or use callback style
 *   mediaLibrary.open({
 *     onSelect: (files) => console.log('Selected:', files)
 *   });
 */

class MediaLibraryService {
    constructor() {
        this.modalId = 'laraBuilderMediaModal';
        this.callbackName = 'handleLaraBuilderMediaSelection';
        this.pendingResolve = null;
        this.pendingReject = null;
        this.isInitialized = false;
    }

    /**
     * Initialize the service - ensures modal exists in DOM
     */
    initialize() {
        if (this.isInitialized) return;

        // Set up global callback for media selection
        window[this.callbackName] = (files) => {
            if (this.pendingResolve) {
                this.pendingResolve(files);
                this.pendingResolve = null;
                this.pendingReject = null;
            }
        };

        // Listen for modal close without selection
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.pendingReject) {
                this.pendingReject(new Error('Selection cancelled'));
                this.pendingResolve = null;
                this.pendingReject = null;
            }
        });

        this.isInitialized = true;
    }

    /**
     * Open the media library modal
     *
     * @param {Object} options - Configuration options
     * @param {boolean} options.multiple - Allow multiple file selection (default: false)
     * @param {string} options.allowedTypes - File type filter: 'all', 'images', 'videos', 'audio', 'documents' (default: 'images')
     * @param {Function} options.onSelect - Callback when files are selected
     * @param {Function} options.onCancel - Callback when selection is cancelled
     * @returns {Promise<Array>} - Resolves with selected files array
     */
    open(options = {}) {
        this.initialize();

        const {
            multiple = false,
            allowedTypes = 'images',
            onSelect = null,
            onCancel = null
        } = options;

        return new Promise((resolve, reject) => {
            this.pendingResolve = (files) => {
                if (onSelect) onSelect(files);
                resolve(files);
            };

            this.pendingReject = (error) => {
                if (onCancel) onCancel();
                reject(error);
            };

            // Check if openMediaModal function exists (from Blade component)
            if (typeof window.openMediaModal === 'function') {
                // Check if modal element exists
                const modalEl = document.getElementById(this.modalId);
                if (!modalEl) {
                    console.warn(`[MediaLibrary] Modal element #${this.modalId} not found in DOM`);
                }

                window.openMediaModal(
                    this.modalId,
                    multiple,
                    allowedTypes,
                    this.callbackName
                );
            } else {
                const error = new Error('Media modal not available. Ensure x-media-modal component is included in your Blade template.');
                console.error('[MediaLibrary]', error.message);
                this.pendingReject(error);
            }
        });
    }

    /**
     * Open for single image selection
     * @returns {Promise<Object|null>} - Single file object or null
     */
    async selectImage() {
        try {
            const files = await this.open({ multiple: false, allowedTypes: 'images' });
            return files[0] || null;
        } catch (error) {
            console.error('[MediaLibrary] selectImage error:', error);
            return null;
        }
    }

    /**
     * Open for multiple image selection
     * @returns {Promise<Array>} - Array of file objects
     */
    async selectImages() {
        try {
            return await this.open({ multiple: true, allowedTypes: 'images' });
        } catch {
            return [];
        }
    }

    /**
     * Open for single video selection
     * @returns {Promise<Object|null>} - Single file object or null
     */
    async selectVideo() {
        try {
            const files = await this.open({ multiple: false, allowedTypes: 'videos' });
            return files[0] || null;
        } catch {
            return null;
        }
    }

    /**
     * Open for any file type
     * @param {boolean} multiple - Allow multiple selection
     * @returns {Promise<Array>} - Array of file objects
     */
    async selectFiles(multiple = false) {
        try {
            return await this.open({ multiple, allowedTypes: 'all' });
        } catch {
            return multiple ? [] : null;
        }
    }
}

// Singleton instance
export const mediaLibrary = new MediaLibraryService();

// Default export for flexibility
export default MediaLibraryService;
