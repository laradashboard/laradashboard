/**
 * Social Block - Property Editor
 *
 * Renders the property fields for the social block in the properties panel.
 */

const socialPlatforms = [
    { key: 'facebook', label: 'Facebook', icon: 'mdi:facebook', color: '#1877f2' },
    { key: 'twitter', label: 'Twitter/X', icon: 'mdi:twitter', color: '#1da1f2' },
    { key: 'instagram', label: 'Instagram', icon: 'mdi:instagram', color: '#e4405f' },
    { key: 'linkedin', label: 'LinkedIn', icon: 'mdi:linkedin', color: '#0a66c2' },
    { key: 'youtube', label: 'YouTube', icon: 'mdi:youtube', color: '#ff0000' },
];

const SocialBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleLinkChange = (platform, value) => {
        onUpdate({
            ...props,
            links: {
                ...props.links,
                [platform]: value,
            },
        });
    };

    return (
        <div className="space-y-4">
            {/* Layout Section */}
            <Section title="Layout">
                <Label>Icon Size</Label>
                <select
                    value={props.iconSize || '32px'}
                    onChange={(e) => handleChange('iconSize', e.target.value)}
                    className="form-control"
                >
                    <option value="24px">Small (24px)</option>
                    <option value="32px">Medium (32px)</option>
                    <option value="40px">Large (40px)</option>
                    <option value="48px">X-Large (48px)</option>
                </select>

                <div className="mt-3">
                    <Label>Spacing</Label>
                    <select
                        value={props.gap || '12px'}
                        onChange={(e) => handleChange('gap', e.target.value)}
                        className="form-control"
                    >
                        <option value="8px">Small</option>
                        <option value="12px">Medium</option>
                        <option value="16px">Large</option>
                        <option value="24px">X-Large</option>
                    </select>
                </div>
            </Section>

            {/* Social Links Section */}
            <Section title="Social Links">
                <div className="space-y-3">
                    {socialPlatforms.map((platform) => (
                        <div key={platform.key}>
                            <label className="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <iconify-icon
                                    icon={platform.icon}
                                    width="16"
                                    height="16"
                                    style={{ color: platform.color }}
                                />
                                {platform.label}
                            </label>
                            <input
                                type="url"
                                value={props.links?.[platform.key] || ''}
                                onChange={(e) => handleLinkChange(platform.key, e.target.value)}
                                placeholder={`https://${platform.key}.com/...`}
                                className="form-control"
                            />
                        </div>
                    ))}
                </div>
            </Section>
        </div>
    );
};

// Reusable Section Component
const Section = ({ title, children }) => (
    <div className="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0">
        <h4 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
            {title}
        </h4>
        {children}
    </div>
);

// Reusable Label Component
const Label = ({ children }) => (
    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {children}
    </label>
);

export default SocialBlockEditor;
