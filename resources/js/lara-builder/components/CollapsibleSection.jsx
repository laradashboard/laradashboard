/**
 * CollapsibleSection - Reusable collapsible section component for Properties Panel
 *
 * Provides a consistent collapsible UI pattern for organizing block settings.
 */

import { useState } from 'react';

const CollapsibleSection = ({
    title,
    icon = 'mdi:cog',
    iconColor = 'text-primary',
    defaultExpanded = true,
    children,
    className = '',
}) => {
    const [isExpanded, setIsExpanded] = useState(defaultExpanded);

    return (
        <div className={`border-t border-gray-200 mt-4 pt-4 ${className}`}>
            {/* Collapsible Header */}
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
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

            {isExpanded && (
                <div className="space-y-1">
                    {children}
                </div>
            )}
        </div>
    );
};

export default CollapsibleSection;
