/**
 * Accordion Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'accordion';
    const blockClasses = buildBlockClasses(type, props);
    const items = props.items || [{ title: 'Accordion Item', content: 'Content goes here...' }];
    const accordionId = `accordion-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
    const borderColor = props.layoutStyles?.border?.color || props.borderColor || '#e5e7eb';
    const borderRadius = props.layoutStyles?.border?.radius || props.borderRadius || '8px';
    const headerBgColor = props.headerBgColor || '#ffffff';
    const headerBgColorActive = props.headerBgColorActive || '#f9fafb';
    const headerPadding = props.headerPadding || '16px';
    const titleColor = props.layoutStyles?.typography?.color || props.titleColor || '#1f2937';
    const titleFontSize = props.layoutStyles?.typography?.fontSize || props.titleFontSize || '16px';
    const titleFontWeight = props.layoutStyles?.typography?.fontWeight || props.titleFontWeight || '600';
    const contentBgColor = props.contentBgColor || '#ffffff';
    const contentColor = props.contentColor || '#4b5563';
    const contentFontSize = props.contentFontSize || '14px';
    const contentPadding = props.contentPadding || '16px';
    const iconColor = props.iconColor || '#6b7280';
    const iconPosition = props.iconPosition || 'right';
    const transitionDuration = props.transitionDuration || 200;
    const independentToggle = props.independentToggle || false;

    const accordionItems = items.map((item, index) => {
        const isLast = index === items.length - 1;
        const itemId = `${accordionId}-item-${index}`;
        return `
            <div class="lb-accordion-item" data-index="${index}" style="border-bottom: ${isLast ? 'none' : `1px solid ${borderColor}`};">
                <button type="button" class="lb-accordion-header" data-target="${itemId}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: ${headerPadding}; background-color: ${headerBgColor}; border: none; cursor: pointer; text-align: left; transition: background-color 0.2s; flex-direction: ${iconPosition === 'left' ? 'row-reverse' : 'row'};">
                    <span style="font-weight: ${titleFontWeight}; font-size: ${titleFontSize}; color: ${titleColor}; flex: 1;">${item.title}</span>
                    <span class="lb-accordion-icon" style="color: ${iconColor}; transition: transform ${transitionDuration}ms ease; ${iconPosition === 'left' ? 'margin-right: 12px;' : 'margin-left: 12px;'}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </span>
                </button>
                <div id="${itemId}" class="lb-accordion-content" style="max-height: 0; overflow: hidden; transition: max-height ${transitionDuration}ms ease-in-out;">
                    <div style="padding: ${contentPadding}; background-color: ${contentBgColor}; color: ${contentColor}; font-size: ${contentFontSize}; line-height: 1.6;">
                        ${item.content}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    // Block-specific styles
    const blockStyles = [`overflow: hidden`];

    // Only add if not controlled by layoutStyles
    if (!props.layoutStyles?.border) {
        blockStyles.push(`border: 1px solid ${borderColor}`);
        blockStyles.push(`border-radius: ${borderRadius}`);
    }

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    return `
        <div class="${blockClasses}" id="${accordionId}" data-independent="${independentToggle}" style="${mergedStyles}">
            ${accordionItems}
        </div>
        <script>
            (function() {
                const accordion = document.getElementById('${accordionId}');
                if (!accordion) return;
                const isIndependent = accordion.dataset.independent === 'true';
                const headers = accordion.querySelectorAll('.lb-accordion-header');

                headers.forEach(header => {
                    header.addEventListener('click', function() {
                        const targetId = this.dataset.target;
                        const content = document.getElementById(targetId);
                        const icon = this.querySelector('.lb-accordion-icon');
                        const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';

                        if (!isIndependent) {
                            // Close all other items
                            accordion.querySelectorAll('.lb-accordion-content').forEach(c => {
                                c.style.maxHeight = '0px';
                            });
                            accordion.querySelectorAll('.lb-accordion-icon').forEach(i => {
                                i.style.transform = 'rotate(0deg)';
                            });
                            accordion.querySelectorAll('.lb-accordion-header').forEach(h => {
                                h.style.backgroundColor = '${headerBgColor}';
                            });
                        }

                        if (isOpen) {
                            content.style.maxHeight = '0px';
                            icon.style.transform = 'rotate(0deg)';
                            this.style.backgroundColor = '${headerBgColor}';
                        } else {
                            content.style.maxHeight = content.scrollHeight + 'px';
                            icon.style.transform = 'rotate(180deg)';
                            this.style.backgroundColor = '${headerBgColorActive}';
                        }
                    });
                });

                // Open first item by default
                const firstHeader = headers[0];
                if (firstHeader) {
                    firstHeader.click();
                }
            })();
        </script>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const items = props.items || [{ title: 'Accordion Item', content: 'Content goes here...' }];
    const borderColor = props.borderColor || '#e5e7eb';
    const borderRadius = props.borderRadius || '8px';
    const headerBgColor = props.headerBgColor || '#ffffff';
    const headerPadding = props.headerPadding || '16px';
    const titleColor = props.titleColor || '#1f2937';
    const titleFontSize = props.titleFontSize || '16px';
    const titleFontWeight = props.titleFontWeight || '600';
    const contentBgColor = props.contentBgColor || '#ffffff';
    const contentColor = props.contentColor || '#4b5563';
    const contentFontSize = props.contentFontSize || '14px';
    const contentPadding = props.contentPadding || '16px';
    const iconColor = props.iconColor || '#6b7280';

    // For email, we render accordion as expanded static content since email clients don't support interactive JS
    const accordionItems = items.map((item, index) => {
        const isLast = index === items.length - 1;
        return `
            <div style="border-bottom: ${isLast ? 'none' : `1px solid ${borderColor}`};">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: ${headerPadding}; background-color: ${headerBgColor};">
                    <span style="font-weight: ${titleFontWeight}; font-size: ${titleFontSize}; color: ${titleColor};">${item.title}</span>
                    <span style="color: ${iconColor}; font-size: 12px;">&#9660;</span>
                </div>
                <div style="padding: ${contentPadding}; background-color: ${contentBgColor}; color: ${contentColor}; font-size: ${contentFontSize}; line-height: 1.6;">
                    ${item.content}
                </div>
            </div>
        `;
    }).join('');

    return `
        <div style="border: 1px solid ${borderColor}; border-radius: ${borderRadius}; overflow: hidden;">
            ${accordionItems}
        </div>
    `;
};

export default {
    page,
    email,
};
