import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const FooterBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
    const companyNameRef = useRef(null);
    const addressRef = useRef(null);
    const phoneRef = useRef(null);
    const emailRef = useRef(null);
    const unsubscribeTextRef = useRef(null);
    const copyrightRef = useRef(null);
    
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const handleInput = useCallback((field, ref) => {
        if (ref.current) {
            const newValue = ref.current.innerHTML;
            onUpdateRef.current({ ...propsRef.current, [field]: newValue });
        }
    }, []);

    // Stable align change handler that uses refs
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Set initial content when becoming selected
    useEffect(() => {
        if (isSelected) {
            if (companyNameRef.current && (companyNameRef.current.innerHTML === '' || companyNameRef.current.innerHTML === '<br>')) {
                companyNameRef.current.innerHTML = props.companyName || '';
            }
            if (addressRef.current && (addressRef.current.innerHTML === '' || addressRef.current.innerHTML === '<br>')) {
                addressRef.current.innerHTML = props.address || '';
            }
            if (phoneRef.current && (phoneRef.current.innerHTML === '' || phoneRef.current.innerHTML === '<br>')) {
                phoneRef.current.innerHTML = props.phone || '';
            }
            if (emailRef.current && (emailRef.current.innerHTML === '' || emailRef.current.innerHTML === '<br>')) {
                emailRef.current.innerHTML = props.email || '';
            }
            if (unsubscribeTextRef.current && (unsubscribeTextRef.current.innerHTML === '' || unsubscribeTextRef.current.innerHTML === '<br>')) {
                unsubscribeTextRef.current.innerHTML = props.unsubscribeText || '';
            }
            if (copyrightRef.current && (copyrightRef.current.innerHTML === '' || copyrightRef.current.innerHTML === '<br>')) {
                copyrightRef.current.innerHTML = props.copyright || '';
            }
        }
    }, [isSelected, props.companyName, props.address, props.phone, props.email, props.unsubscribeText, props.copyright]);

    // Register text format props with parent when selected
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef: null, // Footer has multiple editable fields
                isContentEditable: false,
                align: propsRef.current.align || 'center',
                onAlignChange: handleAlignChange,
            });
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange]);

    // Base container styles
    const defaultContainerStyle = {
        padding: '24px 16px',
        textAlign: props.align || 'center',
        borderTop: '1px solid #e5e7eb',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base text styles
    const defaultTextStyle = {
        color: props.textColor || '#6b7280',
        fontSize: props.fontSize || '12px',
        lineHeight: '1.6',
        margin: '0 0 8px 0',
    };

    // Apply typography from layout styles
    const textStyle = applyLayoutStyles(defaultTextStyle, props.layoutStyles);

    const linkStyle = {
        color: props.linkColor || '#635bff',
        textDecoration: 'underline',
    };

    const companyStyle = {
        ...textStyle,
        fontWeight: '600',
        fontSize: '14px',
        marginBottom: '12px',
    };

    const editableStyle = {
        outline: 'none',
        minHeight: '1em',
        cursor: isSelected ? 'text' : 'default',
    };

    return (
        <div style={containerStyle}>
            {(props.companyName !== undefined || isSelected) && (
                <p
                    ref={companyNameRef}
                    contentEditable={isSelected}
                    suppressContentEditableWarning={true}
                    onInput={() => handleInput('companyName', companyNameRef)}
                    style={{ ...companyStyle, ...editableStyle }}
                    data-placeholder={isSelected && !props.companyName ? 'Company Name' : ''}
                >
                    {!isSelected && props.companyName}
                </p>
            )}
            {(props.address !== undefined || isSelected) && (
                <p
                    ref={addressRef}
                    contentEditable={isSelected}
                    suppressContentEditableWarning={true}
                    onInput={() => handleInput('address', addressRef)}
                    style={{ ...textStyle, ...editableStyle }}
                    data-placeholder={isSelected && !props.address ? 'Address' : ''}
                >
                    {!isSelected && props.address}
                </p>
            )}
            {((props.phone !== undefined || props.email !== undefined) || isSelected) && (
                <p style={textStyle}>
                    {(props.phone !== undefined || isSelected) && (
                        <span
                            ref={phoneRef}
                            contentEditable={isSelected}
                            suppressContentEditableWarning={true}
                            onInput={() => handleInput('phone', phoneRef)}
                            style={editableStyle}
                            data-placeholder={isSelected && !props.phone ? 'Phone' : ''}
                        >
                            {!isSelected && props.phone}
                        </span>
                    )}
                    {props.phone && props.email && <span> | </span>}
                    {(props.email !== undefined || isSelected) && (
                        <span
                            ref={emailRef}
                            contentEditable={isSelected}
                            suppressContentEditableWarning={true}
                            onInput={() => handleInput('email', emailRef)}
                            style={{ ...editableStyle, ...linkStyle }}
                            data-placeholder={isSelected && !props.email ? 'Email' : ''}
                        >
                            {!isSelected && props.email ? (
                                <a href={`mailto:${props.email}`} style={linkStyle}>{props.email}</a>
                            ) : !isSelected && ''}
                        </span>
                    )}
                </p>
            )}
            {(props.unsubscribeText !== undefined || isSelected) && (
                <p style={{ ...textStyle, marginTop: '16px' }}>
                    <span
                        ref={unsubscribeTextRef}
                        contentEditable={isSelected}
                        suppressContentEditableWarning={true}
                        onInput={() => handleInput('unsubscribeText', unsubscribeTextRef)}
                        style={{ ...editableStyle, ...linkStyle }}
                        data-placeholder={isSelected && !props.unsubscribeText ? 'Unsubscribe Text' : ''}
                    >
                        {!isSelected && props.unsubscribeText ? (
                            <a href={props.unsubscribeUrl || '#'} style={linkStyle}>
                                {props.unsubscribeText}
                            </a>
                        ) : !isSelected && ''}
                    </span>
                </p>
            )}
            {(props.copyright !== undefined || isSelected) && (
                <p
                    ref={copyrightRef}
                    contentEditable={isSelected}
                    suppressContentEditableWarning={true}
                    onInput={() => handleInput('copyright', copyrightRef)}
                    style={{ ...textStyle, ...editableStyle, marginTop: '12px', fontSize: '11px' }}
                    data-placeholder={isSelected && !props.copyright ? 'Copyright Text' : ''}
                >
                    {!isSelected && props.copyright}
                </p>
            )}
        </div>
    );
};

export default FooterBlock;
