import { useState } from 'react';
import { useDroppable } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { getBlockComponent } from '../blocks';
import { getBlockSupports } from '../blocks/blockLoader';
import BlockToolbar from './BlockToolbar';
import { layoutStylesToCSS } from './LayoutStylesSection';
import { buildBlockClasses } from './BlockWrapper';
import { __ } from '@lara-builder/i18n';

// Drop zone indicator between blocks
const DropZone = ({ id, isFirst = false }) => {
    const { setNodeRef, isOver } = useDroppable({
        id: id,
        data: {
            type: 'dropzone',
            position: id,
        },
    });

    return (
        <div
            ref={setNodeRef}
            className={`transition-all duration-200 ${
                isOver
                    ? 'h-16 bg-primary/10 border-2 border-dashed border-primary rounded-lg my-2 flex items-center justify-center'
                    : isFirst ? 'h-0' : 'h-1'
            }`}
        >
            {isOver && (
                <span className="text-primary text-sm font-medium">{__('Drop here')}</span>
            )}
        </div>
    );
};

// Note: Block features are now read from block.json supports configuration
// via getBlockSupports() instead of hardcoded arrays

const SortableBlock = ({ block, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, onInsertBlockAfter, onReplaceBlock, totalBlocks, blockIndex, context }) => {
    const [textFormatProps, setTextFormatProps] = useState(null);

    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: block.id });

    // Note: Layout styles and custom CSS are now applied directly to each block's
    // main element (inside the block component), not to this wrapper.
    // This ensures consistent output between canvas preview and HTML export.

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    // Build block classes (lb-block lb-{type} + custom class)
    const blockClasses = buildBlockClasses(block.type, block.props);

    const BlockComponent = getBlockComponent(block.type);
    const isSelected = selectedBlockId === block.id;

    // Get supports configuration from block.json
    const supports = getBlockSupports(block.type);

    // Determine block capabilities from supports
    const hasTextFormatting = supports.bold || supports.italic || supports.underline;
    const hasAlignOnly = supports.align && !hasTextFormatting;
    const hasColumnCount = supports.columnCount === true;

    // Blocks with their own toolbar (like text-editor) - always show toolbar at bottom
    const SELF_EDITING_BLOCKS = ['text-editor'];
    const hasSelfEditor = SELF_EDITING_BLOCKS.includes(block.type);
    const toolbarAtBottom = hasSelfEditor;

    // Alignment props for align-only blocks (align support but no text formatting)
    const alignProps = hasAlignOnly ? {
        align: block.props?.align || 'center',
        onAlignChange: (newAlign) => onUpdate(block.id, { ...block.props, align: newAlign }),
    } : null;

    // Column props for blocks with columnCount support
    const columnsProps = hasColumnCount ? {
        columns: block.props?.columns || 1,
        onColumnsChange: (newColumns) => {
            const currentColumns = parseInt(block.props?.columns) || 1;
            const currentChildren = block.props?.children || [[]];

            // Adjust children array based on column count change
            let newChildren = [...currentChildren];
            if (newColumns > currentColumns) {
                // Add empty arrays for new columns
                for (let i = currentColumns; i < newColumns; i++) {
                    newChildren.push([]);
                }
            } else if (newColumns < currentColumns) {
                // Keep only the needed columns (content in removed columns is lost)
                newChildren = newChildren.slice(0, newColumns);
            }

            onUpdate(block.id, {
                ...block.props,
                columns: newColumns,
                children: newChildren,
            });
        },
    } : null;

    // Heading level props for blocks with headingLevel support
    const headingLevelProps = supports.headingLevel ? {
        level: block.props?.level || 'h1',
        onLevelChange: (newLevel) => {
            // Get default font size for the new level
            const fontSizeMap = {
                h1: '32px',
                h2: '28px',
                h3: '24px',
                h4: '20px',
                h5: '18px',
                h6: '16px',
            };
            onUpdate(block.id, {
                ...block.props,
                level: newLevel,
                fontSize: fontSizeMap[newLevel] || '32px',
            });
        },
    } : null;

    if (!BlockComponent) {
        return (
            <div ref={setNodeRef} style={style} className="p-4 bg-red-100 text-red-600 rounded">
                Unknown block type: {block.type}
            </div>
        );
    }

    const canMoveUp = blockIndex > 0;
    const canMoveDown = blockIndex < totalBlocks - 1;

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={`${blockClasses} relative group cursor-grab active:cursor-grabbing ${isDragging ? 'z-50' : ''} ${isSelected ? 'lb-block-selected' : ''}`}
            data-block-type={block.type}
            onClick={(e) => {
                e.stopPropagation();
                onSelect(block.id);
            }}
            {...attributes}
            {...listeners}
        >
            {/* Block Toolbar at TOP - for regular blocks */}
            {isSelected && !toolbarAtBottom && (
                <BlockToolbar
                    block={block}
                    onMoveUp={() => onMoveBlock(block.id, 'up')}
                    onMoveDown={() => onMoveBlock(block.id, 'down')}
                    onDelete={() => onDelete(block.id)}
                    onDuplicate={() => onDuplicateBlock(block.id)}
                    canMoveUp={canMoveUp}
                    canMoveDown={canMoveDown}
                    textFormatProps={textFormatProps}
                    alignProps={alignProps}
                    columnsProps={columnsProps}
                    headingLevelProps={headingLevelProps}
                />
            )}

            {/* Block component */}
            <BlockComponent
                props={block.props}
                isSelected={isSelected}
                onUpdate={(newProps) => onUpdate(block.id, newProps)}
                onInsertBlockAfter={onInsertBlockAfter ? (blockType) => onInsertBlockAfter(block.id, blockType) : undefined}
                {...(hasTextFormatting ? {
                    onRegisterTextFormat: setTextFormatProps,
                    onDelete: () => onDelete(block.id),
                    onReplaceBlock: onReplaceBlock ? (blockType) => onReplaceBlock(block.id, blockType) : undefined,
                    context: context,
                } : {})}
                {...(hasColumnCount ? {
                    blockId: block.id,
                    onSelect: onSelect,
                    selectedBlockId: selectedBlockId,
                    onUpdateNested: onUpdate, // Raw handler for nested blocks (takes blockId, newProps)
                    onDeleteNested: onDeleteNested,
                    onMoveNestedBlock: onMoveNestedBlock,
                    onDuplicateNestedBlock: onDuplicateNestedBlock,
                } : {})}
            />

            {/* Block Toolbar at BOTTOM - for blocks with their own toolbar (like text-editor) */}
            {isSelected && toolbarAtBottom && (
                <BlockToolbar
                    block={block}
                    onMoveUp={() => onMoveBlock(block.id, 'up')}
                    onMoveDown={() => onMoveBlock(block.id, 'down')}
                    onDelete={() => onDelete(block.id)}
                    onDuplicate={() => onDuplicateBlock(block.id)}
                    canMoveUp={canMoveUp}
                    canMoveDown={canMoveDown}
                    textFormatProps={textFormatProps}
                    alignProps={alignProps}
                    columnsProps={columnsProps}
                    headingLevelProps={headingLevelProps}
                    position="bottom"
                />
            )}
        </div>
    );
};

const Canvas = ({ blocks, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, onInsertBlockAfter, onReplaceBlock, canvasSettings, previewMode = 'desktop', context = 'post' }) => {
    const { setNodeRef, isOver } = useDroppable({
        id: 'canvas',
    });

    const blockIds = blocks.map(b => b.id);

    // Get layout styles from canvasSettings (same format as blocks)
    const canvasLayoutStyles = layoutStylesToCSS(canvasSettings?.layoutStyles || {});

    // Get preview width based on mode
    const getPreviewWidth = () => {
        switch (previewMode) {
            case 'mobile':
                return '375px';
            case 'tablet':
                return '768px';
            case 'desktop':
            default:
                return canvasSettings?.width || '700px';
        }
    };

    // Default settings (width and padding are still separate)
    const settings = {
        width: getPreviewWidth(),
        contentPadding: canvasSettings?.contentPadding || '32px',
        contentMargin: canvasSettings?.contentMargin || '40px',
    };

    // Outer container background style (minimal - just padding for now)
    const outerBackgroundStyle = {
        backgroundColor: '#f3f4f6',
        padding: settings.contentMargin,
    };

    // Content area background style - uses layoutStyles (same as blocks)
    const contentBackgroundStyle = {
        backgroundColor: canvasLayoutStyles.backgroundColor || '#ffffff',
        fontFamily: canvasLayoutStyles.fontFamily || 'Arial, sans-serif',
        fontSize: canvasLayoutStyles.fontSize,
        fontWeight: canvasLayoutStyles.fontWeight,
        lineHeight: canvasLayoutStyles.lineHeight,
        color: canvasLayoutStyles.color,
        textAlign: canvasLayoutStyles.textAlign,
        // Background image
        backgroundImage: canvasLayoutStyles.backgroundImage,
        backgroundSize: canvasLayoutStyles.backgroundSize,
        backgroundPosition: canvasLayoutStyles.backgroundPosition,
        backgroundRepeat: canvasLayoutStyles.backgroundRepeat,
        // Border
        borderTopWidth: canvasLayoutStyles.borderTopWidth,
        borderRightWidth: canvasLayoutStyles.borderRightWidth,
        borderBottomWidth: canvasLayoutStyles.borderBottomWidth,
        borderLeftWidth: canvasLayoutStyles.borderLeftWidth,
        borderStyle: canvasLayoutStyles.borderStyle || 'solid',
        borderColor: canvasLayoutStyles.borderColor,
        borderTopLeftRadius: canvasLayoutStyles.borderTopLeftRadius || '8px',
        borderTopRightRadius: canvasLayoutStyles.borderTopRightRadius || '8px',
        borderBottomLeftRadius: canvasLayoutStyles.borderBottomLeftRadius || '8px',
        borderBottomRightRadius: canvasLayoutStyles.borderBottomRightRadius || '8px',
        // Box shadow
        boxShadow: canvasLayoutStyles.boxShadow || '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
        // Margin/Padding from layout styles
        marginTop: canvasLayoutStyles.marginTop,
        marginRight: canvasLayoutStyles.marginRight,
        marginBottom: canvasLayoutStyles.marginBottom,
        marginLeft: canvasLayoutStyles.marginLeft,
        paddingTop: canvasLayoutStyles.paddingTop,
        paddingRight: canvasLayoutStyles.paddingRight,
        paddingBottom: canvasLayoutStyles.paddingBottom,
        paddingLeft: canvasLayoutStyles.paddingLeft,
    };

    return (
        <div
            className="flex-1 overflow-auto"
            style={outerBackgroundStyle}
            onClick={() => onSelect(null)}
        >
            <div className="mx-auto" style={{ maxWidth: settings.width }}>
                {/* Email preview container */}
                <div
                    ref={setNodeRef}
                    className={`min-h-[400px] transition-colors ${
                        isOver ? 'ring-2 ring-primary ring-offset-2' : ''
                    }`}
                    style={contentBackgroundStyle}
                >
                    <div style={{ padding: settings.contentPadding }}>
                        <SortableContext items={blockIds} strategy={verticalListSortingStrategy}>
                            {blocks.length > 0 ? (
                                <div>
                                    {/* Drop zone at the top */}
                                    <DropZone id="dropzone-0" isFirst={true} />

                                    {blocks.map((block, index) => (
                                        <div key={block.id}>
                                            <SortableBlock
                                                block={block}
                                                blockIndex={index}
                                                totalBlocks={blocks.length}
                                                selectedBlockId={selectedBlockId}
                                                onSelect={onSelect}
                                                onUpdate={onUpdate}
                                                onDelete={onDelete}
                                                onDeleteNested={onDeleteNested}
                                                onMoveBlock={onMoveBlock}
                                                onDuplicateBlock={onDuplicateBlock}
                                                onMoveNestedBlock={onMoveNestedBlock}
                                                onDuplicateNestedBlock={onDuplicateNestedBlock}
                                                onInsertBlockAfter={onInsertBlockAfter}
                                                onReplaceBlock={onReplaceBlock}
                                                context={context}
                                            />
                                            {/* Drop zone after each block */}
                                            <DropZone id={`dropzone-${index + 1}`} />
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className={`border-2 border-dashed rounded-lg p-12 text-center transition-colors ${
                                    isOver ? 'border-primary bg-primary/10' : 'border-gray-300'
                                }`}>
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p className="mt-4 text-gray-500 font-medium">{__('Drag blocks here to start building')}</p>
                                    <p className="mt-1 text-gray-400 text-sm">{__('or click blocks on the left to add them')}</p>
                                </div>
                            )}
                        </SortableContext>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Canvas;
