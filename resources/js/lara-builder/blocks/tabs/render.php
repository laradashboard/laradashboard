<?php

/**
 * Tabs Block - Server-side Renderer
 *
 * Page: renders interactive tabbed content with vanilla JS tab switching.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $tabs = $props['tabs'] ?? [
        ['label' => 'Tab 1', 'heading' => 'First Tab', 'description' => 'Content for the first tab.', 'items' => [], 'badges' => []],
    ];
    $activeTab = (int) ($props['activeTab'] ?? 0);
    $tabStyle = $props['tabStyle'] ?? 'pills';
    $tabAlignment = $props['tabAlignment'] ?? 'center';
    $accentColor = $props['accentColor'] ?? '#3b82f6';
    $contentPadding = $props['contentPadding'] ?? '24px';
    $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
    $tabBgColor = $props['tabBgColor'] ?? '#f3f4f6';
    $customClass = $props['customClass'] ?? '';

    $groupId = 'tabs-' . ($blockId ?? uniqid());
    $blockClasses = trim("lb-block lb-tabs {$customClass}");

    // Determine alignment justify
    $alignJustify = match ($tabAlignment) {
        'left' => 'flex-start',
        'stretch' => 'stretch',
        default => 'center',
    };

    // Build tab buttons
    $tabButtonsHtml = '';
    foreach ($tabs as $index => $tab) {
        $isActive = $index === $activeTab;
        $label = e($tab['label'] ?? 'Tab ' . ($index + 1));
        $activeClass = $isActive ? 'lb-tab-btn-active' : '';
        $flex = $tabAlignment === 'stretch' ? 'flex: 1;' : '';

        // Base button styles
        $btnStyles = "padding: 10px 20px; font-size: 14px; cursor: pointer; border: none; transition: all 0.2s ease; text-align: center; white-space: nowrap; {$flex}";

        if ($tabStyle === 'pills') {
            $btnStyles .= ' border-radius: 9999px;';
            $btnStyles .= $isActive
                ? " background-color: {$accentColor}; color: #ffffff; font-weight: 600;"
                : " background-color: {$tabBgColor}; color: #374151; font-weight: 400;";
        } elseif ($tabStyle === 'underline') {
            $btnStyles .= ' border-radius: 0; background-color: transparent; padding-bottom: 8px;';
            $btnStyles .= $isActive
                ? " color: {$accentColor}; font-weight: 600; border-bottom: 3px solid {$accentColor};"
                : " color: #6b7280; font-weight: 400; border-bottom: 3px solid transparent;";
        } elseif ($tabStyle === 'buttons') {
            $btnStyles .= ' border-radius: 8px;';
            $btnStyles .= $isActive
                ? " background-color: {$accentColor}; color: #ffffff; font-weight: 600; border: 2px solid {$accentColor};"
                : " background-color: transparent; color: #374151; font-weight: 400; border: 2px solid #d1d5db;";
        }

        $tabButtonsHtml .= sprintf(
            '<button type="button" class="lb-tab-btn %s" data-tab-btn="%d" style="%s">%s</button>',
            $activeClass,
            $index,
            e($btnStyles),
            $label
        );
    }

    // Build tab panels
    $tabPanelsHtml = '';
    foreach ($tabs as $index => $tab) {
        $isActive = $index === $activeTab;
        $display = $isActive ? 'block' : 'none';
        $heading = e($tab['heading'] ?? '');
        $description = e($tab['description'] ?? '');
        $items = $tab['items'] ?? [];
        $badges = $tab['badges'] ?? [];

        $panelContent = '';

        if (! empty($heading)) {
            $panelContent .= sprintf(
                '<h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 12px 0;">%s</h3>',
                $heading
            );
        }

        if (! empty($description)) {
            $panelContent .= sprintf(
                '<p style="font-size: 14px; color: #6b7280; margin: 0 0 16px 0; line-height: 1.6;">%s</p>',
                $description
            );
        }

        if (! empty($items)) {
            $listItems = '';
            foreach ($items as $item) {
                $listItems .= sprintf(
                    '<li style="font-size: 14px; color: #374151; margin-bottom: 6px; line-height: 1.5;">%s</li>',
                    e($item)
                );
            }
            $panelContent .= sprintf(
                '<ul style="margin: 0 0 16px 0; padding: 0 0 0 20px; list-style-type: disc;">%s</ul>',
                $listItems
            );
        }

        if (! empty($badges)) {
            $badgeHtml = '';
            foreach ($badges as $badge) {
                $badgeHtml .= sprintf(
                    '<span style="display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 500; border-radius: 9999px; background-color: %s15; color: %s; border: 1px solid %s30;">%s</span>',
                    e($accentColor),
                    e($accentColor),
                    e($accentColor),
                    e($badge)
                );
            }
            $panelContent .= sprintf(
                '<div style="display: flex; gap: 8px; flex-wrap: wrap;">%s</div>',
                $badgeHtml
            );
        }

        $tabPanelsHtml .= sprintf(
            '<div class="lb-tab-panel" data-tab-panel="%d" style="display: %s; padding: %s;">%s</div>',
            $index,
            $display,
            e($contentPadding),
            $panelContent
        );
    }

    // Tab button row styles
    $tabRowBorderBottom = $tabStyle === 'underline' ? 'border-bottom: 1px solid #e5e7eb;' : '';
    $tabRowGap = $tabStyle === 'underline' ? '0' : '8px';

    $html = sprintf(
        '<div class="%s" data-tab-group="%s" style="background-color: %s;"><div class="lb-tab-buttons" style="display: flex; gap: %s; justify-content: %s; margin-bottom: 16px; flex-wrap: wrap; %s">%s</div><div class="lb-tab-panels">%s</div></div>',
        e($blockClasses),
        e($groupId),
        e($backgroundColor),
        $tabRowGap,
        $alignJustify,
        $tabRowBorderBottom,
        $tabButtonsHtml,
        $tabPanelsHtml
    );

    // Vanilla JS for tab switching
    $script = <<<JS
<script>
(function(){
    var group = document.querySelector('[data-tab-group="{$groupId}"]');
    if (!group) return;
    var btns = group.querySelectorAll('[data-tab-btn]');
    var panels = group.querySelectorAll('[data-tab-panel]');
    var tabStyle = '{$tabStyle}';
    var accentColor = '{$accentColor}';
    var tabBgColor = '{$tabBgColor}';

    btns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var idx = this.getAttribute('data-tab-btn');

            // Hide all panels
            panels.forEach(function(p) { p.style.display = 'none'; });

            // Show target panel
            var target = group.querySelector('[data-tab-panel="' + idx + '"]');
            if (target) target.style.display = 'block';

            // Update button styles
            btns.forEach(function(b) {
                b.classList.remove('lb-tab-btn-active');
                if (tabStyle === 'pills') {
                    b.style.backgroundColor = tabBgColor;
                    b.style.color = '#374151';
                    b.style.fontWeight = '400';
                } else if (tabStyle === 'underline') {
                    b.style.color = '#6b7280';
                    b.style.fontWeight = '400';
                    b.style.borderBottom = '3px solid transparent';
                } else if (tabStyle === 'buttons') {
                    b.style.backgroundColor = 'transparent';
                    b.style.color = '#374151';
                    b.style.fontWeight = '400';
                    b.style.border = '2px solid #d1d5db';
                }
            });

            // Set active button styles
            this.classList.add('lb-tab-btn-active');
            if (tabStyle === 'pills') {
                this.style.backgroundColor = accentColor;
                this.style.color = '#ffffff';
                this.style.fontWeight = '600';
            } else if (tabStyle === 'underline') {
                this.style.color = accentColor;
                this.style.fontWeight = '600';
                this.style.borderBottom = '3px solid ' + accentColor;
            } else if (tabStyle === 'buttons') {
                this.style.backgroundColor = accentColor;
                this.style.color = '#ffffff';
                this.style.fontWeight = '600';
                this.style.border = '2px solid ' + accentColor;
            }
        });
    });
})();
</script>
JS;

    return $html . $script;
};
