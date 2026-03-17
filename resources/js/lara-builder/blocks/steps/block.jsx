/**
 * Steps Block - Canvas Component
 *
 * Renders the steps/timeline block in the builder canvas.
 * Supports vertical and horizontal layouts with number circles and connectors.
 */

import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const StepsBlock = ({ props, isSelected, onUpdate }) => {
    const steps = props.steps || [
        { title: 'Step 1', description: 'Description', code: '', linkText: '', linkUrl: '' },
    ];
    const layout = props.layout || 'vertical';
    const showNumbers = props.showNumbers !== false;
    const showConnector = props.showConnector !== false;
    const numberColor = props.numberColor || '#ffffff';
    const numberBgColor = props.numberBgColor || '#3b82f6';
    const titleColor = props.titleColor || '#111827';
    const titleSize = props.titleSize || '20px';
    const descriptionColor = props.descriptionColor || '#6b7280';
    const descriptionSize = props.descriptionSize || '14px';
    const connectorColor = props.connectorColor || '#e5e7eb';
    const codeBackgroundColor = props.codeBackgroundColor || '#1f2937';
    const codeTextColor = props.codeTextColor || '#e5e7eb';

    const circleSize = 40;

    const defaultContainerStyle = {
        padding: '16px',
    };
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    if (layout === 'horizontal') {
        return (
            <div style={containerStyle}>
                <div style={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    gap: '0',
                }}>
                    {steps.map((step, index) => {
                        const isLast = index === steps.length - 1;
                        return (
                            <div key={index} style={{
                                flex: 1,
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                position: 'relative',
                            }}>
                                {/* Number circle with connector */}
                                <div style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    width: '100%',
                                    marginBottom: '16px',
                                }}>
                                    {/* Left connector */}
                                    {index > 0 && showConnector && (
                                        <div style={{
                                            flex: 1,
                                            height: '3px',
                                            backgroundColor: connectorColor,
                                        }} />
                                    )}
                                    {index === 0 && <div style={{ flex: 1 }} />}

                                    {/* Circle */}
                                    {showNumbers && (
                                        <div style={{
                                            width: `${circleSize}px`,
                                            height: `${circleSize}px`,
                                            borderRadius: '50%',
                                            backgroundColor: numberBgColor,
                                            color: numberColor,
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontSize: '16px',
                                            fontWeight: '700',
                                            flexShrink: 0,
                                        }}>
                                            {index + 1}
                                        </div>
                                    )}

                                    {/* Right connector */}
                                    {!isLast && showConnector && (
                                        <div style={{
                                            flex: 1,
                                            height: '3px',
                                            backgroundColor: connectorColor,
                                        }} />
                                    )}
                                    {isLast && <div style={{ flex: 1 }} />}
                                </div>

                                {/* Content */}
                                <div style={{ textAlign: 'center', padding: '0 8px' }}>
                                    <h4 style={{
                                        fontSize: titleSize,
                                        fontWeight: '600',
                                        color: titleColor,
                                        margin: '0 0 8px 0',
                                    }}>
                                        {step.title}
                                    </h4>
                                    <p style={{
                                        fontSize: descriptionSize,
                                        color: descriptionColor,
                                        margin: '0 0 8px 0',
                                        lineHeight: '1.5',
                                    }}>
                                        {step.description}
                                    </p>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {isSelected && (
                    <p style={{ marginTop: '8px', fontSize: '12px', color: '#6b7280', textAlign: 'center' }}>
                        Use the properties panel to edit steps content.
                    </p>
                )}
            </div>
        );
    }

    // Vertical layout (default)
    return (
        <div style={containerStyle}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0' }}>
                {steps.map((step, index) => {
                    const isLast = index === steps.length - 1;
                    return (
                        <div key={index} style={{
                            display: 'flex',
                            gap: '20px',
                            position: 'relative',
                            minHeight: isLast ? 'auto' : '100px',
                        }}>
                            {/* Number column */}
                            <div style={{
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                flexShrink: 0,
                                width: `${circleSize}px`,
                            }}>
                                {/* Circle */}
                                {showNumbers && (
                                    <div style={{
                                        width: `${circleSize}px`,
                                        height: `${circleSize}px`,
                                        borderRadius: '50%',
                                        backgroundColor: numberBgColor,
                                        color: numberColor,
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: '16px',
                                        fontWeight: '700',
                                        flexShrink: 0,
                                        zIndex: 1,
                                    }}>
                                        {index + 1}
                                    </div>
                                )}

                                {/* Connector line */}
                                {!isLast && showConnector && (
                                    <div style={{
                                        width: '3px',
                                        flex: 1,
                                        backgroundColor: connectorColor,
                                        marginTop: '4px',
                                        marginBottom: '4px',
                                    }} />
                                )}
                            </div>

                            {/* Content */}
                            <div style={{
                                flex: 1,
                                paddingBottom: isLast ? '0' : '32px',
                            }}>
                                <h4 style={{
                                    fontSize: titleSize,
                                    fontWeight: '600',
                                    color: titleColor,
                                    margin: '0 0 8px 0',
                                    lineHeight: `${circleSize}px`,
                                }}>
                                    {step.title}
                                </h4>

                                <p style={{
                                    fontSize: descriptionSize,
                                    color: descriptionColor,
                                    margin: '0 0 12px 0',
                                    lineHeight: '1.6',
                                }}>
                                    {step.description}
                                </p>

                                {/* Code block */}
                                {step.code && (
                                    <pre style={{
                                        backgroundColor: codeBackgroundColor,
                                        color: codeTextColor,
                                        padding: '12px 16px',
                                        borderRadius: '8px',
                                        fontSize: '13px',
                                        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace',
                                        margin: '0 0 12px 0',
                                        overflow: 'auto',
                                        whiteSpace: 'pre-wrap',
                                        wordBreak: 'break-all',
                                    }}>
                                        <code>{step.code}</code>
                                    </pre>
                                )}

                                {/* Link */}
                                {step.linkText && step.linkUrl && (
                                    <a
                                        href={step.linkUrl}
                                        onClick={(e) => e.preventDefault()}
                                        style={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: '4px',
                                            fontSize: '14px',
                                            fontWeight: '500',
                                            color: numberBgColor,
                                            textDecoration: 'none',
                                        }}
                                    >
                                        {step.linkText}
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                            <polyline points="12 5 19 12 12 19" />
                                        </svg>
                                    </a>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {isSelected && (
                <p style={{ marginTop: '12px', fontSize: '12px', color: '#6b7280', textAlign: 'center' }}>
                    Use the properties panel to edit steps content.
                </p>
            )}
        </div>
    );
};

export default StepsBlock;
