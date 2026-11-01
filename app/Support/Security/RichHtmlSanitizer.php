<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Removes active content from author-controlled LaraBuilder HTML before it is rendered.
 *
 * The goal is to stop stored XSS while leaving layout markup (tables, inline styles,
 * <style> blocks, ids, data-* attributes, email wrappers) byte-for-byte unchanged, so this
 * is a small, quote-aware tokenizer instead of a DOM round-trip:
 *
 *  - script / object / embed / applet / frame / base / SVG animation elements are removed;
 *  - iframes are kept only when they point at an https:// URL (no srcdoc);
 *  - on* event handlers and Alpine/Livewire directives (x-*, @*, :*, wire:*) are removed;
 *  - javascript:/vbscript: values and non-image data: URLs are removed;
 *  - <meta http-equiv="refresh"> is removed;
 *  - '<' inside raw-text elements (style, textarea, title, noscript, ...) is escaped so their
 *    content cannot be re-parsed as markup in either HTML or SVG/MathML context.
 *
 * Builder placeholders (<div data-lara-block=".." data-props='..'>) pass through untouched:
 * data-* attributes are never inspected, and block props are cleaned separately with
 * sanitizeBlockProps() right before each block is rendered.
 */
final class RichHtmlSanitizer
{
    /**
     * Block types whose string props are never interpreted as HTML: the code block escapes its
     * source in the editor and in every renderer, and must keep `<script>` samples intact.
     */
    private const RAW_TEXT_BLOCKS = ['code'];

    /** Props that hold nested blocks; those are cleaned as blocks of their own type. */
    private const NESTED_BLOCK_KEYS = ['children', 'blocks'];

    /** Elements removed together with their content. */
    private const DROPPED_ELEMENTS = ['script', 'object', 'applet', 'frameset', 'noembed', 'noframes'];

    /** Tags removed on their own (void elements, or elements whose content is harmless). */
    private const DROPPED_TAGS = [
        'embed', 'frame', 'base', 'param', 'plaintext',
        'set', 'animate', 'animatemotion', 'animatetransform',
    ];

    /** Elements whose content the browser treats as text up to the matching end tag. */
    private const RAW_TEXT_ELEMENTS = ['style', 'textarea', 'title', 'xmp', 'noscript', 'iframe'];

    /** Attributes that carry a URL (or, for SVG animation, a value that may become one). */
    private const URL_ATTRIBUTES = [
        'href', 'src', 'action', 'formaction', 'xlink:href', 'data', 'poster', 'background',
        'cite', 'codebase', 'dynsrc', 'lowsrc', 'values', 'to', 'from', 'by',
    ];

    /** HTML whitespace (PCRE \s also matches \v, which HTML does not). */
    private const WS = '[\t\n\f\r ]';

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // No tag can exist without '<'; keeps plain text and entity-only strings untouched.
        if (! str_contains($html, '<')) {
            return $html;
        }

        return (new self())->sanitize($html);
    }

    /**
     * Clean a full stored document (post content, email body) that may contain builder placeholders.
     */
    public static function neutralizeDocument(string $html): string
    {
        return self::clean($html);
    }

    /**
     * Clean the props of a single block before its renderer (or the editor) uses them.
     *
     * Every string prop is treated as potential HTML, so core, module and `make:block`
     * blocks are covered without keeping a per-block field list. URL-like props
     * (link, url, href, src, links.*) are blanked when they hold an unsafe scheme.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    public static function sanitizeBlockProps(string $type, array $props): array
    {
        if (in_array($type, self::RAW_TEXT_BLOCKS, true)) {
            return $props;
        }

        return self::sanitizeProps($props, true);
    }

    /**
     * Clean every block of a stored LaraBuilder design (a block list, or ['blocks' => [...], ...]).
     *
     * @param  array<mixed>  $design
     * @return array<mixed>
     */
    public static function sanitizeDesign(array $design): array
    {
        foreach ($design as $key => $node) {
            if (! is_array($node)) {
                continue;
            }

            if (isset($node['type']) && is_string($node['type']) && isset($node['props']) && is_array($node['props'])) {
                $props = self::sanitizeBlockProps($node['type'], $node['props']);

                foreach (self::NESTED_BLOCK_KEYS as $nestedKey) {
                    if (isset($props[$nestedKey]) && is_array($props[$nestedKey])) {
                        $props[$nestedKey] = self::sanitizeDesign($props[$nestedKey]);
                    }
                }

                $node['props'] = $props;
                $design[$key] = $node;

                continue;
            }

            // A container: the top-level ['blocks' => ...] wrapper or a column (list of blocks).
            $design[$key] = self::sanitizeDesign($node);
        }

        return $design;
    }

    /**
     * True when a URL is safe to emit in href/src: relative, #fragment, http(s), mailto, tel,
     * or a raster data: image. Rejects javascript:, vbscript:, data:text/html, etc.
     */
    public static function isSafeUrl(string $url): bool
    {
        $normalized = self::normalizeAttributeValue($url);

        if ($normalized === '' || ! preg_match('/^[a-z][a-z0-9+.\-]*:/', $normalized)) {
            return true; // relative URL, path, query or fragment
        }

        if (preg_match('#^data:image/(?:png|jpe?g|gif|webp|avif);#', $normalized) === 1) {
            return true;
        }

        return preg_match('/^(?:https?|mailto|tel|sms):/', $normalized) === 1;
    }

    /**
     * @param  array<mixed>  $props
     * @return array<mixed>
     */
    private static function sanitizeProps(array $props, bool $isTopLevel, bool $insideLinks = false): array
    {
        foreach ($props as $key => $value) {
            if (is_array($value)) {
                if (! ($isTopLevel && in_array($key, self::NESTED_BLOCK_KEYS, true))) {
                    $props[$key] = self::sanitizeProps($value, false, is_string($key) && strtolower($key) === 'links');
                }

                continue;
            }

            if (! is_string($value) || $value === '') {
                continue;
            }

            if ($insideLinks || (is_string($key) && preg_match('/(?:url|link|href|src)$/i', $key) === 1)) {
                if (! self::isSafeUrl($value)) {
                    $props[$key] = '';
                }

                continue;
            }

            $props[$key] = self::clean($value);
        }

        return $props;
    }

    public function sanitize(string $html): string
    {
        $html = str_replace("\0", '', $html);
        $length = strlen($html);
        $out = '';
        $pos = 0;

        while (($lt = strpos($html, '<', $pos)) !== false) {
            $out .= substr($html, $pos, $lt - $pos);
            $next = $html[$lt + 1] ?? '';

            if (str_starts_with(substr($html, $lt, 4), '<!--')) {
                $pos = $this->commentEnd($html, $lt);
                $out .= substr($html, $lt, $pos - $lt);

                continue;
            }

            if ($next === '!' || $next === '?' || ($next === '/' && ! ctype_alpha($html[$lt + 2] ?? ''))) {
                // Bogus comment / doctype: the browser ends it at the first '>'.
                $gt = strpos($html, '>', $lt);
                $pos = $gt === false ? $length : $gt + 1;
                $out .= substr($html, $lt, $pos - $lt);

                continue;
            }

            if (! ctype_alpha($next) && $next !== '/') {
                $out .= '<';
                $pos = $lt + 1;

                continue;
            }

            if (! preg_match($this->tagPattern(), $html, $m, 0, $lt)) {
                // Unterminated tag: the browser would drop it; we make it inert text.
                $out .= '&lt;';
                $pos = $lt + 1;

                continue;
            }

            $pos = $lt + strlen($m[0]);
            $isEndTag = $m[1] === '/';
            $name = strtolower($m[2]);

            if ($isEndTag) {
                if (! in_array($name, self::DROPPED_ELEMENTS, true) && ! in_array($name, self::DROPPED_TAGS, true)) {
                    $out .= '</'.$m[2].'>';
                }

                continue;
            }

            if (in_array($name, self::DROPPED_ELEMENTS, true)) {
                $pos = $this->endOfElement($html, $name, $pos);

                continue;
            }

            if (in_array($name, self::DROPPED_TAGS, true)) {
                continue;
            }

            $attributes = $this->parseAttributes($m[3]);

            if (! $this->keepElement($name, $attributes)) {
                if (in_array($name, self::RAW_TEXT_ELEMENTS, true)) {
                    $pos = $this->endOfElement($html, $name, $pos);
                }

                continue;
            }

            $out .= $this->rebuildTag($m[0], $m[2], $m[3], $attributes);

            if (in_array($name, self::RAW_TEXT_ELEMENTS, true)) {
                $close = $this->findEndTag($html, $name, $pos);
                $end = $close ?? $length;
                $out .= str_replace('<', '&lt;', substr($html, $pos, $end - $pos));
                $pos = $end;
            }
        }

        return $out.substr($html, $pos);
    }

    /**
     * Matches a start or end tag at the current offset, following the HTML tokenizer:
     * attribute values may contain '>' when quoted, names may contain quotes.
     */
    private function tagPattern(): string
    {
        $ws = self::WS;
        $attr = "(?:[{$this->wsChars()}/]*+(?:=?[^\\t\\n\\f\\r />=]++|=)(?:{$ws}*+={$ws}*+(?:\"[^\"]*+\"|'[^']*+'|[^\\t\\n\\f\\r >]*+))?+)";

        return "~\\G<(/?)([a-zA-Z][^\\t\\n\\f\\r />]*+)((?:{$attr})*+)[{$this->wsChars()}/]*+>~";
    }

    private function wsChars(): string
    {
        return '\t\n\f\r ';
    }

    /**
     * @return list<array{raw: string, name: string, value: string|null, ambiguous: bool}>
     */
    private function parseAttributes(string $source): array
    {
        $ws = self::WS;
        $pattern = "~[{$this->wsChars()}/]*+(=?[^\\t\\n\\f\\r />=]++|=)(?:{$ws}*+={$ws}*+(\"[^\"]*+\"|'[^']*+'|[^\\t\\n\\f\\r >]*+))?+~";

        preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);

        $attributes = [];

        foreach ($matches as $match) {
            if ($match[0] === '') {
                continue;
            }

            $value = $match[2] ?? null;
            $quoted = $value !== null && $value !== '' && ($value[0] === '"' || $value[0] === "'");

            if ($quoted) {
                $value = substr($value, 1, -1);
            }

            $attributes[] = [
                'raw' => $match[0],
                'name' => strtolower($match[1]),
                'value' => $value,
                'ambiguous' => ! $quoted && $value !== null && preg_match('/[\/="\'`<]/', $value) === 1,
            ];
        }

        return $attributes;
    }

    /**
     * @param  list<array{raw: string, name: string, value: string|null}>  $attributes
     */
    private function keepElement(string $name, array $attributes): bool
    {
        if ($name === 'meta') {
            foreach ($attributes as $attribute) {
                if ($attribute['name'] === 'http-equiv' && str_contains(strtolower((string) $attribute['value']), 'refresh')) {
                    return false;
                }
            }
        }

        if ($name === 'iframe') {
            foreach ($attributes as $attribute) {
                if ($attribute['name'] === 'src') {
                    return str_starts_with(self::normalizeAttributeValue((string) $attribute['value']), 'https://');
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Re-emits the original tag text, minus any attributes that are unsafe.
     *
     * @param  list<array{raw: string, name: string, value: string|null, ambiguous: bool}>  $attributes
     */
    private function rebuildTag(string $original, string $tagName, string $attributeSource, array $attributes): string
    {
        $kept = '';
        $changed = false;

        foreach ($attributes as $attribute) {
            if ($this->isUnsafeAttribute($attribute['name'], $attribute['value'])) {
                $changed = true;

                continue;
            }

            if ($attribute['ambiguous']) {
                // An unquoted value like `x/onerror=alert(1)` is one attribute to a browser, but a
                // lenient parser (some mail clients) may split it. Quote it so every parser agrees.
                $name = substr($attribute['raw'], 0, (int) strpos($attribute['raw'], '='));
                $kept .= rtrim($name).'="'.str_replace('"', '&quot;', (string) $attribute['value']).'"';
                $changed = true;

                continue;
            }

            $kept .= $attribute['raw'];
        }

        if (! $changed) {
            return $original;
        }

        $selfClosing = str_ends_with(rtrim(substr($original, 0, -1)), '/') ? ' /' : '';

        return '<'.$tagName.$kept.$selfClosing.'>';
    }

    private function isUnsafeAttribute(string $name, ?string $value): bool
    {
        // data-* is never executed by the browser and carries builder placeholder props.
        if (str_starts_with($name, 'data-')) {
            return false;
        }

        if ((str_starts_with($name, 'on') && $name !== 'open')
            || str_starts_with($name, 'x-')
            || str_starts_with($name, 'wire:')
            || str_starts_with($name, '@')
            || str_starts_with($name, ':')
            || $name === 'srcdoc'
        ) {
            return true;
        }

        if ($value === null || $value === '') {
            return false;
        }

        $normalized = self::normalizeAttributeValue($value);

        if (preg_match('/(?:^|[;,])(?:javascript|vbscript|livescript):/', $normalized) === 1) {
            return true;
        }

        if ($name === 'style') {
            return preg_match('/expression\(|-moz-binding|behavior:/', $normalized) === 1;
        }

        return in_array($name, self::URL_ATTRIBUTES, true) && ! self::isSafeUrl($value);
    }

    /**
     * Decode entities the way the browser does inside attributes, drop whitespace/control
     * characters (which browsers ignore inside URL schemes), and lowercase.
     */
    private static function normalizeAttributeValue(string $value): string
    {
        // Browsers accept numeric references without the trailing ';'.
        $value = preg_replace(['/&#(x[0-9a-f]+)(?![0-9a-f;])/i', '/&#([0-9]+)(?![0-9;])/'], '&#$1;', $value) ?? $value;
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\x00-\x20\x7F]+/', '', $value) ?? $value;

        return strtolower($value);
    }

    /** Position right after the comment that starts at $start. */
    private function commentEnd(string $html, int $start): int
    {
        $body = $start + 4;

        if (substr($html, $body, 1) === '>') {
            return $body + 1; // <!-->
        }

        if (substr($html, $body, 2) === '->') {
            return $body + 2; // <!--->
        }

        if (! preg_match('/--!?>/', $html, $m, PREG_OFFSET_CAPTURE, $body)) {
            return strlen($html);
        }

        return $m[0][1] + strlen($m[0][0]);
    }

    /** Offset of the matching end tag "</name" (followed by whitespace, '/' or '>'), or null. */
    private function findEndTag(string $html, string $name, int $from): ?int
    {
        if (! preg_match('~</'.preg_quote($name, '~').'(?=[\t\n\f\r />])~i', $html, $m, PREG_OFFSET_CAPTURE, $from)) {
            return null;
        }

        return $m[0][1];
    }

    /** Position right after the end tag that closes $name, or the end of the input. */
    private function endOfElement(string $html, string $name, int $from): int
    {
        $close = $this->findEndTag($html, $name, $from);

        if ($close === null) {
            return strlen($html);
        }

        $gt = strpos($html, '>', $close);

        return $gt === false ? strlen($html) : $gt + 1;
    }
}
