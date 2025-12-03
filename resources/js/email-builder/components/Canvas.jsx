import { useDroppable } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { getBlockComponent } from '../blocks';
import BlockToolbar from './BlockToolbar';

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
                    ? 'h-16 bg-blue-100 border-2 border-dashed border-blue-400 rounded-lg my-2 flex items-center justify-center'
                    : isFirst ? 'h-0' : 'h-1'
            }`}
        >
            {isOver && (
                <span className="text-blue-500 text-sm font-medium">Drop here</span>
            )}
        </div>
    );
};

const SortableBlock = ({ block, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, totalBlocks, blockIndex }) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: block.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const BlockComponent = getBlockComponent(block.type);
    const isSelected = selectedBlockId === block.id;

    if (!BlockComponent) {
        return (
            <div ref={setNodeRef} style={style} className="p-4 bg-red-100 text-red-600 rounded">
                Unknown block type: {block.type}
            </div>
        );
    }

    // Check if this is a columns block - it needs extra props
    const isColumnsBlock = block.type === 'columns';

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
            {/* Block Toolbar - shows when selected */}
            {isSelected && (
                <BlockToolbar
                    block={block}
                    onMoveUp={() => onMoveBlock(block.id, 'up')}
                    onMoveDown={() => onMoveBlock(block.id, 'down')}
                    onDelete={() => onDelete(block.id)}
                    onDuplicate={() => onDuplicateBlock(block.id)}
                    canMoveUp={canMoveUp}
                    canMoveDown={canMoveDown}
                />
            )}

            {/* Block component */}
            <BlockComponent
                props={block.props}
                isSelected={isSelected}
                onUpdate={(newProps) => onUpdate(block.id, newProps)}
                {...(isColumnsBlock ? {
                    blockId: block.id,
                    onSelect: onSelect,
                    selectedBlockId: selectedBlockId,
                    onDeleteNested: onDeleteNested,
                    onMoveNestedBlock: onMoveNestedBlock,
                    onDuplicateNestedBlock: onDuplicateNestedBlock,
                } : {})}
            />
        </div>
    );
};

const Canvas = ({ blocks, selectedBlockId, onSelect, onUpdate, onDelete, onDeleteNested, onMoveBlock, onDuplicateBlock, onMoveNestedBlock, onDuplicateNestedBlock, canvasSettings }) => {
    const { setNodeRef, isOver } = useDroppable({
        id: 'canvas',
    });

    const blockIds = blocks.map(b => b.id);

    // Default settings
    const settings = {
        width: canvasSettings?.width || '700px',
        backgroundColor: canvasSettings?.backgroundColor || '#f3f4f6',
        backgroundImage: canvasSettings?.backgroundImage || '',
        backgroundSize: canvasSettings?.backgroundSize || 'cover',
        backgroundPosition: canvasSettings?.backgroundPosition || 'center',
        backgroundRepeat: canvasSettings?.backgroundRepeat || 'no-repeat',
        contentBackgroundColor: canvasSettings?.contentBackgroundColor || '#ffffff',
        contentBackgroundImage: canvasSettings?.contentBackgroundImage || '',
        contentBackgroundSize: canvasSettings?.contentBackgroundSize || 'cover',
        contentBackgroundPosition: canvasSettings?.contentBackgroundPosition || 'center',
        contentBackgroundRepeat: canvasSettings?.contentBackgroundRepeat || 'no-repeat',
        contentPadding: canvasSettings?.contentPadding || '32px',
        contentMargin: canvasSettings?.contentMargin || '40px',
        contentBorderWidth: canvasSettings?.contentBorderWidth || '0px',
        contentBorderColor: canvasSettings?.contentBorderColor || '#e5e7eb',
        contentBorderRadius: canvasSettings?.contentBorderRadius || '8px',
        fontFamily: canvasSettings?.fontFamily || 'Arial, sans-serif',
    };

    const borderStyle = settings.contentBorderWidth !== '0px'
        ? `${settings.contentBorderWidth} solid ${settings.contentBorderColor}`
        : 'none';

    // Outer container background style
    const outerBackgroundStyle = {
        backgroundColor: settings.backgroundColor,
        padding: settings.contentMargin,
        ...(settings.backgroundImage && {
            backgroundImage: `url(${settings.backgroundImage})`,
            backgroundSize: settings.backgroundSize,
            backgroundPosition: settings.backgroundPosition,
            backgroundRepeat: settings.backgroundRepeat,
        }),
    };

    // Content area background style
    const contentBackgroundStyle = {
        backgroundColor: settings.contentBackgroundColor,
        fontFamily: settings.fontFamily,
        borderRadius: settings.contentBorderRadius,
        border: borderStyle,
        ...(settings.contentBackgroundImage && {
            backgroundImage: `url(${settings.contentBackgroundImage})`,
            backgroundSize: settings.contentBackgroundSize,
            backgroundPosition: settings.contentBackgroundPosition,
            backgroundRepeat: settings.contentBackgroundRepeat,
        }),
    };

    return (
        <div
            className="flex-1 overflow-auto"
            style={outerBackgroundStyle}
        >
            <div className="mx-auto" style={{ maxWidth: settings.width }}>
                {/* Email preview container */}
                <div
                    ref={setNodeRef}
                    className={`shadow-lg min-h-[400px] transition-colors ${
                        isOver ? 'ring-2 ring-blue-400 ring-offset-2' : ''
                    }`}
                    style={contentBackgroundStyle}
                    onClick={() => onSelect(null)}
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
                                    isOver ? 'border-blue-400 bg-blue-50' : 'border-gray-300'
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
