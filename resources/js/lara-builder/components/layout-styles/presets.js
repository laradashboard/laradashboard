/**
 * Shared presets for layout style controls
 */

// Preset options for spacing
export const SPACING_PRESETS = [
    { value: '', label: 'Auto' },
    { value: '0', label: '0' },
    { value: '4px', label: '4' },
    { value: '8px', label: '8' },
    { value: '12px', label: '12' },
    { value: '16px', label: '16' },
    { value: '20px', label: '20' },
    { value: '24px', label: '24' },
    { value: '32px', label: '32' },
    { value: '40px', label: '40' },
    { value: '48px', label: '48' },
    { value: '64px', label: '64' },
];

// Preset options for sizing
export const SIZE_PRESETS = [
    { value: '', label: 'Auto' },
    { value: '100%', label: '100%' },
    { value: '75%', label: '75%' },
    { value: '50%', label: '50%' },
    { value: '25%', label: '25%' },
    { value: '100px', label: '100px' },
    { value: '150px', label: '150px' },
    { value: '200px', label: '200px' },
    { value: '250px', label: '250px' },
    { value: '300px', label: '300px' },
    { value: '400px', label: '400px' },
    { value: '500px', label: '500px' },
];

// Background size presets
export const BACKGROUND_SIZE_PRESETS = [
    { value: 'cover', label: 'Cover' },
    { value: 'contain', label: 'Contain' },
    { value: 'auto', label: 'Auto' },
    { value: '100% 100%', label: '100%' },
];

// Background position presets
export const BACKGROUND_POSITION_PRESETS = [
    { value: 'center', label: 'Center' },
    { value: 'top', label: 'Top' },
    { value: 'bottom', label: 'Bottom' },
    { value: 'left', label: 'Left' },
    { value: 'right', label: 'Right' },
    { value: 'top left', label: 'Top Left' },
    { value: 'top right', label: 'Top Right' },
    { value: 'bottom left', label: 'Bottom Left' },
    { value: 'bottom right', label: 'Bottom Right' },
];

// Background repeat presets
export const BACKGROUND_REPEAT_PRESETS = [
    { value: 'no-repeat', label: 'No Repeat' },
    { value: 'repeat', label: 'Repeat' },
    { value: 'repeat-x', label: 'Repeat X' },
    { value: 'repeat-y', label: 'Repeat Y' },
];

// Typography presets
export const FONT_FAMILY_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: 'Arial, sans-serif', label: 'Arial' },
    { value: 'Helvetica, Arial, sans-serif', label: 'Helvetica' },
    { value: 'Georgia, serif', label: 'Georgia' },
    { value: "'Times New Roman', Times, serif", label: 'Times New Roman' },
    { value: 'Verdana, Geneva, sans-serif', label: 'Verdana' },
    { value: "'Trebuchet MS', sans-serif", label: 'Trebuchet MS' },
    { value: "'Courier New', Courier, monospace", label: 'Courier New' },
    { value: 'system-ui, -apple-system, sans-serif', label: 'System UI' },
];

export const FONT_SIZE_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: '10px', label: '10px' },
    { value: '12px', label: '12px' },
    { value: '14px', label: '14px' },
    { value: '16px', label: '16px' },
    { value: '18px', label: '18px' },
    { value: '20px', label: '20px' },
    { value: '24px', label: '24px' },
    { value: '28px', label: '28px' },
    { value: '32px', label: '32px' },
    { value: '36px', label: '36px' },
    { value: '48px', label: '48px' },
    { value: '64px', label: '64px' },
];

export const FONT_WEIGHT_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: '100', label: 'Thin (100)' },
    { value: '200', label: 'Extra Light (200)' },
    { value: '300', label: 'Light (300)' },
    { value: '400', label: 'Normal (400)' },
    { value: '500', label: 'Medium (500)' },
    { value: '600', label: 'Semi Bold (600)' },
    { value: '700', label: 'Bold (700)' },
    { value: '800', label: 'Extra Bold (800)' },
    { value: '900', label: 'Black (900)' },
];

export const FONT_STYLE_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: 'normal', label: 'Normal' },
    { value: 'italic', label: 'Italic' },
    { value: 'oblique', label: 'Oblique' },
];

export const LINE_HEIGHT_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: '1', label: '1' },
    { value: '1.2', label: '1.2' },
    { value: '1.4', label: '1.4' },
    { value: '1.5', label: '1.5' },
    { value: '1.6', label: '1.6' },
    { value: '1.8', label: '1.8' },
    { value: '2', label: '2' },
];

export const LETTER_SPACING_PRESETS = [
    { value: '', label: 'Inherit' },
    { value: '-1px', label: '-1px' },
    { value: '-0.5px', label: '-0.5px' },
    { value: '0', label: '0' },
    { value: '0.5px', label: '0.5px' },
    { value: '1px', label: '1px' },
    { value: '2px', label: '2px' },
    { value: '3px', label: '3px' },
];

export const TEXT_TRANSFORM_PRESETS = [
    { value: '', label: 'Inherit', icon: 'Aa' },
    { value: 'uppercase', label: 'Uppercase', icon: 'AA' },
    { value: 'lowercase', label: 'Lowercase', icon: 'aa' },
    { value: 'capitalize', label: 'Capitalize', icon: 'Aa' },
    { value: 'none', label: 'None', icon: 'x' },
];

export const TEXT_DECORATION_PRESETS = [
    { value: '', label: 'Inherit', icon: 'mdi:format-text' },
    { value: 'underline', label: 'Underline', icon: 'mdi:format-underline' },
    { value: 'overline', label: 'Overline', icon: 'mdi:format-overline' },
    { value: 'line-through', label: 'Strikethrough', icon: 'mdi:format-strikethrough' },
    { value: 'none', label: 'None', icon: 'mdi:close' },
];
