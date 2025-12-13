/**
 * Quote Block - Canvas Component
 *
 * Renders the quote block in the builder canvas.
 * Supports inline editing for quote text, author name, and author title.
 */

import { useRef, useEffect, useCallback, useState } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const QuoteBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
    const quoteRef = useRef(null);
    const authorRef = useRef(null);
    const titleRef = useRef(null);

    // Track which field is currently focused for toolbar
    const [activeField, setActiveField] = useState('quote');

    const lastQuoteText = useRef(props.text);
    const lastAuthor = useRef(props.author);
    const lastAuthorTitle = useRef(props.authorTitle);

    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Get the active editor ref based on focused field
    const getActiveRef = useCallback(() => {
        switch (activeField) {
            case 'author': return authorRef;
            case 'title': return titleRef;
            default: return quoteRef;
        }
    }, [activeField]);

    const handleQuoteInput = useCallback(() => {
        if (quoteRef.current) {
            const newText = quoteRef.current.innerHTML;
            lastQuoteText.current = newText;
            onUpdateRef.current({ ...propsRef.current, text: newText });
        }
    }, []);

    const handleAuthorInput = useCallback(() => {
        if (authorRef.current) {
            const newAuthor = authorRef.current.innerHTML;
            lastAuthor.current = newAuthor;
            onUpdateRef.current({ ...propsRef.current, author: newAuthor });
        }
    }, []);

    const handleTitleInput = useCallback(() => {
        if (titleRef.current) {
            const newTitle = titleRef.current.innerHTML;
            lastAuthorTitle.current = newTitle;
            onUpdateRef.current({ ...propsRef.current, authorTitle: newTitle });
        }
    }, []);

    // Stable align change handler
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Set initial content when becoming selected
    useEffect(() => {
        if (isSelected) {
            if (quoteRef.current && (quoteRef.current.innerHTML === '' || quoteRef.current.innerHTML === '<br>')) {
                quoteRef.current.innerHTML = props.text || '';
                lastQuoteText.current = props.text;
            }
            if (authorRef.current && (authorRef.current.innerHTML === '' || authorRef.current.innerHTML === '<br>')) {
                authorRef.current.innerHTML = props.author || '';
                lastAuthor.current = props.author;
            }
            if (titleRef.current && (titleRef.current.innerHTML === '' || titleRef.current.innerHTML === '<br>')) {
                titleRef.current.innerHTML = props.authorTitle || '';
                lastAuthorTitle.current = props.authorTitle;
            }
        }
    }, [isSelected]);

    // Handle external prop changes (from formatting toolbar)
    useEffect(() => {
        if (isSelected && quoteRef.current && props.text !== lastQuoteText.current) {
            quoteRef.current.innerHTML = props.text || '';
            lastQuoteText.current = props.text;
        }
    }, [props.text, isSelected]);

    useEffect(() => {
        if (isSelected && authorRef.current && props.author !== lastAuthor.current) {
            authorRef.current.innerHTML = props.author || '';
            lastAuthor.current = props.author;
        }
    }, [props.author, isSelected]);

    useEffect(() => {
        if (isSelected && titleRef.current && props.authorTitle !== lastAuthorTitle.current) {
            titleRef.current.innerHTML = props.authorTitle || '';
            lastAuthorTitle.current = props.authorTitle;
        }
    }, [props.authorTitle, isSelected]);

    // Register text format props with parent when selected
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef: getActiveRef(),
                isContentEditable: true,
                align: propsRef.current.align || 'left',
                onAlignChange: handleAlignChange,
            });
        } else if (!isSelected && onRegisterTextFormat) {
            onRegisterTextFormat(null);
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange, activeField, getActiveRef]);

    // Focus the quote field when selected
    useEffect(() => {
        if (isSelected && quoteRef.current) {
            // Use requestAnimationFrame to ensure focus happens after click event completes
            // This is necessary when inserting blocks via click from the BlockPanel
            requestAnimationFrame(() => {
                if (quoteRef.current) {
                    quoteRef.current.focus();
                    const range = document.createRange();
                    range.selectNodeContents(quoteRef.current);
                    range.collapse(false);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            });
        }
    }, [isSelected]);

    // Styles
    const defaultContainerStyle = {
        padding: '20px',
        paddingLeft: '24px',
        backgroundColor: props.backgroundColor || '#f8fafc',
        borderLeft: `4px solid ${props.borderColor || '#635bff'}`,
        textAlign: props.align || 'left',
        borderRadius: '4px',
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const quoteStyle = {
        color: props.textColor || '#475569',
        fontSize: '16px',
        fontStyle: 'italic',
        lineHeight: '1.6',
        margin: '0 0 12px 0',
        outline: 'none',
        minHeight: '1.5em',
    };

    const authorStyle = {
        color: props.authorColor || '#1e293b',
        fontSize: '14px',
        fontWeight: '600',
        margin: '0 0 4px 0',
        outline: 'none',
        minHeight: '1.2em',
    };

    const titleStyle = {
        color: props.textColor || '#475569',
        fontSize: '12px',
        margin: 0,
        outline: 'none',
        minHeight: '1em',
    };

    const handleFieldFocus = (field) => {
        setActiveField(field);
    };

    // Render content safely
    const renderQuoteContent = () => {
        const text = props.text || 'Click to add quote...';
        return <span dangerouslySetInnerHTML={{ __html: `"${text}"` }} />;
    };

    const renderAuthorContent = () => {
        const author = props.author || '';
        return author ? <span dangerouslySetInnerHTML={{ __html: author }} /> : <span style={{ opacity: 0.5 }}>Author name</span>;
    };

    const renderTitleContent = () => {
        const title = props.authorTitle || '';
        return title ? <span dangerouslySetInnerHTML={{ __html: title }} /> : <span style={{ opacity: 0.5 }}>Author title</span>;
    };

    if (isSelected) {
        return (
            <div
                data-text-editing="true"
                style={containerStyle}
            >
                {/* Quote Text - Editable */}
                <div style={{ position: 'relative', marginBottom: '12px' }}>
                    <span style={{ ...quoteStyle, position: 'absolute', left: 0, pointerEvents: 'none' }}>"</span>
                    <div
                        ref={quoteRef}
                        contentEditable
                        suppressContentEditableWarning
                        onInput={handleQuoteInput}
                        onBlur={handleQuoteInput}
                        onFocus={() => handleFieldFocus('quote')}
                        data-placeholder="Enter quote..."
                        style={{
                            ...quoteStyle,
                            paddingLeft: '12px',
                            background: activeField === 'quote' ? 'rgba(99, 91, 255, 0.05)' : 'transparent',
                            borderRadius: '4px',
                        }}
                    />
                    <span style={{ ...quoteStyle, position: 'absolute', right: 0, bottom: 0, pointerEvents: 'none' }}>"</span>
                </div>

                {/* Author Section */}
                <div>
                    {/* Author Name - Editable */}
                    <div
                        ref={authorRef}
                        contentEditable
                        suppressContentEditableWarning
                        onInput={handleAuthorInput}
                        onBlur={handleAuthorInput}
                        onFocus={() => handleFieldFocus('author')}
                        data-placeholder="Author name"
                        style={{
                            ...authorStyle,
                            background: activeField === 'author' ? 'rgba(99, 91, 255, 0.05)' : 'transparent',
                            borderRadius: '4px',
                            padding: '2px 4px',
                        }}
                    />

                    {/* Author Title - Editable */}
                    <div
                        ref={titleRef}
                        contentEditable
                        suppressContentEditableWarning
                        onInput={handleTitleInput}
                        onBlur={handleTitleInput}
                        onFocus={() => handleFieldFocus('title')}
                        data-placeholder="Author title"
                        style={{
                            ...titleStyle,
                            background: activeField === 'title' ? 'rgba(99, 91, 255, 0.05)' : 'transparent',
                            borderRadius: '4px',
                            padding: '2px 4px',
                        }}
                    />
                </div>
            </div>
        );
    }

    // Display mode
    return (
        <div style={containerStyle}>
            <p style={quoteStyle}>{renderQuoteContent()}</p>
            <div>
                <p style={authorStyle}>{renderAuthorContent()}</p>
                <p style={titleStyle}>{renderTitleContent()}</p>
            </div>
        </div>
    );
};

export default QuoteBlock;
