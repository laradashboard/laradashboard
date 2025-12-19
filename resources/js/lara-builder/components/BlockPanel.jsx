import { useState, useRef, useMemo, useEffect, useCallback } from 'react';
import { useDraggable } from '@dnd-kit/core';
import { blockRegistry } from '../registry/BlockRegistry';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';
import { __ } from '@lara-builder/i18n';

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

    const handleClick = (e) => {
        // Stop propagation to prevent the sidebar's onClick from deselecting the block
        e.stopPropagation();
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
            className="flex flex-col items-center justify-center p-2 bg-white border border-gray-200 rounded-lg cursor-grab hover:border-primary hover:bg-primary/10 transition-colors active:cursor-grabbing"
            title={__(block.label)}
        >
            <iconify-icon icon={block.icon} width="24" height="24" class="text-primary"></iconify-icon>
            <span className="text-[10px] text-gray-600 font-medium mt-1 text-center leading-tight">{__(block.label)}</span>
        </div>
    );
};

const BlockPanel = ({ onAddBlock, context = null }) => {
    const [searchQuery, setSearchQuery] = useState('');
    const [blockVersion, setBlockVersion] = useState(0);

    // Listen for new blocks being registered
    useEffect(() => {
        const handleBlockRegistered = () => {
            // Increment version to force re-computation of blocks
            setBlockVersion((v) => v + 1);
        };

        // Subscribe to block registration events
        LaraHooks.addAction(BuilderHooks.ACTION_BLOCK_REGISTERED, handleBlockRegistered);

        return () => {
            LaraHooks.removeAction(BuilderHooks.ACTION_BLOCK_REGISTERED, handleBlockRegistered);
        };
    }, []);

    // Get blocks filtered by context (if provided) or all blocks
    // Re-compute when blockVersion changes (new blocks registered)
    const allBlocks = useMemo(() => {
        return context
            ? blockRegistry.getBlocksForContext(context)
            : blockRegistry.getAll();
    }, [context, blockVersion]);

    // Get unique categories from filtered blocks
    const categories = useMemo(() => {
        const cats = new Set(allBlocks.map((b) => b.category));
        return Array.from(cats);
    }, [allBlocks]);

    // Filter blocks based on search query
    const filteredBlocks = useMemo(() => {
        if (!searchQuery.trim()) {
            return allBlocks;
        }
        const query = searchQuery.toLowerCase().trim();
        return allBlocks.filter(block =>
            block.label.toLowerCase().includes(query) ||
            block.type.toLowerCase().includes(query) ||
            block.category.toLowerCase().includes(query)
        );
    }, [allBlocks, searchQuery]);

    // Get categories that have matching blocks
    const filteredCategories = useMemo(() => {
        if (!searchQuery.trim()) {
            return categories;
        }
        const categoriesWithBlocks = new Set(filteredBlocks.map(b => b.category));
        return categories.filter(cat => categoriesWithBlocks.has(cat));
    }, [categories, filteredBlocks, searchQuery]);

    return (
        <div className="h-full flex flex-col overflow-hidden">
            {/* Search input */}
            <div className="mb-3 shrink-0">
                <div className="relative m-0.5">
                    <iconify-icon
                        icon="mdi:magnify"
                        width="18"
                        height="18"
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"
                    ></iconify-icon>
                    <input
                        type="text"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        placeholder={__('Search blocks...')}
                        className="form-control pl-9 pr-8 w-full"
                    />
                    {searchQuery && (
                        <button
                            onClick={() => setSearchQuery('')}
                            className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                        </button>
                    )}
                </div>
            </div>

            {/* Blocks list */}
            <div className="flex-1 overflow-y-auto">
                {filteredCategories.length === 0 ? (
                    <div className="text-center py-8 text-gray-400">
                        <iconify-icon icon="mdi:package-variant" width="32" height="32" class="mb-2 opacity-50"></iconify-icon>
                        <p className="text-sm">{__('No blocks found')}</p>
                    </div>
                ) : (
                    filteredCategories.map(category => {
                        const categoryBlocks = filteredBlocks.filter(block => block.category === category);
                        if (categoryBlocks.length === 0) return null;

                        return (
                            <div key={category} className="mb-4">
                                <h4 className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
                                    {__(category)}
                                </h4>
                                <div className="grid grid-cols-3 gap-1.5">
                                    {categoryBlocks.map(block => (
                                        <DraggableBlockItem key={block.type} block={block} onAddBlock={onAddBlock} />
                                    ))}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </div>
    );
};

export default BlockPanel;
