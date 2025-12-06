/**
 * Video Block - Property Editor
 *
 * Renders the property fields for the video block in the properties panel.
 */

const VideoBlockEditor = ({ props, onUpdate, onImageUpload }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleThumbnailUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        try {
            if (onImageUpload) {
                const result = await onImageUpload(file);
                handleChange('thumbnailUrl', result.url || result.path);
            }
        } catch (error) {
            console.error('Thumbnail upload failed:', error);
        }
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

    const helpTextStyle = {
        fontSize: '12px',
        color: '#6b7280',
        marginTop: '4px',
    };

    return (
        <div>
            {/* Video Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Video</div>

                <label style={labelStyle}>Video URL</label>
                <input
                    type="url"
                    value={props.videoUrl || ''}
                    onChange={(e) => handleChange('videoUrl', e.target.value)}
                    placeholder="YouTube, Vimeo, or direct video URL..."
                    className="form-control mb-2"
                />
                <div style={helpTextStyle}>
                    Supports YouTube, Vimeo, Dailymotion, Wistia, Loom, or direct video files (mp4, webm, etc.)
                </div>
            </div>

            {/* Thumbnail Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Thumbnail</div>

                {props.thumbnailUrl && (
                    <div style={{ marginBottom: '12px' }}>
                        <img
                            src={props.thumbnailUrl}
                            alt="Thumbnail Preview"
                            style={{
                                maxWidth: '100%',
                                maxHeight: '150px',
                                borderRadius: '4px',
                                border: '1px solid #e5e7eb',
                            }}
                        />
                    </div>
                )}

                <label style={labelStyle}>Upload Custom Thumbnail (Optional)</label>
                <input
                    type="file"
                    accept="image/*"
                    onChange={handleThumbnailUpload}
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Or Enter Thumbnail URL</label>
                <input
                    type="url"
                    value={props.thumbnailUrl || ''}
                    onChange={(e) => handleChange('thumbnailUrl', e.target.value)}
                    placeholder="https://..."
                    className="form-control mb-2"
                />
                <div style={helpTextStyle}>
                    Leave empty to auto-generate from video platform or video file
                </div>
            </div>

            {/* Alt Text Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Accessibility</div>

                <label style={labelStyle}>Alt Text</label>
                <input
                    type="text"
                    value={props.alt || ''}
                    onChange={(e) => handleChange('alt', e.target.value)}
                    placeholder="Describe the video..."
                    className="form-control"
                />
            </div>

            {/* Appearance Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Appearance</div>

                <label style={labelStyle}>Width</label>
                <select
                    value={props.width || '100%'}
                    onChange={(e) => handleChange('width', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="100%">Full Width (100%)</option>
                    <option value="75%">Three Quarters (75%)</option>
                    <option value="50%">Half (50%)</option>
                    <option value="25%">Quarter (25%)</option>
                </select>

                <label style={labelStyle}>Play Button Color</label>
                <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                    <input
                        type="color"
                        value={props.playButtonColor || '#FF0000'}
                        onChange={(e) => handleChange('playButtonColor', e.target.value)}
                        className="form-control"
                        style={{ width: '60px', height: '38px', padding: '2px' }}
                    />
                    <input
                        type="text"
                        value={props.playButtonColor || ''}
                        onChange={(e) => handleChange('playButtonColor', e.target.value)}
                        placeholder="#FF0000"
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>
                <div style={helpTextStyle}>
                    Leave empty to use platform default color
                </div>
            </div>

            {/* Alignment Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Alignment</div>

                <div style={{ display: 'flex', gap: '4px' }}>
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
            </div>
        </div>
    );
};

export default VideoBlockEditor;
