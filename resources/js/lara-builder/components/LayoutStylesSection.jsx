/**
 * LayoutStylesSection - Reusable Layout styles panel for all blocks
 *
 * Provides Background, Typography, Spacing (margin, padding), and Sizing controls
 * that can be applied to any block type.
 */

import { useState } from 'react';
import BackgroundControls from './layout-styles/BackgroundControls';
import TypographyControls from './layout-styles/TypographyControls';
import SpacingControls from './layout-styles/SpacingControls';
import SizingControls from './layout-styles/SizingControls';

// Re-export helpers for backward compatibility
export { layoutStylesToCSS, layoutStylesToInlineCSS } from './layout-styles/styleHelpers';

// Collapsible section header component
const SectionHeader = ({ icon, iconColor = 'text-primary/100', title, isExpanded, onToggle }) => (
    <button
        type="button"
        onClick={onToggle}
        className="flex items-center justify-between w-full text-left mb-3 group"
    >
        <div className="flex items-center gap-2">
            <iconify-icon
                icon={icon}
                width="16"
                height="16"
                class={iconColor}
            ></iconify-icon>
            <span className="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                {title}
            </span>
        </div>
        <iconify-icon
            icon={isExpanded ? 'mdi:chevron-up' : 'mdi:chevron-down'}
            width="18"
            height="18"
            class="text-gray-400 group-hover:text-gray-600 transition-colors"
        ></iconify-icon>
    </button>
);

const LayoutStylesSection = ({ layoutStyles = {}, onUpdate, onImageUpload, defaultCollapsed = true }) => {
    const [isBgExpanded, setIsBgExpanded] = useState(false);
    const [isTypoExpanded, setIsTypoExpanded] = useState(false);
    const [isLayoutExpanded, setIsLayoutExpanded] = useState(!defaultCollapsed);

    const handleLayoutChange = (field, value) => {
        onUpdate({ ...layoutStyles, [field]: value });
    };

    const handleMarginChange = (margins) => {
        onUpdate({ ...layoutStyles, margin: margins });
    };

    const handlePaddingChange = (paddings) => {
        onUpdate({ ...layoutStyles, padding: paddings });
    };

    const handleBackgroundChange = (background) => {
        onUpdate({ ...layoutStyles, background });
    };

    const handleTypographyChange = (typography) => {
        onUpdate({ ...layoutStyles, typography });
    };

    return (
        <div className="border-t border-gray-200 mt-4 pt-4 space-y-4">
            {/* BACKGROUND Section */}
            <div>
                <SectionHeader
                    icon="mdi:palette-outline"
                    title="Background"
                    isExpanded={isBgExpanded}
                    onToggle={() => setIsBgExpanded(!isBgExpanded)}
                />
                {isBgExpanded && (
                    <BackgroundControls
                        background={layoutStyles.background || {}}
                        onChange={handleBackgroundChange}
                        onImageUpload={onImageUpload}
                    />
                )}
            </div>

            {/* TYPOGRAPHY Section */}
            <div className="border-t border-gray-200 pt-4">
                <SectionHeader
                    icon="mdi:format-font"
                    title="Typography"
                    isExpanded={isTypoExpanded}
                    onToggle={() => setIsTypoExpanded(!isTypoExpanded)}
                />
                {isTypoExpanded && (
                    <TypographyControls
                        typography={layoutStyles.typography || {}}
                        onChange={handleTypographyChange}
                    />
                )}
            </div>

            {/* LAYOUT Section */}
            <div className="border-t border-gray-200 pt-4">
                <SectionHeader
                    icon="mdi:view-dashboard-outline"
                    title="Layout"
                    isExpanded={isLayoutExpanded}
                    onToggle={() => setIsLayoutExpanded(!isLayoutExpanded)}
                />
                {isLayoutExpanded && (
                    <div className="space-y-4">
                        {/* Spacing */}
                        <SpacingControls
                            margin={layoutStyles.margin || {}}
                            padding={layoutStyles.padding || {}}
                            onMarginChange={handleMarginChange}
                            onPaddingChange={handlePaddingChange}
                        />

                        {/* Sizing */}
                        <SizingControls
                            layoutStyles={layoutStyles}
                            onChange={handleLayoutChange}
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

export default LayoutStylesSection;
