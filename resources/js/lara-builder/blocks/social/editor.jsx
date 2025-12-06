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

    const sectionStyle = {
        marginBottom: '16px',
    };

    const labelStyle = {
        display: 'block',
        fontSize: '13px',
        fontWeight: '500',
        color: '#374151',
        marginBottom: '6px',
    };

    const sectionTitleStyle = {
        fontSize: '12px',
        fontWeight: '600',
        color: '#6b7280',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        marginBottom: '12px',
        paddingBottom: '8px',
        borderBottom: '1px solid #e5e7eb',
    };

    return (
        <div>
            {/* Layout Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Layout</div>

                <label style={labelStyle}>Alignment</label>
                <div style={{ display: 'flex', gap: '4px', marginBottom: '12px' }}>
                    {['left', 'center', 'right'].map((align) => (
                        <button
                            key={align}
                            onClick={() => handleChange('align', align)}
                            className={`btn ${props.align === align ? 'btn-primary' : 'btn-default'}`}
                            style={{ flex: 1, padding: '8px' }}
                            title={`Align ${align}`}
                        >
                            <iconify-icon
                                icon={`mdi:format-align-${align}`}
                                width="18"
                                height="18"
                            />
                        </button>
                    ))}
                </div>

                <label style={labelStyle}>Icon Size</label>
                <select
                    value={props.iconSize || '32px'}
                    onChange={(e) => handleChange('iconSize', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="24px">Small (24px)</option>
                    <option value="32px">Medium (32px)</option>
                    <option value="40px">Large (40px)</option>
                    <option value="48px">X-Large (48px)</option>
                </select>

                <label style={labelStyle}>Spacing</label>
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

            {/* Social Links Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Social Links</div>

                {socialPlatforms.map((platform) => (
                    <div key={platform.key} style={{ marginBottom: '12px' }}>
                        <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: '6px' }}>
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
        </div>
    );
};

export default SocialBlockEditor;
