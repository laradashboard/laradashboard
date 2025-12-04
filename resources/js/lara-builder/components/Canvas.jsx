import { useState } from 'react';
import { useDroppable } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { getBlockComponent } from '../blocks';
import BlockToolbar from './BlockToolbar';
import { layoutStylesToCSS } from './LayoutStylesSection';

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
                <span className="text-primary text-sm font-medium">Drop here</span>
            )}
        </div>
    );
};

// Blocks that support alignment-only toolbar
const ALIGN_ONLY_BLOCKS = ['image', 'button', 'quote', 'video', 'countdown', 'social', 'footer'];

const SortableBlock = ({ block, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, totalBlocks, blockIndex }) => {
    const [textFormatProps, setTextFormatProps] = useState(null);

    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: block.id });

    // Get layout styles from block props
    const layoutStyles = layoutStylesToCSS(block.props?.layoutStyles);

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
        // Apply layout styles (margin, padding, width, height, etc.)
        ...layoutStyles,
    };

    const BlockComponent = getBlockComponent(block.type);
    const isSelected = selectedBlockId === block.id;
    const isTextBasedBlock = block.type === 'heading' || block.type === 'text' || block.type === 'list' || block.type === 'text-editor';
    const isAlignOnlyBlock = ALIGN_ONLY_BLOCKS.includes(block.type);
    const isColumnsBlock = block.type === 'columns';
    // Blocks with their own toolbar (like text-editor) - always show toolbar at bottom
    const toolbarAtBottom = block.type === 'text-editor';

    // Alignment props for align-only blocks
    const alignProps = isAlignOnlyBlock ? {
        align: block.props?.align || 'center',
        onAlignChange: (newAlign) => onUpdate(block.id, { ...block.props, align: newAlign }),
    } : null;

    // Column props for columns block
    const columnsProps = isColumnsBlock ? {
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
            className={`relative group cursor-grab active:cursor-grabbing ${isDragging ? 'z-50' : ''}`}
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
                />
            )}

            {/* Block component */}
            <BlockComponent
                props={block.props}
                isSelected={isSelected}
                onUpdate={(newProps) => onUpdate(block.id, newProps)}
                {...(isTextBasedBlock ? {
                    onRegisterTextFormat: setTextFormatProps,
                } : {})}
                {...(isColumnsBlock ? {
                    blockId: block.id,
                    onSelect: onSelect,
                    selectedBlockId: selectedBlockId,
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
                    position="bottom"
                />
            )}
        </div>
    );
};

const Canvas = ({ blocks, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, canvasSettings }) => {
    const { setNodeRef, isOver } = useDroppable({
        id: 'canvas',
    });

    const blockIds = blocks.map(b => b.id);

    // Get layout styles from canvasSettings (same format as blocks)
    const canvasLayoutStyles = layoutStylesToCSS(canvasSettings?.layoutStyles || {});

    // Default settings (width and padding are still separate)
    const settings = {
        width: canvasSettings?.width || '700px',
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
                                    <p className="mt-4 text-gray-500 font-medium">Drag blocks here to start building</p>
                                    <p className="mt-1 text-gray-400 text-sm">or click blocks on the left to add them</p>
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
