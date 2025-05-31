/**
 * Slug Generator utility
 * Provides functionality for generating URL-friendly slugs from text
 */
const SlugGenerator = {
    /**
     * Convert text to a URL-friendly slug
     * 
     * @param {string} text - Text to convert to slug
     * @returns {string} - Converted slug
     */
    slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/&/g, '-and-')
            .replace(/[\s\W-]+/g, '-')
            .replace(/--+/g, '-')
            .replace(/^-+|-+$/g, '');
    },

    /**
     * Initialize Alpine.js component for slug generation
     * 
     * @param {string} initialTitle - Initial title value
     * @param {string} initialSlug - Initial slug value
     * @returns {object} - Alpine.js component data
     */
    alpineComponent(initialTitle = '', initialSlug = '') {
        return {
            title: initialTitle,
            slug: initialSlug,
            isSlugManuallyChanged: false,
            showSlugEdit: false,
            originalSlug: initialSlug,

            /**
             * Generate a slug from the current title
             */
            generateSlug() {
                if (this.title) {
                    this.slug = SlugGenerator.slugify(this.title);
                    this.originalSlug = this.slug;
                    this.isSlugManuallyChanged = false;
                }
            },

            /**
             * Toggle slug edit mode
             */
            toggleSlugEdit() {
                this.showSlugEdit = !this.showSlugEdit;
                if (this.showSlugEdit) {
                    // Focus the slug input when showing it
                    setTimeout(() => {
                        document.getElementById('slug').focus();
                    }, 50);
                }
            },

            /**
             * Initialize the component
             */
            init() {
                // Set up a watcher for the title
                this.$watch('title', (value) => {
                    // Auto-generate slug if it hasn't been manually edited
                    if (!this.isSlugManuallyChanged) {
                        this.slug = SlugGenerator.slugify(value);
                    }
                });

                // Set up a watcher for the slug to detect manual changes
                this.$watch('slug', (value) => {
                    // Compare with what would be auto-generated
                    const autoSlug = SlugGenerator.slugify(this.title);
                    // If they differ and it's not empty, mark as manually changed
                    if (value !== autoSlug && value !== '') {
                        this.isSlugManuallyChanged = true;
                    }
                    // If it's reset to match auto-generated or empty, unmark
                    if (value === autoSlug || value === '') {
                        this.isSlugManuallyChanged = false;
                    }
                });
            }
        };
    }
};

export default SlugGenerator;