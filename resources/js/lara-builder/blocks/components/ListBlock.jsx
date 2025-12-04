import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const ListBlock = ({ props, isSelected, onUpdate, onRegisterTextFormat }) => {
    const editorRef = useRef(null);
    const lastItemsHtml = useRef('');
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Convert items array to HTML for contentEditable
    const itemsToHtml = (items) => {
        if (!items || items.length === 0) {
            return '<li>List item</li>';
        }
        return items.map(item => `<li>${item || ''}</li>`).join('');
    };

    // Convert HTML back to items array
    const htmlToItems = (html) => {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        // Only get direct li children (not nested ones)
        const listItems = tempDiv.querySelectorAll(':scope > li');
        // If no direct li children found (might be wrapped in ul/ol), try that
        const items = listItems.length > 0
            ? Array.from(listItems).map(li => li.innerHTML.trim())
            : Array.from(tempDiv.querySelectorAll('li')).map(li => li.innerHTML.trim());
        // Filter out completely empty items but keep at least one
        const nonEmptyItems = items.filter(item => item !== '' && item !== '<br>');
        return nonEmptyItems.length > 0 ? nonEmptyItems : [''];
    };

    // Track internal updates to prevent effect from resetting DOM
    const isInternalUpdate = useRef(false);

    const handleInput = useCallback(() => {
        if (editorRef.current) {
            const html = editorRef.current.innerHTML;
            // Avoid saving if nothing changed
            if (html === lastItemsHtml.current) {
                return;
            }
            lastItemsHtml.current = html;
            const newItems = htmlToItems(html);
            isInternalUpdate.current = true; // Mark as internal update
            onUpdateRef.current({ ...propsRef.current, items: newItems });
        }
    }, []);

    const handleKeyDown = useCallback((e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();

            const selection = window.getSelection();
            if (!selection.rangeCount) return;

            const range = selection.getRangeAt(0);

            // Find the current li element
            let currentLi = range.startContainer;
            while (currentLi && currentLi.nodeName !== 'LI') {
                currentLi = currentLi.parentElement;
            }

            if (currentLi && editorRef.current) {
                // Create new li element
                const newLi = document.createElement('li');
                newLi.innerHTML = '<br>';

                // Insert after current li
                if (currentLi.nextSibling) {
                    currentLi.parentNode.insertBefore(newLi, currentLi.nextSibling);
                } else {
                    currentLi.parentNode.appendChild(newLi);
                }

                // Move cursor to new li
                const newRange = document.createRange();
                newRange.setStart(newLi, 0);
                newRange.collapse(true);
                selection.removeAllRanges();
                selection.addRange(newRange);

                // Trigger input to save changes
                handleInput();
            }
        } else if (e.key === 'Backspace') {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                let li = range.startContainer;
                while (li && li.nodeName !== 'LI') {
                    li = li.parentElement;
                }

                // If at the start of a list item and it's not the first one, merge with previous
                if (li && range.startOffset === 0 && li.previousElementSibling) {
                    const liContent = li.textContent || '';
                    // Only merge if cursor is at the very start
                    if (range.startContainer === li || (range.startContainer.nodeType === 3 && range.startOffset === 0)) {
                        e.preventDefault();
                        const prevLi = li.previousElementSibling;
                        const currentContent = li.innerHTML;

                        // Move cursor to end of previous item
                        const newRange = document.createRange();
                        newRange.selectNodeContents(prevLi);
                        newRange.collapse(false);

                        // Append current content to previous
                        if (currentContent && currentContent !== '<br>') {
                            prevLi.innerHTML += currentContent;
                        }
                        li.remove();

                        // Restore cursor
                        selection.removeAllRanges();
                        selection.addRange(newRange);

                        handleInput();
                    }
                }
            }
        }
    }, [handleInput]);

    // Stable align change handler - uses refs for latest values
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Track the current list type to detect changes
    const lastListType = useRef(props.listType);
    const wasSelected = useRef(false);

    // Single unified effect to handle content synchronization
    useEffect(() => {
        if (isSelected && editorRef.current) {
            const currentHtml = editorRef.current.innerHTML;
            const listTypeChanged = lastListType.current !== props.listType;
            const justBecameSelected = !wasSelected.current;

            lastListType.current = props.listType;
            wasSelected.current = true;

            // Skip if this update was triggered by our own input
            if (isInternalUpdate.current) {
                isInternalUpdate.current = false;
                return;
            }

            // Set content if:
            // 1. Just became selected (empty content)
            // 2. List type changed (element was recreated)
            // 3. Content is empty
            const needsInit = justBecameSelected || listTypeChanged ||
                              currentHtml === '' || currentHtml === '<li><br></li>';

            if (needsInit) {
                const html = itemsToHtml(props.items);
                editorRef.current.innerHTML = html;
                lastItemsHtml.current = html;
            }
        } else {
            wasSelected.current = false;
        }
    }, [isSelected, props.listType, props.items]);

    // Register text format props with parent when selected
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef,
                isContentEditable: true,
                align: propsRef.current.align || 'left',
                onAlignChange: handleAlignChange,
            });
        } else if (!isSelected && onRegisterTextFormat) {
            onRegisterTextFormat(null);
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange]);

    // Focus the editor when selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            editorRef.current.focus();
            // Place cursor at the end
            const range = document.createRange();
            range.selectNodeContents(editorRef.current);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }, [isSelected]);

    // Base container styles
    const defaultContainerStyle = {
        padding: '8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base list styles
    const defaultListStyle = {
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        lineHeight: '1.8',
        margin: 0,
        paddingLeft: props.listType === 'none' ? '0' : '24px',
        listStyleType: props.listType === 'bullet' ? 'disc' : props.listType === 'number' ? 'decimal' : 'none',
        textAlign: props.align || 'left',
    };

    // Apply typography from layout styles to list
    const listStyle = applyLayoutStyles(defaultListStyle, props.layoutStyles);

    const itemStyle = {
        marginBottom: '8px',
    };

    const checkIconStyle = {
        color: props.iconColor || '#3b82f6',
        marginRight: '8px',
        flexShrink: 0,
    };

    const items = props.items || ['List item'];

    // Editable mode when selected
    if (isSelected) {
        const ListTag = props.listType === 'number' ? 'ol' : 'ul';

        // For check list type, we use a custom style
        const editListStyle = {
            ...listStyle,
            border: '2px solid #635bff',
            outline: 'none',
            background: 'white',
            minHeight: '40px',
            padding: props.listType === 'check' ? '8px' : '8px 8px 8px 32px',
            listStylePosition: 'inside',
        };

        if (props.listType === 'check') {
            // For checklist, we need special handling
            editListStyle.listStyleType = 'none';
            editListStyle.paddingLeft = '8px';
        }

        return (
            <div style={containerStyle} data-text-editing="true">
                <ListTag
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onKeyDown={handleKeyDown}
                    onBlur={handleInput}
                    style={editListStyle}
                    className={props.listType === 'check' ? 'checklist-edit' : ''}
                />
                {props.listType === 'check' && (
                    <style>{`
                        .checklist-edit li {
                            display: flex;
                            align-items: flex-start;
                            margin-bottom: 8px;
                        }
                        .checklist-edit li::before {
                            content: '✓';
                            color: ${props.iconColor || '#3b82f6'};
                            margin-right: 8px;
                            font-weight: bold;
                        }
                    `}</style>
                )}
            </div>
        );
    }

    // Display mode - render with check icons for check type
    if (props.listType === 'check') {
        return (
            <div style={containerStyle}>
                <div style={listStyle}>
                    {items.map((item, index) => (
                        <div key={index} style={{ ...itemStyle, display: 'flex', alignItems: 'flex-start' }}>
                            <span style={checkIconStyle}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span dangerouslySetInnerHTML={{ __html: item || 'List item' }} />
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    const ListTag = props.listType === 'number' ? 'ol' : 'ul';

    return (
        <div style={containerStyle}>
            <ListTag style={listStyle}>
                {items.map((item, index) => (
                    <li key={index} style={itemStyle} dangerouslySetInnerHTML={{ __html: item || 'List item' }} />
                ))}
            </ListTag>
        </div>
    );
};

export default ListBlock;
