import './bootstrap';
import 'iconify-icon/dist/iconify-icon.min.js';
import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

import focus from '@alpinejs/focus'
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import userGrowthChart from "./components/charts/user-growth-chart.js";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";
import SlugGenerator from "./components/slug-generator";
import * as Popper from '@popperjs/core';

// Make Popper available globally with the correct structure
window.Popper = Popper;

// Register a slug generator component with Alpine.
Alpine.data('slugGenerator', (initialTitle = '', initialSlug = '') => {
    return SlugGenerator.alpineComponent(initialTitle, initialSlug);
});

// Initialize Livewire.
Livewire.start();

// Register an advanced fields component with Alpine.
Alpine.data('advancedFields', (initialMeta = {}) => {
    return {
        fields: [],
        initialized: false,

        init() {
            // Convert initial meta object to array format.
            if (initialMeta && Object.keys(initialMeta).length > 0) {
                this.fields = Object.entries(initialMeta).map(([key, data]) => {
                    if (typeof data === 'object' && data !== null && data.value !== undefined) {
                        return {
                            key: key,
                            value: data.value || '',
                            type: data.type || 'input',
                            default_value: data.default_value || ''
                        };
                    } else {
                        // Handle legacy format where data is just the value
                        return {
                            key: key,
                            value: typeof data === 'string' ? data : '',
                            type: 'input',
                            default_value: ''
                        };
                    }
                });
            }

            // If no fields exist, add one empty field.
            if (this.fields.length === 0) {
                this.addField();
            }

            this.initialized = true;
        },

        addField() {
            this.fields.push({
                key: '',
                value: '',
                type: 'input',
                default_value: ''
            });
        },

        removeField(index) {
            if (this.fields.length > 1) {
                this.fields.splice(index, 1);
            }
        },

        get fieldsJson() {
            return this.initialized ? JSON.stringify(this.fields) : '[]';
        }
    };
});

// Alpine plugins.
Alpine.plugin(focus);
window.Alpine = Alpine;

// Global drawers.
window.openDrawer = function (drawerId) {
    // Try using the LaraDrawers registry if available.
    if (window.LaraDrawers && window.LaraDrawers[drawerId]) {
        window.LaraDrawers[drawerId].open = true;
        return;
    }

    // Try using Alpine.js directly.
    const drawerEl = document.querySelector(`[data-drawer-id="${drawerId}"]`);
    if (drawerEl && window.Alpine) {
        try {
            const alpineInstance = Alpine.getComponent(drawerEl);
            if (alpineInstance) {
                alpineInstance.open = true;
                return;
            }
        } catch (e) {
            console.error('Alpine error:', e);
        }
    }

    window.dispatchEvent(new CustomEvent('open-drawer-' + drawerId));
};

// Dark mode toggle.
document.addEventListener('DOMContentLoaded', function () {
    const html = document.documentElement;
    const darkModeToggle = document.getElementById('darkModeToggle');
    const header = document.getElementById('appHeader');

    // Update header background based on current mode
    function updateHeaderBg() {
        if (!header) return;
        const isDark = html.classList.contains('dark');
    }

    // Initialize dark mode
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        html.classList.add('dark');
    } else if (savedDarkMode === 'false') {
        html.classList.remove('dark');
    }

    updateHeaderBg();

    const observer = new MutationObserver(updateHeaderBg);
    observer.observe(html, {
        attributes: true,
        attributeFilter: ['class']
    });

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            updateHeaderBg();
        });
    }

    // Initialize sidebar state from localStorage if it exists
    if (window.Alpine) {
        const sidebarState = localStorage.getItem('sidebarToggle');
        if (sidebarState !== null) {
            document.addEventListener('alpine:initialized', () => {
                // Ensure the Alpine.js instance is ready
                setTimeout(() => {
                    const alpineData = document.querySelector('body').__x;
                    if (alpineData && typeof alpineData.$data !== 'undefined') {
                        alpineData.$data.sidebarToggle = JSON.parse(sidebarState);
                    }
                }, 0);
            });
        }
    }
});

// Initialize all drawer triggers on page load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-drawer-trigger]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            const drawerId = this.getAttribute('data-drawer-trigger');
            if (drawerId) {
                e.preventDefault();
                window.openDrawer(drawerId);
                return false;
            }
        });
    });
});


// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
    let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

// Document Loaded
document.addEventListener("DOMContentLoaded", () => {
    chart01();
    chart02();
    chart03();
    userGrowthChart();
    map01();

    const copyInput = document.getElementById("copy-input");
    if (copyInput) {
        // Select the copy button and input field
        const copyButton = document.getElementById("copy-button");
        const copyText = document.getElementById("copy-text");
        const websiteInput = document.getElementById("website-input");

        // Event listener for the copy button
        copyButton.addEventListener("click", () => {
            // Copy the input value to the clipboard
            navigator.clipboard.writeText(websiteInput.value).then(() => {
                // Change the text to "Copied"
                copyText.textContent = "Copied";

                // Reset the text back to "Copy" after 2 seconds
                setTimeout(() => {
                    copyText.textContent = "Copy";
                }, 2000);
            });
        });
    }
});

// Get the current year
const year = document.getElementById("year");
if (year) {
    year.textContent = new Date().getFullYear();
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    const searchButton = document.getElementById("search-button");

    // Function to focus the search input
    function focusSearchInput() {
        searchInput?.focus();
    }

    if (searchInput) {
        // Add click event listener to the search button
        searchButton.addEventListener("click", focusSearchInput);
    }

    // Add keyboard event listener for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
    document.addEventListener("keydown", function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key === "k") {
            event.preventDefault(); // Prevent the default browser behavior
            focusSearchInput();
        }
    });

    // Add keyboard event listener for "/" key
    document.addEventListener("keydown", function (event) {
        // Check if the active element is an input, textarea, or contenteditable element
        const activeElement = document.activeElement;
        const isInputField = activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.contentEditable === 'true';

        if (event.key === "/" && !isInputField) {
            event.preventDefault(); // Prevent the "/" character from being typed
            focusSearchInput();
        }
    });
});

// Toast notification helper function
window.showToast = function (variant, title, message) {
    // Dispatch the notify event that the toast component listens for
    window.dispatchEvent(new CustomEvent('notify', {
        detail: {
            variant, // 'success', 'error', 'warning', 'info'
            title,
            message
        }
    }));
};

// Import term drawer functionality
import './term-drawer.js';

// Prevent navigation if form is dirty (unsaved changes).
import './prevent-dirty-form-changes.js';

// Import menu handling functionality
import './components/menu-handler.js';
