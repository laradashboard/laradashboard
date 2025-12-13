/**
 * LaraBuilder Translation System
 *
 * Works exactly like Laravel's __() function + WordPress-style helpers.
 * Uses the string itself as the key, just like Laravel JSON translations.
 *
 * ## Setup (in Blade layout):
 *
 * ```blade
 * <script>
 *     window.__translations = @json(app('translator')->getLoader()->load(app()->getLocale(), '*', '*'));
 * </script>
 * ```
 *
 * ## Available Functions:
 *
 * - __()      - Basic translation (like Laravel/WordPress)
 * - _n()      - Pluralization (like WordPress __n)
 * - _x()      - Translation with context (like WordPress _x)
 * - _nx()     - Pluralization with context
 * - sprintf() - String formatting (like WordPress/PHP sprintf)
 *
 * ## Usage Examples:
 *
 * ```jsx
 * import { __, _n, _x, sprintf } from '@lara-builder/i18n';
 *
 * // Basic translation
 * __('Save')
 * __('Hello :name', { name: 'John' })
 *
 * // Pluralization
 * _n('1 item', ':count items', count, { count })
 *
 * // Context-based translation
 * _x('Post', 'verb')      // "Post" as action
 * _x('Post', 'noun')      // "Post" as content type
 *
 * // sprintf formatting
 * sprintf(__('Welcome %s, you have %d messages'), name, count)
 * ```
 */

let translations = {};

/**
 * Initialize translations from window or data attribute
 *
 * @param {Object} trans - Translation key-value pairs
 */
export function initTranslations(trans = null) {
    if (trans) {
        translations = trans;
    } else if (typeof window !== 'undefined') {
        translations = window.__translations || window.LaraBuilderTranslations || {};
    }
}

/**
 * Get raw translation without replacements
 *
 * @param {string} key - Translation key
 * @returns {string} Translated string or key
 */
function getTranslation(key) {
    return translations[key] ?? key;
}

/**
 * Apply Laravel-style :placeholder replacements
 *
 * @param {string} text - Text with placeholders
 * @param {Object} replacements - Key-value replacements
 * @returns {string} Text with replacements applied
 */
function applyReplacements(text, replacements) {
    if (typeof text !== 'string' || Object.keys(replacements).length === 0) {
        return text;
    }

    Object.entries(replacements).forEach(([placeholder, value]) => {
        text = text.replace(new RegExp(`:${placeholder}`, 'gi'), String(value));
    });

    return text;
}

/**
 * Translate a string - works exactly like Laravel's __() function
 *
 * @param {string} key - The string to translate
 * @param {Object} replacements - Placeholder replacements { name: 'John' }
 * @returns {string} Translated string
 *
 * @example
 * __('Save')
 * __('Hello :name', { name: 'John' })
 */
export function __(key, replacements = {}) {
    const text = getTranslation(key);
    return applyReplacements(text, replacements);
}

/**
 * Pluralize a string - like WordPress _n()
 *
 * @param {string} singular - Singular form
 * @param {string} plural - Plural form
 * @param {number} count - The count to determine singular/plural
 * @param {Object} replacements - Placeholder replacements
 * @returns {string} Translated singular or plural form
 *
 * @example
 * _n('1 item', ':count items', 1)           // "1 item"
 * _n('1 item', ':count items', 5, { count: 5 }) // "5 items"
 * _n('1 block selected', ':count blocks selected', count, { count })
 */
export function _n(singular, plural, count, replacements = {}) {
    const key = count === 1 ? singular : plural;
    const text = getTranslation(key);
    return applyReplacements(text, { count, ...replacements });
}

/**
 * Translate with context - like WordPress _x()
 *
 * Useful when the same word has different meanings in different contexts.
 * The context helps translators understand how the word is used.
 *
 * @param {string} text - Text to translate
 * @param {string} context - Context for translators
 * @param {Object} replacements - Placeholder replacements
 * @returns {string} Translated string
 *
 * @example
 * _x('Post', 'verb')           // "Post" as in "Post a comment"
 * _x('Post', 'noun')           // "Post" as in "Blog post"
 * _x('Block', 'editor_block')  // "Block" in editor context
 * _x('Block', 'prevent')       // "Block" as in "Block user"
 */
export function _x(text, context, replacements = {}) {
    // Try context-specific key first: "text|context"
    const contextKey = `${text}|${context}`;
    const translated = translations[contextKey] ?? translations[text] ?? text;
    return applyReplacements(translated, replacements);
}

/**
 * Pluralize with context - like WordPress _nx()
 *
 * Combines pluralization and context for complex translations.
 *
 * @param {string} singular - Singular form
 * @param {string} plural - Plural form
 * @param {number} count - The count
 * @param {string} context - Context for translators
 * @param {Object} replacements - Placeholder replacements
 * @returns {string} Translated string
 *
 * @example
 * _nx('1 block', ':count blocks', count, 'editor', { count })
 */
export function _nx(singular, plural, count, context, replacements = {}) {
    const key = count === 1 ? singular : plural;
    const contextKey = `${key}|${context}`;
    const translated = translations[contextKey] ?? translations[key] ?? key;
    return applyReplacements(translated, { count, ...replacements });
}

/**
 * sprintf - Format a string with placeholders (like PHP/WordPress sprintf)
 *
 * Supports: %s (string), %d (integer), %f (float), %% (literal %)
 *
 * @param {string} format - Format string with placeholders
 * @param {...any} args - Values to insert
 * @returns {string} Formatted string
 *
 * @example
 * sprintf('Hello %s', 'World')                    // "Hello World"
 * sprintf('You have %d messages', 5)              // "You have 5 messages"
 * sprintf('%s has %d items', 'Cart', 3)           // "Cart has 3 items"
 * sprintf('Price: $%.2f', 19.99)                  // "Price: $19.99"
 * sprintf('100%% complete')                       // "100% complete"
 */
export function sprintf(format, ...args) {
    if (typeof format !== 'string') return format;

    let argIndex = 0;

    return format.replace(/%(%|s|d|f|\.\d+f)/g, (match) => {
        // %% = literal %
        if (match === '%%') return '%';

        const arg = args[argIndex++];

        if (arg === undefined) return match;

        // %s = string
        if (match === '%s') return String(arg);

        // %d = integer
        if (match === '%d') return parseInt(arg, 10).toString();

        // %f = float
        if (match === '%f') return parseFloat(arg).toString();

        // %.Nf = float with N decimal places
        const floatMatch = match.match(/^%\.(\d+)f$/);
        if (floatMatch) {
            return parseFloat(arg).toFixed(parseInt(floatMatch[1], 10));
        }

        return match;
    });
}

/**
 * Escape HTML entities for safe output
 *
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 *
 * @example
 * esc_html('<script>alert("xss")</script>')
 * // Returns: &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;
 */
export function esc_html(text) {
    if (typeof text !== 'string') return text;

    const escapeMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };

    return text.replace(/[&<>"']/g, (char) => escapeMap[char]);
}

/**
 * Translate and escape HTML - safe for output
 *
 * @param {string} key - Translation key
 * @param {Object} replacements - Placeholder replacements
 * @returns {string} Translated and escaped string
 *
 * @example
 * esc_html__('User <script> input')
 */
export function esc_html__(key, replacements = {}) {
    return esc_html(__(key, replacements));
}

/**
 * React hook for translations
 *
 * @returns {Object} Translation functions
 *
 * @example
 * const { __, _n, sprintf } = useTranslation();
 */
export function useTranslation() {
    return {
        __,
        _n,
        _x,
        _nx,
        sprintf,
        esc_html,
        esc_html__,
    };
}

// Auto-initialize from window on load
if (typeof window !== 'undefined') {
    initTranslations();

    // Expose globally for non-module usage
    window.__ = window.__ || __;
    window._n = window._n || _n;
    window._x = window._x || _x;
    window.sprintf = window.sprintf || sprintf;
}

export default __;
