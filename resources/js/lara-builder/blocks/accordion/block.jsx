/**
 * Accordion Block - Canvas Component
 *
 * Renders the accordion block in the builder canvas.
 * Supports inline editing when selected.
 */

import { useState, useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const AccordionBlock = ({ props, isSelected, onUpdate }) => {
    const [expandedItems, setExpandedItems] = useState([0]); // First item expanded by default
    const [editingItem, setEditingItem] = useState(null); // { index, field: 'title' | 'content' }
    const editRef = useRef(null);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const items = props.items || [
        { title: 'Accordion Item 1', content: 'Content for the first accordion item.' },
    ];

    // Toggle accordion item
    const toggleItem = (index) => {
        if (props.independentToggle) {
            // Independent mode - toggle individual items
            setExpandedItems(prev =>
                prev.includes(index)
                    ? prev.filter(i => i !== index)
                    : [...prev, index]
            );
        } else {
            // Default mode - only one item open at a time
            setExpandedItems(prev =>
                prev.includes(index) ? [] : [index]
            );
        }
    };

    // Add new accordion item
    const addItem = () => {
        const newItems = [...items, { title: 'New Item', content: 'Content goes here...' }];
        onUpdateRef.current({ ...propsRef.current, items: newItems });
    };

    // Delete accordion item
    const deleteItem = (index, e) => {
        e.stopPropagation();
        if (items.length <= 1) return; // Keep at least one item
        const newItems = items.filter((_, i) => i !== index);
        onUpdateRef.current({ ...propsRef.current, items: newItems });
        setExpandedItems(prev => prev.filter(i => i !== index).map(i => i > index ? i - 1 : i));
    };

    // Start editing
    const startEditing = (index, field, e) => {
        e.stopPropagation();
        setEditingItem({ index, field });
    };

    // Handle content change
    const handleContentChange = useCallback(() => {
        if (editRef.current && editingItem) {
            const newValue = editRef.current.innerText;
            const newItems = [...propsRef.current.items];
            newItems[editingItem.index] = {
                ...newItems[editingItem.index],
                [editingItem.field]: newValue,
            };
            onUpdateRef.current({ ...propsRef.current, items: newItems });
        }
    }, [editingItem]);

    // Handle blur
    const handleBlur = () => {
        handleContentChange();
        setEditingItem(null);
    };

    // Handle key down
    const handleKeyDown = (e) => {
        if (e.key === 'Enter' && editingItem?.field === 'title') {
            e.preventDefault();
            handleBlur();
        } else if (e.key === 'Escape') {
            setEditingItem(null);
        }
    };

    // Focus edit field when editing starts
    useEffect(() => {
        if (editingItem && editRef.current) {
            editRef.current.focus();
            // Place cursor at end
            const range = document.createRange();
            range.selectNodeContents(editRef.current);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }, [editingItem]);

    // Styles
    const defaultContainerStyle = {
        padding: '8px',
        borderRadius: '8px',
    };
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const accordionStyle = {
        border: `1px solid ${props.borderColor || '#e5e7eb'}`,
        borderRadius: props.borderRadius || '8px',
        overflow: 'hidden',
    };

    const itemStyle = (isLast) => ({
        borderBottom: isLast ? 'none' : `1px solid ${props.borderColor || '#e5e7eb'}`,
    });

    const headerStyle = (isExpanded) => ({
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: props.headerPadding || '16px',
        backgroundColor: isExpanded ? (props.headerBgColorActive || props.headerBgColor || '#f9fafb') : (props.headerBgColor || '#ffffff'),
        cursor: 'pointer',
        transition: 'background-color 0.2s',
        userSelect: 'none',
    });

    const titleStyle = {
        fontWeight: props.titleFontWeight || '600',
        fontSize: props.titleFontSize || '16px',
        color: props.titleColor || '#1f2937',
        margin: 0,
        flex: 1,
    };

    const contentStyle = {
        padding: props.contentPadding || '16px',
        backgroundColor: props.contentBgColor || '#ffffff',
        color: props.contentColor || '#4b5563',
        fontSize: props.contentFontSize || '14px',
        lineHeight: '1.6',
    };

    const iconStyle = (isExpanded) => ({
        color: props.iconColor || '#6b7280',
        transition: `transform ${props.transitionDuration || 200}ms ease`,
        transform: isExpanded ? 'rotate(180deg)' : 'rotate(0deg)',
        flexShrink: 0,
        marginLeft: '12px',
    });

    return (
        <div style={containerStyle}>
            <div style={accordionStyle}>
                {items.map((item, index) => {
                    const isExpanded = expandedItems.includes(index);
                    const isLast = index === items.length - 1;
                    const isEditingTitle = editingItem?.index === index && editingItem?.field === 'title';
                    const isEditingContent = editingItem?.index === index && editingItem?.field === 'content';

                    return (
                        <div key={index} style={itemStyle(isLast)}>
                            {/* Header */}
                            <div
                                style={headerStyle(isExpanded)}
                                onClick={() => !editingItem && toggleItem(index)}
                            >
                                <div style={{ display: 'flex', alignItems: 'center', flex: 1, gap: '8px' }}>
                                    {/* Icon on left if configured */}
                                    {props.iconPosition === 'left' && (
                                        <span style={{ ...iconStyle(isExpanded), marginLeft: 0, marginRight: '12px' }}>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </span>
                                    )}

                                    {/* Title */}
                                    {isSelected && isEditingTitle ? (
                                        <div
                                            ref={editRef}
                                            contentEditable
                                            suppressContentEditableWarning
                                            onBlur={handleBlur}
                                            onKeyDown={handleKeyDown}
                                            onClick={(e) => e.stopPropagation()}
                                            style={{
                                                ...titleStyle,
                                                outline: 'none',
                                                border: '1px solid var(--color-primary, #635bff)',
                                                borderRadius: '4px',
                                                padding: '4px 8px',
                                                backgroundColor: 'white',
                                            }}
                                        >
                                            {item.title}
                                        </div>
                                    ) : (
                                        <h3
                                            style={titleStyle}
                                            onDoubleClick={(e) => isSelected && startEditing(index, 'title', e)}
                                        >
                                            {item.title}
                                        </h3>
                                    )}
                                </div>

                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                    {/* Delete button (only when selected and more than 1 item) */}
                                    {isSelected && items.length > 1 && (
                                        <button
                                            type="button"
                                            onClick={(e) => deleteItem(index, e)}
                                            style={{
                                                padding: '4px',
                                                border: 'none',
                                                background: 'none',
                                                cursor: 'pointer',
                                                color: '#ef4444',
                                                opacity: 0.7,
                                            }}
                                            title="Delete item"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                            </svg>
                                        </button>
                                    )}

                                    {/* Icon on right (default) */}
                                    {props.iconPosition !== 'left' && (
                                        <span style={iconStyle(isExpanded)}>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Content */}
                            <div
                                style={{
                                    maxHeight: isExpanded ? '1000px' : '0',
                                    overflow: 'hidden',
                                    transition: `max-height ${props.transitionDuration || 200}ms ease-in-out`,
                                }}
                            >
                                {isSelected && isEditingContent ? (
                                    <div
                                        ref={editRef}
                                        contentEditable
                                        suppressContentEditableWarning
                                        onBlur={handleBlur}
                                        onKeyDown={(e) => e.key === 'Escape' && handleBlur()}
                                        style={{
                                            ...contentStyle,
                                            outline: 'none',
                                            border: '2px solid var(--color-primary, #635bff)',
                                            minHeight: '80px',
                                        }}
                                    >
                                        {item.content}
                                    </div>
                                ) : (
                                    <div
                                        style={contentStyle}
                                        onDoubleClick={(e) => isSelected && startEditing(index, 'content', e)}
                                    >
                                        {item.content || 'Click to add content...'}
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Add Item Button (only when selected) */}
            {isSelected && (
                <button
                    type="button"
                    onClick={addItem}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '6px',
                        marginTop: '12px',
                        padding: '8px 16px',
                        backgroundColor: 'var(--color-primary, #635bff)',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        fontSize: '13px',
                        fontWeight: '500',
                        cursor: 'pointer',
                    }}
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Accordion Item
                </button>
            )}

            {/* Helper text when selected */}
            {isSelected && (
                <p style={{ marginTop: '8px', fontSize: '12px', color: '#6b7280' }}>
                    Double-click on title or content to edit
                </p>
            )}
        </div>
    );
};

export default AccordionBlock;
