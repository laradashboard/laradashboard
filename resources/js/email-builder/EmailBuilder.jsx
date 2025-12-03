import { useState, useCallback, useEffect, useRef } from "react";
import {
    DndContext,
    DragOverlay,
    closestCenter,
    pointerWithin,
    PointerSensor,
    useSensor,
    useSensors,
} from "@dnd-kit/core";
import { arrayMove } from "@dnd-kit/sortable";
import { v4 as uuidv4 } from "uuid";

import BlockPanel from "./components/BlockPanel";
import Canvas from "./components/Canvas";
import PropertiesPanel from "./components/PropertiesPanel";
import Toast from "./components/Toast";
import { getBlock } from "./utils/blockRegistry";
import { generateEmailHtml } from "./utils/generateHtml";

const defaultCanvasSettings = {
    width: "700px",
    backgroundColor: "#f3f4f6",
    contentBackgroundColor: "#ffffff",
    contentPadding: "32px",
    contentMargin: "40px",
    contentBorderWidth: "0px",
    contentBorderColor: "#e5e7eb",
    contentBorderRadius: "8px",
    fontFamily: "Arial, sans-serif",
};

const EmailBuilder = ({
    initialData,
    onSave,
    onImageUpload,
    onVideoUpload,
    templateData,
    listUrl,
}) => {
    const [blocks, setBlocks] = useState(initialData?.blocks || []);
    const [canvasSettings, setCanvasSettings] = useState(
        initialData?.canvasSettings || defaultCanvasSettings
    );
    const [selectedBlockId, setSelectedBlockId] = useState(null);
    const [activeId, setActiveId] = useState(null);
    const [templateName, setTemplateName] = useState(templateData?.name || "");
    const [templateSubject, setTemplateSubject] = useState(
        templateData?.subject || ""
    );
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [toast, setToast] = useState(null);

    // Helper function to show toast
    const showToast = useCallback((variant, title, message) => {
        setToast({ variant, title, message });
    }, []);

    // Mobile drawer states
    const [leftDrawerOpen, setLeftDrawerOpen] = useState(false);
    const [rightDrawerOpen, setRightDrawerOpen] = useState(false);

    // Desktop sidebar collapse states
    const [leftSidebarCollapsed, setLeftSidebarCollapsed] = useState(false);
    const [rightSidebarCollapsed, setRightSidebarCollapsed] = useState(false);

    // Track initial state for comparison
    const initialStateRef = useRef({
        blocks: JSON.stringify(initialData?.blocks || []),
        canvasSettings: JSON.stringify(
            initialData?.canvasSettings || defaultCanvasSettings
        ),
        templateName: templateData?.name || "",
        templateSubject: templateData?.subject || "",
    });

    // Check if there are unsaved changes
    useEffect(() => {
        const currentState = {
            blocks: JSON.stringify(blocks),
            canvasSettings: JSON.stringify(canvasSettings),
            templateName,
            templateSubject,
        };

        const hasChanges =
            currentState.blocks !== initialStateRef.current.blocks ||
            currentState.canvasSettings !==
                initialStateRef.current.canvasSettings ||
            currentState.templateName !==
                initialStateRef.current.templateName ||
            currentState.templateSubject !==
                initialStateRef.current.templateSubject;

        setIsDirty(hasChanges);
    }, [blocks, canvasSettings, templateName, templateSubject]);

    // Warn user before leaving with unsaved changes
    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = "";
                return "";
            }
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () =>
            window.removeEventListener("beforeunload", handleBeforeUnload);
    }, [isDirty]);

    // Handle keyboard shortcuts for block operations
    useEffect(() => {
        const handleKeyDown = (e) => {
            // Only handle if a block is selected
            if (!selectedBlockId) return;

            // Check if user is typing in an input, textarea, or contenteditable element
            const activeElement = document.activeElement;
            const isEditing =
                activeElement?.tagName === "INPUT" ||
                activeElement?.tagName === "TEXTAREA" ||
                activeElement?.isContentEditable ||
                activeElement?.closest('[contenteditable="true"]') ||
                activeElement?.closest(".ProseMirror") || // TipTap/ProseMirror editor
                activeElement?.closest(".ql-editor") || // Quill editor
                activeElement?.closest('[data-text-editing="true"]');

            // If user is editing text, don't handle shortcuts
            if (isEditing) return;

            // Find block location info (needed for both delete and insert)
            let isNested = false;
            let parentId = null;
            let columnIndex = null;
            let blockIndex = -1;

            // Check top-level first
            blockIndex = blocks.findIndex((b) => b.id === selectedBlockId);

            // Check nested in columns
            if (blockIndex === -1) {
                for (const block of blocks) {
                    if (block.type === "columns" && block.props.children) {
                        for (
                            let colIdx = 0;
                            colIdx < block.props.children.length;
                            colIdx++
                        ) {
                            const column = block.props.children[colIdx];
                            const nestedIdx = column.findIndex(
                                (b) => b.id === selectedBlockId
                            );
                            if (nestedIdx !== -1) {
                                isNested = true;
                                parentId = block.id;
                                columnIndex = colIdx;
                                blockIndex = nestedIdx;
                                break;
                            }
                        }
                    }
                    if (isNested) break;
                }
            }

            // Delete block on Backspace or Delete key
            if (e.key === "Backspace" || e.key === "Delete") {
                e.preventDefault();

                if (isNested && parentId !== null && columnIndex !== null) {
                    // Delete nested block
                    setBlocks((prev) =>
                        prev.map((block) => {
                            if (block.id === parentId) {
                                const newChildren = [
                                    ...(block.props.children || []),
                                ];
                                newChildren[columnIndex] = newChildren[
                                    columnIndex
                                ].filter((b) => b.id !== selectedBlockId);
                                return {
                                    ...block,
                                    props: {
                                        ...block.props,
                                        children: newChildren,
                                    },
                                };
                            }
                            return block;
                        })
                    );
                } else {
                    // Delete top-level block
                    setBlocks((prev) =>
                        prev.filter((block) => block.id !== selectedBlockId)
                    );
                }

                setSelectedBlockId(null);
            }

            // Create new text block on Enter key
            if (e.key === "Enter") {
                e.preventDefault();

                const textBlockConfig = getBlock("text");
                const newBlock = {
                    id: uuidv4(),
                    type: "text",
                    props: { ...textBlockConfig.defaultProps, content: "" },
                };

                if (isNested && parentId !== null && columnIndex !== null) {
                    // Insert after nested block
                    setBlocks((prev) =>
                        prev.map((block) => {
                            if (block.id === parentId) {
                                const newChildren = [
                                    ...(block.props.children || []),
                                ];
                                const column = [...newChildren[columnIndex]];
                                column.splice(blockIndex + 1, 0, newBlock);
                                newChildren[columnIndex] = column;
                                return {
                                    ...block,
                                    props: {
                                        ...block.props,
                                        children: newChildren,
                                    },
                                };
                            }
                            return block;
                        })
                    );
                } else {
                    // Insert after top-level block
                    setBlocks((prev) => {
                        const newBlocks = [...prev];
                        newBlocks.splice(blockIndex + 1, 0, newBlock);
                        return newBlocks;
                    });
                }

                // Select the new block
                setSelectedBlockId(newBlock.id);
            }
        };

        window.addEventListener("keydown", handleKeyDown);
        return () => window.removeEventListener("keydown", handleKeyDown);
    }, [selectedBlockId, blocks]);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        })
    );

    // Find selected block - could be in top level or nested in columns
    const findBlock = useCallback(
        (blockId) => {
            // Check top level
            const topLevel = blocks.find((b) => b.id === blockId);
            if (topLevel) return topLevel;

            // Check nested in columns
            for (const block of blocks) {
                if (block.type === "columns" && block.props.children) {
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

    const handleDragStart = (event) => {
        setActiveId(event.active.id);
    };

    const handleDragEnd = (event) => {
        const { active, over } = event;
        setActiveId(null);

        if (!over) return;

        const overId = over.id;
        const overData = over.data.current;

        // If dragging from palette
        if (active.data.current?.type === "palette") {
            const blockType = active.data.current.blockType;
            const blockConfig = getBlock(blockType);

            // Don't allow nested columns
            if (blockType === "columns" && overData?.type === "column") {
                return;
            }

            if (blockConfig) {
                const newBlock = {
                    id: uuidv4(),
                    type: blockType,
                    props: { ...blockConfig.defaultProps },
                };

                // Dropping into a column
                if (overData?.type === "column") {
                    const { parentId, columnIndex } = overData;
                    setBlocks((prev) =>
                        prev.map((block) => {
                            if (block.id === parentId) {
                                const newChildren = [
                                    ...(block.props.children || []),
                                ];
                                // Ensure the column array exists
                                while (newChildren.length <= columnIndex) {
                                    newChildren.push([]);
                                }
                                newChildren[columnIndex] = [
                                    ...newChildren[columnIndex],
                                    newBlock,
                                ];
                                return {
                                    ...block,
                                    props: {
                                        ...block.props,
                                        children: newChildren,
                                    },
                                };
                            }
                            return block;
                        })
                    );
                    setSelectedBlockId(newBlock.id);
                    return;
                }

                // Add to main canvas
                if (overId === "canvas") {
                    setBlocks((prev) => [...prev, newBlock]);
                } else if (overId.toString().startsWith("dropzone-")) {
                    // Insert at specific drop zone position
                    const dropIndex = parseInt(
                        overId.toString().replace("dropzone-", ""),
                        10
                    );
                    setBlocks((prev) => {
                        const newBlocks = [...prev];
                        newBlocks.splice(dropIndex, 0, newBlock);
                        return newBlocks;
                    });
                } else {
                    // Insert at specific position in main canvas (dropping on a block)
                    const overIndex = blocks.findIndex((b) => b.id === overId);
                    if (overIndex !== -1) {
                        setBlocks((prev) => {
                            const newBlocks = [...prev];
                            newBlocks.splice(overIndex, 0, newBlock);
                            return newBlocks;
                        });
                    } else {
                        setBlocks((prev) => [...prev, newBlock]);
                    }
                }

                setSelectedBlockId(newBlock.id);
            }
            return;
        }

        // Moving a nested block
        if (active.data.current?.type === "nested") {
            const { parentId: sourceParentId, columnIndex: sourceColumnIndex } =
                active.data.current;

            // Reordering within the same column (dropping on another nested block)
            if (active.id !== over.id && overData?.type === "nested") {
                const {
                    parentId: targetParentId,
                    columnIndex: targetColumnIndex,
                } = overData;

                // Same column reordering
                if (
                    sourceParentId === targetParentId &&
                    sourceColumnIndex === targetColumnIndex
                ) {
                    setBlocks((prev) =>
                        prev.map((block) => {
                            if (block.id === sourceParentId) {
                                const newChildren = [
                                    ...(block.props.children || []),
                                ];
                                const column = [
                                    ...(newChildren[sourceColumnIndex] || []),
                                ];
                                const oldIndex = column.findIndex(
                                    (b) => b.id === active.id
                                );
                                const newIndex = column.findIndex(
                                    (b) => b.id === over.id
                                );
                                if (oldIndex !== -1 && newIndex !== -1) {
                                    newChildren[sourceColumnIndex] = arrayMove(
                                        column,
                                        oldIndex,
                                        newIndex
                                    );
                                }
                                return {
                                    ...block,
                                    props: {
                                        ...block.props,
                                        children: newChildren,
                                    },
                                };
                            }
                            return block;
                        })
                    );
                    return;
                }

                // Moving to a different column (dropping on a nested block in another column)
                setBlocks((prev) => {
                    let movedBlock = null;

                    // First, remove from source
                    const afterRemove = prev.map((block) => {
                        if (block.id === sourceParentId) {
                            const newChildren = [
                                ...(block.props.children || []),
                            ];
                            const sourceColumn = [
                                ...(newChildren[sourceColumnIndex] || []),
                            ];
                            const blockIndex = sourceColumn.findIndex(
                                (b) => b.id === active.id
                            );
                            if (blockIndex !== -1) {
                                movedBlock = sourceColumn[blockIndex];
                                sourceColumn.splice(blockIndex, 1);
                                newChildren[sourceColumnIndex] = sourceColumn;
                            }
                            return {
                                ...block,
                                props: {
                                    ...block.props,
                                    children: newChildren,
                                },
                            };
                        }
                        return block;
                    });

                    if (!movedBlock) return prev;

                    // Then, add to target column at the position of the over block
                    return afterRemove.map((block) => {
                        if (block.id === targetParentId) {
                            const newChildren = [
                                ...(block.props.children || []),
                            ];
                            while (newChildren.length <= targetColumnIndex) {
                                newChildren.push([]);
                            }
                            const targetColumn = [
                                ...newChildren[targetColumnIndex],
                            ];
                            const insertIndex = targetColumn.findIndex(
                                (b) => b.id === over.id
                            );
                            if (insertIndex !== -1) {
                                targetColumn.splice(insertIndex, 0, movedBlock);
                            } else {
                                targetColumn.push(movedBlock);
                            }
                            newChildren[targetColumnIndex] = targetColumn;
                            return {
                                ...block,
                                props: {
                                    ...block.props,
                                    children: newChildren,
                                },
                            };
                        }
                        return block;
                    });
                });
                return;
            }

            // Moving to an empty column or column drop zone
            if (overData?.type === "column") {
                const {
                    parentId: targetParentId,
                    columnIndex: targetColumnIndex,
                } = overData;

                setBlocks((prev) => {
                    let movedBlock = null;

                    // First, remove from source
                    const afterRemove = prev.map((block) => {
                        if (block.id === sourceParentId) {
                            const newChildren = [
                                ...(block.props.children || []),
                            ];
                            const sourceColumn = [
                                ...(newChildren[sourceColumnIndex] || []),
                            ];
                            const blockIndex = sourceColumn.findIndex(
                                (b) => b.id === active.id
                            );
                            if (blockIndex !== -1) {
                                movedBlock = sourceColumn[blockIndex];
                                sourceColumn.splice(blockIndex, 1);
                                newChildren[sourceColumnIndex] = sourceColumn;
                            }
                            return {
                                ...block,
                                props: {
                                    ...block.props,
                                    children: newChildren,
                                },
                            };
                        }
                        return block;
                    });

                    if (!movedBlock) return prev;

                    // Then, add to target
                    return afterRemove.map((block) => {
                        if (block.id === targetParentId) {
                            const newChildren = [
                                ...(block.props.children || []),
                            ];
                            while (newChildren.length <= targetColumnIndex) {
                                newChildren.push([]);
                            }
                            newChildren[targetColumnIndex] = [
                                ...newChildren[targetColumnIndex],
                                movedBlock,
                            ];
                            return {
                                ...block,
                                props: {
                                    ...block.props,
                                    children: newChildren,
                                },
                            };
                        }
                        return block;
                    });
                });
                return;
            }
        }

        // Reordering within main canvas
        if (active.id !== over.id) {
            setBlocks((items) => {
                const oldIndex = items.findIndex((i) => i.id === active.id);
                const newIndex = items.findIndex((i) => i.id === over.id);

                if (oldIndex !== -1 && newIndex !== -1) {
                    return arrayMove(items, oldIndex, newIndex);
                }
                return items;
            });
        }
    };

    const handleUpdateBlock = useCallback((blockId, newProps) => {
        setBlocks((prev) => {
            // Check if it's a top-level block
            const topLevelIndex = prev.findIndex((b) => b.id === blockId);
            if (topLevelIndex !== -1) {
                return prev.map((block) =>
                    block.id === blockId ? { ...block, props: newProps } : block
                );
            }

            // Check nested blocks in columns
            return prev.map((block) => {
                if (block.type === "columns" && block.props.children) {
                    const newChildren = block.props.children.map((column) =>
                        column.map((nestedBlock) =>
                            nestedBlock.id === blockId
                                ? { ...nestedBlock, props: newProps }
                                : nestedBlock
                        )
                    );
                    return {
                        ...block,
                        props: { ...block.props, children: newChildren },
                    };
                }
                return block;
            });
        });
    }, []);

    const handleDeleteBlock = useCallback(
        (blockId) => {
            setBlocks((prev) => prev.filter((block) => block.id !== blockId));
            if (selectedBlockId === blockId) {
                setSelectedBlockId(null);
            }
        },
        [selectedBlockId]
    );

    const handleDeleteNestedBlock = useCallback(
        (blockId, parentId, columnIndex) => {
            setBlocks((prev) =>
                prev.map((block) => {
                    if (block.id === parentId) {
                        const newChildren = [...(block.props.children || [])];
                        if (newChildren[columnIndex]) {
                            newChildren[columnIndex] = newChildren[
                                columnIndex
                            ].filter((b) => b.id !== blockId);
                        }
                        return {
                            ...block,
                            props: { ...block.props, children: newChildren },
                        };
                    }
                    return block;
                })
            );
            if (selectedBlockId === blockId) {
                setSelectedBlockId(null);
            }
        },
        [selectedBlockId]
    );

    const handleMoveBlock = useCallback((blockId, direction) => {
        setBlocks((prev) => {
            const index = prev.findIndex((b) => b.id === blockId);
            if (index === -1) return prev;

            const newIndex = direction === "up" ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= prev.length) return prev;

            return arrayMove(prev, index, newIndex);
        });
    }, []);

    const handleDuplicateBlock = useCallback((blockId) => {
        setBlocks((prev) => {
            const index = prev.findIndex((b) => b.id === blockId);
            if (index === -1) return prev;

            const blockToDuplicate = prev[index];
            const duplicatedBlock = {
                ...blockToDuplicate,
                id: uuidv4(),
                props: { ...blockToDuplicate.props },
            };

            // If it's a columns block, also duplicate nested blocks with new IDs
            if (
                blockToDuplicate.type === "columns" &&
                blockToDuplicate.props.children
            ) {
                duplicatedBlock.props.children =
                    blockToDuplicate.props.children.map((column) =>
                        column.map((nestedBlock) => ({
                            ...nestedBlock,
                            id: uuidv4(),
                            props: { ...nestedBlock.props },
                        }))
                    );
            }

            const newBlocks = [...prev];
            newBlocks.splice(index + 1, 0, duplicatedBlock);
            setSelectedBlockId(duplicatedBlock.id);
            return newBlocks;
        });
    }, []);

    // Move nested block within a column
    const handleMoveNestedBlock = useCallback(
        (blockId, parentId, columnIndex, direction) => {
            setBlocks((prev) =>
                prev.map((block) => {
                    if (block.id === parentId) {
                        const newChildren = [...(block.props.children || [])];
                        const column = [...(newChildren[columnIndex] || [])];
                        const index = column.findIndex((b) => b.id === blockId);

                        if (index === -1) return block;

                        const newIndex =
                            direction === "up" ? index - 1 : index + 1;
                        if (newIndex < 0 || newIndex >= column.length)
                            return block;

                        // Swap the blocks
                        const temp = column[index];
                        column[index] = column[newIndex];
                        column[newIndex] = temp;

                        newChildren[columnIndex] = column;
                        return {
                            ...block,
                            props: { ...block.props, children: newChildren },
                        };
                    }
                    return block;
                })
            );
        },
        []
    );

    // Duplicate nested block within a column
    const handleDuplicateNestedBlock = useCallback(
        (blockId, parentId, columnIndex) => {
            setBlocks((prev) =>
                prev.map((block) => {
                    if (block.id === parentId) {
                        const newChildren = [...(block.props.children || [])];
                        const column = [...(newChildren[columnIndex] || [])];
                        const index = column.findIndex((b) => b.id === blockId);

                        if (index === -1) return block;

                        const blockToDuplicate = column[index];
                        const duplicatedBlock = {
                            ...blockToDuplicate,
                            id: uuidv4(),
                            props: { ...blockToDuplicate.props },
                        };

                        column.splice(index + 1, 0, duplicatedBlock);
                        newChildren[columnIndex] = column;
                        setSelectedBlockId(duplicatedBlock.id);
                        return {
                            ...block,
                            props: { ...block.props, children: newChildren },
                        };
                    }
                    return block;
                })
            );
        },
        []
    );

    // Add block at the bottom (for click-to-add)
    const handleAddBlock = useCallback((blockType) => {
        const blockConfig = getBlock(blockType);
        if (blockConfig) {
            const newBlock = {
                id: uuidv4(),
                type: blockType,
                props: { ...blockConfig.defaultProps },
            };
            setBlocks((prev) => [...prev, newBlock]);
            setSelectedBlockId(newBlock.id);
        }
    }, []);

    const handleSave = async () => {
        if (!templateName.trim()) {
            showToast("error", "Validation Error", "Template name is required");
            return;
        }

        setSaving(true);

        try {
            const html = generateEmailHtml(blocks, canvasSettings);
            const designJson = { blocks, canvasSettings, version: "1.0" };

            const result = await onSave({
                name: templateName,
                subject: templateSubject,
                body_html: html,
                design_json: designJson,
            });

            // Update the initial state reference after successful save
            initialStateRef.current = {
                blocks: JSON.stringify(blocks),
                canvasSettings: JSON.stringify(canvasSettings),
                templateName,
                templateSubject,
            };
            setIsDirty(false);

            // Show success toast
            const isEdit = !!templateData?.uuid;
            showToast(
                "success",
                isEdit ? "Template Updated" : "Template Created",
                result?.message || (isEdit ? "Template updated successfully!" : "Template created successfully!")
            );

            // If creating a new template, redirect to edit page
            if (!isEdit && result?.id) {
                setTimeout(() => {
                    window.location.href = `${listUrl.replace(/\/$/, '')}/${result.id}/edit`;
                }, 500);
            }
        } catch (error) {
            showToast("error", "Save Failed", error.message || "Failed to save template");
        } finally {
            setSaving(false);
        }
    };

    // Custom collision detection that prefers nested blocks, then columns
    const customCollisionDetection = useCallback((args) => {
        // First check for pointer collisions
        const pointerCollisions = pointerWithin(args);

        if (pointerCollisions.length > 0) {
            // Prefer nested block collisions for reordering within columns
            const nestedCollision = pointerCollisions.find(
                (c) =>
                    c.data?.droppableContainer?.data?.current?.type === "nested"
            );
            if (nestedCollision) {
                return [nestedCollision];
            }

            // Then check for column collisions
            const columnCollision = pointerCollisions.find((c) =>
                c.id.toString().startsWith("column-")
            );
            if (columnCollision) {
                return [columnCollision];
            }

            // Return the first pointer collision if any
            return [pointerCollisions[0]];
        }

        // Fallback to closest center for main canvas
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
                        <a
                            href={listUrl}
                            onClick={(e) => {
                                if (isDirty) {
                                    const confirmed = window.confirm(
                                        "You have unsaved changes. Are you sure you want to leave?"
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
                                Back to Templates
                            </span>
                        </a>
                        <div className="h-6 w-px bg-gray-300 hidden sm:block"></div>
                        <h1 className="text-sm sm:text-lg font-semibold text-gray-800">
                            {templateData?.uuid
                                ? "Edit"
                                : "Create"}
                            <span className="hidden sm:inline"> Template</span>
                        </h1>
                        {isDirty && (
                            <span className="text-xs text-orange-600 bg-orange-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md font-medium">
                                <span className="hidden sm:inline">Unsaved changes</span>
                                <span className="sm:hidden">•</span>
                            </span>
                        )}
                    </div>

                    <div className="flex items-center gap-2 sm:gap-4">
                        {/* Name input - Hidden on mobile, shown in properties drawer */}
                        <div className="hidden md:flex items-center gap-2">
                            <label className="text-sm font-medium text-gray-600">
                                Name:
                            </label>
                            <input
                                type="text"
                                value={templateName}
                                onChange={(e) =>
                                    setTemplateName(e.target.value)
                                }
                                placeholder="Template name..."
                                className="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary w-48"
                            />
                        </div>

                        {/* Subject input - Hidden on mobile, shown in properties drawer */}
                        <div className="hidden lg:flex items-center gap-2">
                            <label className="text-sm font-medium text-gray-600">
                                Subject:
                            </label>
                            <input
                                type="text"
                                value={templateSubject}
                                onChange={(e) =>
                                    setTemplateSubject(e.target.value)
                                }
                                placeholder="Email subject..."
                                className="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary w-64"
                            />
                        </div>

                        {/* Save button */}
                        <button
                            onClick={handleSave}
                            disabled={saving}
                            className={`gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 ${
                                saving
                                    ? "btn-default cursor-not-allowed"
                                    : "btn-primary"
                            }`}
                        >
                            {saving ? (
                                <>
                                    <svg
                                        className="animate-spin h-4 w-4"
                                        viewBox="0 0 24 24"
                                    >
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
                                    <iconify-icon
                                        icon="mdi:content-save"
                                        class="h-4 w-4"
                                    ></iconify-icon>
                                    <span className="hidden sm:inline">Save Template</span>
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
                    <div className={`hidden lg:flex bg-white border-r border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${leftSidebarCollapsed ? 'w-12' : 'w-64'}`}>
                        {leftSidebarCollapsed ? (
                            /* Collapsed state - just show expand button */
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
                            /* Expanded state */
                            <div className="flex flex-col h-full p-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        Blocks
                                    </h3>
                                    <button
                                        onClick={() => setLeftSidebarCollapsed(true)}
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title="Hide Blocks"
                                    >
                                        <iconify-icon icon="mdi:chevron-left" width="18" height="18"></iconify-icon>
                                    </button>
                                </div>
                                <BlockPanel onAddBlock={handleAddBlock} />
                            </div>
                        )}
                    </div>

                    {/* Left Drawer - Block palette (Mobile) */}
                    {leftDrawerOpen && (
                        <div className="lg:hidden fixed inset-0 z-50">
                            {/* Backdrop */}
                            <div
                                className="absolute inset-0 bg-black/50"
                                onClick={() => setLeftDrawerOpen(false)}
                            ></div>
                            {/* Drawer */}
                            <div className="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-xl flex flex-col animate-slide-in-left">
                                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        Blocks
                                    </h3>
                                    <button
                                        onClick={() => setLeftDrawerOpen(false)}
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 p-4 overflow-hidden">
                                    <BlockPanel onAddBlock={(type) => {
                                        handleAddBlock(type);
                                        setLeftDrawerOpen(false);
                                    }} />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Canvas */}
                    <Canvas
                        blocks={blocks}
                        selectedBlockId={selectedBlockId}
                        onSelect={setSelectedBlockId}
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
                    <div className={`hidden lg:flex bg-white border-l border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${rightSidebarCollapsed ? 'w-12' : 'w-72'}`}>
                        {rightSidebarCollapsed ? (
                            /* Collapsed state - just show expand button */
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
                            /* Expanded state */
                            <div className="flex flex-col h-full p-4 overflow-hidden">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        Properties
                                    </h3>
                                    <button
                                        onClick={() => setRightSidebarCollapsed(true)}
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title="Hide Properties"
                                    >
                                        <iconify-icon icon="mdi:chevron-right" width="18" height="18"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto">
                                    <PropertiesPanel
                                        selectedBlock={selectedBlock}
                                        onUpdate={handleUpdateBlock}
                                        onImageUpload={onImageUpload}
                                        onVideoUpload={onVideoUpload}
                                        canvasSettings={canvasSettings}
                                        onCanvasSettingsUpdate={setCanvasSettings}
                                    />
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Drawer - Properties (Mobile) */}
                    {rightDrawerOpen && (
                        <div className="lg:hidden fixed inset-0 z-50">
                            {/* Backdrop */}
                            <div
                                className="absolute inset-0 bg-black/50"
                                onClick={() => setRightDrawerOpen(false)}
                            ></div>
                            {/* Drawer */}
                            <div className="absolute right-0 top-0 bottom-0 w-80 bg-white shadow-xl flex flex-col animate-slide-in-right">
                                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        Properties
                                    </h3>
                                    <button
                                        onClick={() => setRightDrawerOpen(false)}
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 p-4 overflow-y-auto">
                                    {/* Mobile-only template inputs */}
                                    <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                                        <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                            Template Details
                                        </h4>
                                        <div className="space-y-3">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                    Name
                                                </label>
                                                <input
                                                    type="text"
                                                    value={templateName}
                                                    onChange={(e) => setTemplateName(e.target.value)}
                                                    placeholder="Template name..."
                                                    className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                    Subject
                                                </label>
                                                <input
                                                    type="text"
                                                    value={templateSubject}
                                                    onChange={(e) => setTemplateSubject(e.target.value)}
                                                    placeholder="Email subject..."
                                                    className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <PropertiesPanel
                                        selectedBlock={selectedBlock}
                                        onUpdate={handleUpdateBlock}
                                        onImageUpload={onImageUpload}
                                        onVideoUpload={onVideoUpload}
                                        canvasSettings={canvasSettings}
                                        onCanvasSettingsUpdate={setCanvasSettings}
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Drag overlay */}
            <DragOverlay>
                {activeId && activeId.toString().startsWith("palette-") && (
                    <div className="p-4 bg-white border-2 border-primary rounded-lg shadow-lg opacity-80">
                        <span className="text-sm font-medium">
                            {getBlock(activeId.replace("palette-", ""))?.label}
                        </span>
                    </div>
                )}
            </DragOverlay>

            {/* Toast notification */}
            <Toast toast={toast} onClose={() => setToast(null)} />
        </DndContext>
    );
};

export default EmailBuilder;
