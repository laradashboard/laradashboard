import { useState, useRef } from 'react';
import { useDraggable } from '@dnd-kit/core';
import { getAllBlocks, getCategories } from '../utils/blockRegistry';

const DraggableBlockItem = ({ block, onAddBlock }) => {
    const [wasDragged, setWasDragged] = useState(false);
    const mouseDownPos = useRef(null);

    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id: `palette-${block.type}`,
        data: {
            type: 'palette',
            blockType: block.type,
        },
    });

    const style = {
        transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
        opacity: isDragging ? 0.5 : 1,
    };

    // Track if user dragged or just clicked
    const handleMouseDown = (e) => {
        mouseDownPos.current = { x: e.clientX, y: e.clientY };
        setWasDragged(false);
    };

    const handleMouseMove = (e) => {
        if (mouseDownPos.current) {
            const dx = Math.abs(e.clientX - mouseDownPos.current.x);
            const dy = Math.abs(e.clientY - mouseDownPos.current.y);
            if (dx > 5 || dy > 5) {
                setWasDragged(true);
            }
        }
    };

    const handleClick = () => {
        // Only add block if user didn't drag
        if (!wasDragged && onAddBlock) {
            onAddBlock(block.type);
        }
        mouseDownPos.current = null;
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            {...listeners}
            {...attributes}
            onMouseDown={handleMouseDown}
            onMouseMove={handleMouseMove}
            onClick={handleClick}
            className="flex flex-col items-center justify-center p-2 bg-white border border-gray-200 rounded-lg cursor-grab hover:border-blue-400 hover:bg-blue-50 transition-colors active:cursor-grabbing"
            title={block.label}
        >
            <iconify-icon icon={block.icon} width="24" height="24" class="text-blue-600"></iconify-icon>
            <span className="text-[10px] text-gray-600 font-medium mt-1 text-center leading-tight">{block.label}</span>
        </div>
    );
};

const BlockPanel = ({ onAddBlock }) => {
    const categories = getCategories();
    const blocks = getAllBlocks();

    return (
        <div className="h-full overflow-y-auto">
            {categories.map(category => (
                <div key={category} className="mb-4">
                    <h4 className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
                        {category}
                    </h4>
                    <div className="grid grid-cols-3 gap-1.5">
                        {blocks
                            .filter(block => block.category === category)
                            .map(block => (
                                <DraggableBlockItem key={block.type} block={block} onAddBlock={onAddBlock} />
                            ))
                        }
                    </div>
                </div>
            ))}
        </div>
    );
};

export default BlockPanel;
