/**
 * PostBuilder - Extended LaraBuilder for Post/Page editing
 *
 * Includes post-specific properties panel with:
 * - Title, Slug
 * - Status, Publish date
 * - Featured image
 * - Taxonomies (categories, tags)
 * - Parent post (hierarchical)
 * - Excerpt
 */

import { useState, useCallback, useEffect, useRef, useMemo } from 'react';
import {
    DndContext,
    DragOverlay,
    closestCenter,
    pointerWithin,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';

import { BuilderProvider, useBuilder } from '../core/BuilderContext';
import { useHistory } from '../core/hooks/useHistory';
import { useBlocks } from '../core/hooks/useBlocks';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';
import { blockRegistry } from '../registry/BlockRegistry';

// Import components
import BlockPanel from './BlockPanel';
import Canvas from './Canvas';
import Toast from './Toast';
import PostPropertiesPanel from './PostPropertiesPanel';

/**
 * PostBuilder Inner Component (uses context)
 */
function PostBuilderInner({
    onSave,
    onImageUpload,
    onVideoUpload,
    listUrl,
    postData,
    taxonomies,
    selectedTerms: initialSelectedTerms,
    parentPosts,
    postType,
    postTypeModel,
    statuses,
}) {
    const {
        state,
        actions,
        canUndo,
        canRedo,
        undo,
        redo,
        getHtml,
        getSaveData,
        context,
    } = useBuilder();

    const { blocks, selectedBlockId, canvasSettings, isDirty } = state;

    // Enable keyboard shortcuts for history
    useHistory({ enableKeyboardShortcuts: true });

    // Use blocks hook for add block functionality
    const { addBlockAfterSelected } = useBlocks();

    // Post-specific state
    const [title, setTitle] = useState(postData?.title || '');
    const [slug, setSlug] = useState(postData?.slug || '');
    const [status, setStatus] = useState(postData?.status || 'draft');
    const [excerpt, setExcerpt] = useState(postData?.excerpt || '');
    const [publishedAt, setPublishedAt] = useState(postData?.published_at || '');
    const [parentId, setParentId] = useState(postData?.parent_id || '');
    const [selectedTerms, setSelectedTerms] = useState(initialSelectedTerms || {});
    const [featuredImage, setFeaturedImage] = useState(postData?.featured_image_url || '');
    const [removeFeaturedImage, setRemoveFeaturedImage] = useState(false);

    // Local UI state
    const [saving, setSaving] = useState(false);
    const [toast, setToast] = useState(null);
    const [activeId, setActiveId] = useState(null);

    // Mobile drawer states
    const [leftDrawerOpen, setLeftDrawerOpen] = useState(false);
    const [rightDrawerOpen, setRightDrawerOpen] = useState(false);

    // Desktop sidebar collapse states
    const [leftSidebarCollapsed, setLeftSidebarCollapsed] = useState(false);
    const [rightSidebarCollapsed, setRightSidebarCollapsed] = useState(false);

    // Show toast helper
    const showToast = useCallback((variant, title, message) => {
        setToast({ variant, title, message });
    }, []);

    // Track original data for dirty detection
    const originalDataRef = useRef({
        title: postData?.title || '',
        slug: postData?.slug || '',
        status: postData?.status || 'draft',
        excerpt: postData?.excerpt || '',
        published_at: postData?.published_at || '',
        parent_id: postData?.parent_id || '',
        featured_image_url: postData?.featured_image_url || '',
    });

    // Calculate post-specific dirty state
    const postDirty = useMemo(() => {
        return (
            title !== originalDataRef.current.title ||
            slug !== originalDataRef.current.slug ||
            status !== originalDataRef.current.status ||
            excerpt !== originalDataRef.current.excerpt ||
            publishedAt !== originalDataRef.current.published_at ||
            parentId !== originalDataRef.current.parent_id ||
            featuredImage !== originalDataRef.current.featured_image_url ||
            removeFeaturedImage
        );
    }, [title, slug, status, excerpt, publishedAt, parentId, featuredImage, removeFeaturedImage]);

    // Combined dirty state
    const isFormDirty = isDirty || postDirty;

    // Auto-generate slug from title
    const generateSlug = useCallback(() => {
        const generatedSlug = title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        setSlug(generatedSlug);
    }, [title]);

    // Warn user before leaving with unsaved changes
    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        };

        window.addEventListener('beforeunload', handleBeforeUnload);
        return () => window.removeEventListener('beforeunload', handleBeforeUnload);
    }, [isFormDirty]);

    // DnD sensors
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        })
    );

    // Find block helper
    const findBlock = useCallback(
        (blockId) => {
            const topLevel = blocks.find((b) => b.id === blockId);
            if (topLevel) return topLevel;

            for (const block of blocks) {
                if (block.type === 'columns' && block.props.children) {
                    for (const column of block.props.children) {
                        const nested = column.find((b) => b.id === blockId);
                        if (nested) return nested;
                    }
                }
            }
            return null;
        },
        [blocks]
    );

    const selectedBlock = findBlock(selectedBlockId);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (!selectedBlockId) return;

            const activeElement = document.activeElement;
            const isEditing =
                activeElement?.tagName === 'INPUT' ||
                activeElement?.tagName === 'TEXTAREA' ||
                activeElement?.isContentEditable ||
                activeElement?.closest('[contenteditable="true"]') ||
                activeElement?.closest('.ProseMirror') ||
                activeElement?.closest('.ql-editor') ||
                activeElement?.closest('[data-text-editing="true"]');

            if (isEditing) return;

            let isNested = false;
            let parentBlockId = null;
            let columnIndex = null;
            let blockIndex = blocks.findIndex((b) => b.id === selectedBlockId);

            if (blockIndex === -1) {
                for (const block of blocks) {
                    if (block.type === 'columns' && block.props.children) {
                        for (let colIdx = 0; colIdx < block.props.children.length; colIdx++) {
                            const column = block.props.children[colIdx];
                            const nestedIdx = column.findIndex((b) => b.id === selectedBlockId);
                            if (nestedIdx !== -1) {
                                isNested = true;
                                parentBlockId = block.id;
                                columnIndex = colIdx;
                                blockIndex = nestedIdx;
                                break;
                            }
                        }
                    }
                    if (isNested) break;
                }
            }

            if (e.key === 'Backspace' || e.key === 'Delete') {
                e.preventDefault();

                if (isNested && parentBlockId !== null && columnIndex !== null) {
                    actions.deleteNestedBlock(parentBlockId, columnIndex, selectedBlockId);
                } else {
                    actions.deleteBlock(selectedBlockId);
                }
            }

            if (e.key === 'Enter') {
                e.preventDefault();

                const textBlockDef = blockRegistry.get('text');
                if (textBlockDef) {
                    const newBlock = blockRegistry.createInstance('text', { content: '' });

                    if (isNested && parentBlockId !== null && columnIndex !== null) {
                        actions.addNestedBlock(parentBlockId, columnIndex, newBlock, blockIndex + 1);
                    } else {
                        actions.addBlock(newBlock, blockIndex + 1);
                    }
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [selectedBlockId, blocks, actions]);

    // Drag handlers
    const handleDragStart = (event) => {
        setActiveId(event.active.id);
        LaraHooks.doAction(BuilderHooks.ACTION_DRAG_START, event);
    };

    const handleDragEnd = (event) => {
        const { active, over } = event;
        setActiveId(null);

        LaraHooks.doAction(BuilderHooks.ACTION_DRAG_END, event);

        if (!over) return;

        const overId = over.id;
        const overData = over.data.current;

        if (active.data.current?.type === 'palette') {
            const blockType = active.data.current.blockType;

            if (blockType === 'columns' && overData?.type === 'column') {
                return;
            }

            const newBlock = blockRegistry.createInstance(blockType);
            if (!newBlock) return;

            if (overData?.type === 'column') {
                const { parentId: pId, columnIndex: colIdx } = overData;
                actions.addNestedBlock(pId, colIdx, newBlock);
                return;
            }

            if (overId === 'canvas') {
                actions.addBlock(newBlock);
            } else if (overId.toString().startsWith('dropzone-')) {
                const dropIndex = parseInt(overId.toString().replace('dropzone-', ''), 10);
                actions.addBlock(newBlock, dropIndex);
            } else {
                const overIndex = blocks.findIndex((b) => b.id === overId);
                if (overIndex !== -1) {
                    actions.addBlock(newBlock, overIndex);
                } else {
                    actions.addBlock(newBlock);
                }
            }

            LaraHooks.doAction(BuilderHooks.ACTION_DROP, newBlock, event);
            return;
        }

        if (active.data.current?.type === 'nested') {
            const { parentId: sourceParentId, columnIndex: sourceColumnIndex } = active.data.current;

            if (active.id !== over.id && overData?.type === 'nested') {
                const { parentId: targetParentId, columnIndex: targetColumnIndex } = overData;

                if (sourceParentId === targetParentId && sourceColumnIndex === targetColumnIndex) {
                    const column = blocks.find((b) => b.id === sourceParentId)?.props?.children?.[sourceColumnIndex] || [];
                    const oldIndex = column.findIndex((b) => b.id === active.id);
                    const newIndex = column.findIndex((b) => b.id === over.id);

                    if (oldIndex !== -1 && newIndex !== -1) {
                        actions.moveNestedBlock(sourceParentId, sourceColumnIndex, oldIndex, sourceColumnIndex, newIndex);
                    }
                    return;
                }
            }
        }

        if (active.id !== over.id) {
            const oldIndex = blocks.findIndex((i) => i.id === active.id);
            const newIndex = blocks.findIndex((i) => i.id === over.id);

            if (oldIndex !== -1 && newIndex !== -1) {
                actions.moveBlock(oldIndex, newIndex);
            }
        }
    };

    // Update block handler
    const handleUpdateBlock = useCallback(
        (blockId, newProps) => {
            actions.updateBlock(blockId, newProps);
        },
        [actions]
    );

    // Delete handlers
    const handleDeleteBlock = useCallback(
        (blockId) => {
            actions.deleteBlock(blockId);
        },
        [actions]
    );

    const handleDeleteNestedBlock = useCallback(
        (blockId, pId, colIdx) => {
            actions.deleteNestedBlock(pId, colIdx, blockId);
        },
        [actions]
    );

    // Move handlers
    const handleMoveBlock = useCallback(
        (blockId, direction) => {
            const index = blocks.findIndex((b) => b.id === blockId);
            if (index === -1) return;

            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= blocks.length) return;

            actions.moveBlock(index, newIndex);
        },
        [blocks, actions]
    );

    const handleMoveNestedBlock = useCallback(
        (blockId, pId, colIdx, direction) => {
            const block = blocks.find((b) => b.id === pId);
            if (!block?.props?.children?.[colIdx]) return;

            const column = block.props.children[colIdx];
            const index = column.findIndex((b) => b.id === blockId);
            if (index === -1) return;

            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= column.length) return;

            actions.moveNestedBlock(pId, colIdx, index, colIdx, newIndex);
        },
        [blocks, actions]
    );

    // Duplicate handlers
    const handleDuplicateBlock = useCallback(
        (blockId) => {
            actions.duplicateBlock(blockId);
        },
        [actions]
    );

    const handleDuplicateNestedBlock = useCallback(
        (blockId, pId, colIdx) => {
            const block = blocks.find((b) => b.id === pId);
            if (!block?.props?.children?.[colIdx]) return;

            const column = block.props.children[colIdx];
            const blockToDuplicate = column.find((b) => b.id === blockId);
            if (!blockToDuplicate) return;

            const duplicatedBlock = blockRegistry.createInstance(blockToDuplicate.type, blockToDuplicate.props);
            if (duplicatedBlock) {
                const index = column.findIndex((b) => b.id === blockId);
                actions.addNestedBlock(pId, colIdx, duplicatedBlock, index + 1);
            }
        },
        [blocks, actions]
    );

    // Add block handler - inserts below selected block if one is selected
    const handleAddBlock = useCallback(
        (blockType) => {
            addBlockAfterSelected(blockType);
        },
        [addBlockAfterSelected]
    );

    // Save handler
    const handleSave = async () => {
        if (!title.trim()) {
            showToast('error', 'Validation Error', 'Title is required');
            return;
        }

        LaraHooks.doAction(BuilderHooks.ACTION_BEFORE_SAVE, state);

        setSaving(true);

        try {
            const html = getHtml();
            const designJson = getSaveData();

            // Collect taxonomy term IDs
            const taxonomyData = {};
            Object.entries(selectedTerms).forEach(([taxonomyName, termIds]) => {
                taxonomyData[`taxonomy_${taxonomyName}`] = termIds;
            });

            const saveData = {
                title,
                slug: slug || undefined,
                status,
                excerpt,
                content: html,
                design_json: designJson,
                published_at: status === 'scheduled' ? publishedAt : undefined,
                parent_id: parentId || undefined,
                featured_image: featuredImage || undefined,
                remove_featured_image: removeFeaturedImage,
                ...taxonomyData,
            };

            const result = await onSave(saveData);

            // Mark as saved
            actions.markSaved();
            originalDataRef.current = {
                title,
                slug,
                status,
                excerpt,
                published_at: publishedAt,
                parent_id: parentId,
                featured_image_url: featuredImage,
            };
            setRemoveFeaturedImage(false);

            // Show success toast
            const isEdit = !!postData?.id;
            showToast(
                'success',
                isEdit ? 'Saved' : 'Created',
                result?.message || (isEdit ? 'Saved successfully!' : 'Created successfully!')
            );

            LaraHooks.doAction(BuilderHooks.ACTION_AFTER_SAVE, result);

            // Redirect for new posts - use redirect URL from response
            if (!isEdit && result?.redirect) {
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 500);
            }
        } catch (error) {
            showToast('error', 'Save Failed', error.message || 'Failed to save');
            LaraHooks.doAction(BuilderHooks.ACTION_SAVE_ERROR, error);
        } finally {
            setSaving(false);
        }
    };

    // Custom collision detection
    const customCollisionDetection = useCallback((args) => {
        const pointerCollisions = pointerWithin(args);

        if (pointerCollisions.length > 0) {
            const nestedCollision = pointerCollisions.find(
                (c) => c.data?.droppableContainer?.data?.current?.type === 'nested'
            );
            if (nestedCollision) return [nestedCollision];

            const columnCollision = pointerCollisions.find((c) =>
                c.id.toString().startsWith('column-')
            );
            if (columnCollision) return [columnCollision];

            return [pointerCollisions[0]];
        }

        return closestCenter(args);
    }, []);

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={customCollisionDetection}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            {/* Inline styles for drawer animations */}
            <style>{`
                @keyframes slideInLeft {
                    from { transform: translateX(-100%); }
                    to { transform: translateX(0); }
                }
                @keyframes slideInRight {
                    from { transform: translateX(100%); }
                    to { transform: translateX(0); }
                }
                .animate-slide-in-left {
                    animation: slideInLeft 0.2s ease-out forwards;
                }
                .animate-slide-in-right {
                    animation: slideInRight 0.2s ease-out forwards;
                }
            `}</style>

            <div className="h-screen flex flex-col bg-gray-100">
                {/* Header */}
                <header className="bg-white border-b border-gray-200 px-2 sm:px-4 py-2 sm:py-3 flex items-center justify-between shadow-sm flex-shrink-0">
                    <div className="flex items-center gap-2 sm:gap-4">
                        {listUrl && (
                            <a
                                href={listUrl}
                                onClick={(e) => {
                                    if (isFormDirty) {
                                        const confirmed = window.confirm(
                                            'You have unsaved changes. Are you sure you want to leave?'
                                        );
                                        if (!confirmed) {
                                            e.preventDefault();
                                        }
                                    }
                                }}
                                className="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                <span className="font-medium hidden sm:inline">
                                    Back to {postTypeModel.label}
                                </span>
                            </a>
                        )}
                        <div className="h-6 w-px bg-gray-300 hidden sm:block"></div>
                        <h1 className="text-sm sm:text-lg font-semibold text-gray-800">
                            {postData?.id ? 'Edit' : 'Create'}{' '}
                            <span className="hidden sm:inline">{postTypeModel.label_singular}</span>
                        </h1>

                        {/* History buttons */}
                        <div className="hidden sm:flex items-center gap-1 ml-2">
                            <button
                                onClick={undo}
                                disabled={!canUndo}
                                className={`p-1.5 rounded-md transition-colors ${
                                    canUndo
                                        ? 'hover:bg-gray-100 text-gray-600'
                                        : 'text-gray-300 cursor-not-allowed'
                                }`}
                                title="Undo (Ctrl+Z)"
                            >
                                <iconify-icon icon="mdi:undo" width="18" height="18"></iconify-icon>
                            </button>
                            <button
                                onClick={redo}
                                disabled={!canRedo}
                                className={`p-1.5 rounded-md transition-colors ${
                                    canRedo
                                        ? 'hover:bg-gray-100 text-gray-600'
                                        : 'text-gray-300 cursor-not-allowed'
                                }`}
                                title="Redo (Ctrl+Shift+Z)"
                            >
                                <iconify-icon icon="mdi:redo" width="18" height="18"></iconify-icon>
                            </button>
                        </div>

                        {isFormDirty && (
                            <span className="text-xs text-orange-600 bg-orange-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md font-medium">
                                <span className="hidden sm:inline">Unsaved changes</span>
                                <span className="sm:hidden">*</span>
                            </span>
                        )}
                    </div>

                    <div className="flex items-center gap-2 sm:gap-4">
                        {/* Title input */}
                        <div className="hidden md:flex items-center gap-2">
                            <input
                                type="text"
                                value={title}
                                onChange={(e) => setTitle(e.target.value)}
                                placeholder={`${postTypeModel.label_singular} title...`}
                                className="form-control w-64"
                            />
                        </div>

                        {/* Save button */}
                        <button
                            onClick={handleSave}
                            disabled={saving}
                            className={`gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 ${
                                saving ? 'btn-default cursor-not-allowed' : 'btn-primary'
                            }`}
                        >
                            {saving ? (
                                <>
                                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                            fill="none"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                    <span className="hidden sm:inline">Saving...</span>
                                </>
                            ) : (
                                <>
                                    <iconify-icon icon="mdi:content-save" class="h-4 w-4"></iconify-icon>
                                    <span className="hidden sm:inline">
                                        {postData?.id ? 'Update' : 'Publish'}
                                    </span>
                                </>
                            )}
                        </button>
                    </div>
                </header>

                {/* Main content */}
                <div className="flex-1 flex overflow-hidden relative">
                    {/* Mobile toggle buttons */}
                    <div className="lg:hidden fixed bottom-4 left-4 right-4 z-40 flex justify-between pointer-events-none">
                        <button
                            onClick={() => setLeftDrawerOpen(true)}
                            className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg shadow-lg hover:bg-primary/90 transition-colors"
                        >
                            <iconify-icon icon="mdi:plus-box-multiple" width="20" height="20"></iconify-icon>
                            <span className="text-sm font-medium">Blocks</span>
                        </button>
                        <button
                            onClick={() => setRightDrawerOpen(true)}
                            className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white rounded-lg shadow-lg hover:bg-gray-800 transition-colors"
                        >
                            <iconify-icon icon="mdi:cog" width="20" height="20"></iconify-icon>
                            <span className="text-sm font-medium">Properties</span>
                        </button>
                    </div>

                    {/* Left sidebar - Block palette (Desktop) */}
                    <div
                        className={`hidden lg:flex bg-white border-r border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${
                            leftSidebarCollapsed ? 'w-12' : 'w-64'
                        }`}
                    >
                        {leftSidebarCollapsed ? (
                            <div className="flex flex-col items-center py-4">
                                <button
                                    onClick={() => setLeftSidebarCollapsed(false)}
                                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                                    title="Show Blocks"
                                >
                                    <iconify-icon icon="mdi:chevron-right" width="20" height="20"></iconify-icon>
                                </button>
                                <button
                                    onClick={() => setLeftSidebarCollapsed(false)}
                                    className="mt-2 p-2 rounded-md hover:bg-primary/10 text-primary"
                                    title="Show Blocks"
                                >
                                    <iconify-icon icon="mdi:plus-box-multiple" width="20" height="20"></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div className="flex flex-col h-full p-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-semibold text-gray-900">Blocks</h3>
                                    <button
                                        onClick={() => setLeftSidebarCollapsed(true)}
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title="Hide Blocks"
                                    >
                                        <iconify-icon icon="mdi:chevron-left" width="18" height="18"></iconify-icon>
                                    </button>
                                </div>
                                <BlockPanel onAddBlock={handleAddBlock} context={context} />
                            </div>
                        )}
                    </div>

                    {/* Left Drawer - Mobile */}
                    {leftDrawerOpen && (
                        <div className="lg:hidden fixed inset-0 z-50">
                            <div
                                className="absolute inset-0 bg-black/50"
                                onClick={() => setLeftDrawerOpen(false)}
                            ></div>
                            <div className="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-xl flex flex-col animate-slide-in-left">
                                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                                    <h3 className="text-sm font-semibold text-gray-900">Blocks</h3>
                                    <button
                                        onClick={() => setLeftDrawerOpen(false)}
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 p-4 overflow-hidden">
                                    <BlockPanel
                                        onAddBlock={(type) => {
                                            handleAddBlock(type);
                                            setLeftDrawerOpen(false);
                                        }}
                                        context={context}
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Canvas */}
                    <Canvas
                        blocks={blocks}
                        selectedBlockId={selectedBlockId}
                        onSelect={actions.selectBlock}
                        onUpdate={handleUpdateBlock}
                        onDelete={handleDeleteBlock}
                        onDeleteNested={handleDeleteNestedBlock}
                        onMoveBlock={handleMoveBlock}
                        onDuplicateBlock={handleDuplicateBlock}
                        onMoveNestedBlock={handleMoveNestedBlock}
                        onDuplicateNestedBlock={handleDuplicateNestedBlock}
                        canvasSettings={canvasSettings}
                    />

                    {/* Right sidebar - Properties (Desktop) */}
                    <div
                        className={`hidden lg:flex bg-white border-l border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${
                            rightSidebarCollapsed ? 'w-12' : 'w-80'
                        }`}
                    >
                        {rightSidebarCollapsed ? (
                            <div className="flex flex-col items-center py-4">
                                <button
                                    onClick={() => setRightSidebarCollapsed(false)}
                                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                                    title="Show Properties"
                                >
                                    <iconify-icon icon="mdi:chevron-left" width="20" height="20"></iconify-icon>
                                </button>
                                <button
                                    onClick={() => setRightSidebarCollapsed(false)}
                                    className="mt-2 p-2 rounded-md hover:bg-gray-50 text-gray-600"
                                    title="Show Properties"
                                >
                                    <iconify-icon icon="mdi:cog" width="20" height="20"></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div className="flex flex-col h-full p-4 overflow-hidden">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-semibold text-gray-900">Properties</h3>
                                    <button
                                        onClick={() => setRightSidebarCollapsed(true)}
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title="Hide Properties"
                                    >
                                        <iconify-icon icon="mdi:chevron-right" width="18" height="18"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto">
                                    <PostPropertiesPanel
                                        selectedBlock={selectedBlock}
                                        onUpdate={handleUpdateBlock}
                                        onImageUpload={onImageUpload}
                                        onVideoUpload={onVideoUpload}
                                        canvasSettings={canvasSettings}
                                        onCanvasSettingsUpdate={actions.updateCanvasSettings}
                                        // Post-specific props
                                        title={title}
                                        setTitle={setTitle}
                                        slug={slug}
                                        setSlug={setSlug}
                                        generateSlug={generateSlug}
                                        status={status}
                                        setStatus={setStatus}
                                        excerpt={excerpt}
                                        setExcerpt={setExcerpt}
                                        publishedAt={publishedAt}
                                        setPublishedAt={setPublishedAt}
                                        parentId={parentId}
                                        setParentId={setParentId}
                                        selectedTerms={selectedTerms}
                                        setSelectedTerms={setSelectedTerms}
                                        featuredImage={featuredImage}
                                        setFeaturedImage={setFeaturedImage}
                                        removeFeaturedImage={removeFeaturedImage}
                                        setRemoveFeaturedImage={setRemoveFeaturedImage}
                                        taxonomies={taxonomies}
                                        parentPosts={parentPosts}
                                        postTypeModel={postTypeModel}
                                        statuses={statuses}
                                    />
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Drawer - Mobile */}
                    {rightDrawerOpen && (
                        <div className="lg:hidden fixed inset-0 z-50">
                            <div
                                className="absolute inset-0 bg-black/50"
                                onClick={() => setRightDrawerOpen(false)}
                            ></div>
                            <div className="absolute right-0 top-0 bottom-0 w-80 bg-white shadow-xl flex flex-col animate-slide-in-right">
                                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                                    <h3 className="text-sm font-semibold text-gray-900">Properties</h3>
                                    <button
                                        onClick={() => setRightDrawerOpen(false)}
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 p-4 overflow-y-auto">
                                    {/* Mobile title input */}
                                    <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Title
                                        </label>
                                        <input
                                            type="text"
                                            value={title}
                                            onChange={(e) => setTitle(e.target.value)}
                                            placeholder={`${postTypeModel.label_singular} title...`}
                                            className="form-control w-full"
                                        />
                                    </div>
                                    <PostPropertiesPanel
                                        selectedBlock={selectedBlock}
                                        onUpdate={handleUpdateBlock}
                                        onImageUpload={onImageUpload}
                                        onVideoUpload={onVideoUpload}
                                        canvasSettings={canvasSettings}
                                        onCanvasSettingsUpdate={actions.updateCanvasSettings}
                                        // Post-specific props
                                        title={title}
                                        setTitle={setTitle}
                                        slug={slug}
                                        setSlug={setSlug}
                                        generateSlug={generateSlug}
                                        status={status}
                                        setStatus={setStatus}
                                        excerpt={excerpt}
                                        setExcerpt={setExcerpt}
                                        publishedAt={publishedAt}
                                        setPublishedAt={setPublishedAt}
                                        parentId={parentId}
                                        setParentId={setParentId}
                                        selectedTerms={selectedTerms}
                                        setSelectedTerms={setSelectedTerms}
                                        featuredImage={featuredImage}
                                        setFeaturedImage={setFeaturedImage}
                                        removeFeaturedImage={removeFeaturedImage}
                                        setRemoveFeaturedImage={setRemoveFeaturedImage}
                                        taxonomies={taxonomies}
                                        parentPosts={parentPosts}
                                        postTypeModel={postTypeModel}
                                        statuses={statuses}
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Drag overlay */}
            <DragOverlay>
                {activeId && activeId.toString().startsWith('palette-') && (
                    <div className="p-4 bg-white border-2 border-primary rounded-lg shadow-lg opacity-80">
                        <span className="text-sm font-medium">
                            {blockRegistry.get(activeId.replace('palette-', ''))?.label}
                        </span>
                    </div>
                )}
            </DragOverlay>

            {/* Toast notification */}
            <Toast toast={toast} onClose={() => setToast(null)} />
        </DndContext>
    );
}

/**
 * PostBuilder - Main exported component
 */
function PostBuilder({
    context = 'page',
    initialData = null,
    postData = null,
    onSave,
    onImageUpload,
    onVideoUpload,
    listUrl,
    taxonomies = [],
    selectedTerms = {},
    parentPosts = {},
    postType = 'post',
    postTypeModel = {},
    statuses = {},
}) {
    // Fire init action
    useEffect(() => {
        LaraHooks.doAction(BuilderHooks.ACTION_INIT, { context, initialData });
    }, []);

    return (
        <BuilderProvider
            context={context}
            initialData={initialData}
            config={{}}
        >
            <PostBuilderInner
                onSave={onSave}
                onImageUpload={onImageUpload}
                onVideoUpload={onVideoUpload}
                listUrl={listUrl}
                postData={postData}
                taxonomies={taxonomies}
                selectedTerms={selectedTerms}
                parentPosts={parentPosts}
                postType={postType}
                postTypeModel={postTypeModel}
                statuses={statuses}
            />
        </BuilderProvider>
    );
}

export default PostBuilder;
