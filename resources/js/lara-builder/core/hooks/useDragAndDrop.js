/**
 * useDragAndDrop - Hook for drag and drop functionality
 *
 * Handles drag start, drag end, collision detection, and block placement.
 */

import { useState, useCallback } from "react";
import { closestCenter, pointerWithin } from "@dnd-kit/core";
import { LaraHooks } from "../../hooks-system/LaraHooks";
import { BuilderHooks } from "../../hooks-system/HookNames";
import { blockRegistry } from "../../registry/BlockRegistry";

/**
 * @param {Object} options
 * @param {Array} options.blocks - Current blocks array
 * @param {Object} options.actions - Builder actions from context
 * @returns {Object} DnD state and handlers
 */
export function useDragAndDrop({ blocks, actions }) {
    const [activeId, setActiveId] = useState(null);

    // Drag start handler
    const handleDragStart = useCallback((event) => {
        setActiveId(event.active.id);
        LaraHooks.doAction(BuilderHooks.ACTION_DRAG_START, event);
    }, []);

    // Drag end handler
    const handleDragEnd = useCallback(
        (event) => {
            const { active, over } = event;
            setActiveId(null);

            LaraHooks.doAction(BuilderHooks.ACTION_DRAG_END, event);

            if (!over) return;

            const overId = over.id;
            const overData = over.data.current;

            // Dragging from palette
            if (active.data.current?.type === "palette") {
                const blockType = active.data.current.blockType;

                // Don't allow nested columns
                if (blockType === "columns" && overData?.type === "column") {
                    return;
                }

                const newBlock = blockRegistry.createInstance(blockType);
                if (!newBlock) return;

                // Dropping into a column
                if (overData?.type === "column") {
                    const { parentId: pId, columnIndex: colIdx } = overData;
                    actions.addNestedBlock(pId, colIdx, newBlock);
                    return;
                }

                // Add to main canvas
                if (overId === "canvas") {
                    actions.addBlock(newBlock);
                } else if (overId.toString().startsWith("dropzone-")) {
                    const dropIndex = parseInt(
                        overId.toString().replace("dropzone-", ""),
                        10
                    );
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

            // Moving a nested block
            if (active.data.current?.type === "nested") {
                const {
                    parentId: sourceParentId,
                    columnIndex: sourceColumnIndex,
                } = active.data.current;

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
                        const column =
                            blocks.find((b) => b.id === sourceParentId)?.props
                                ?.children?.[sourceColumnIndex] || [];
                        const oldIndex = column.findIndex(
                            (b) => b.id === active.id
                        );
                        const newIndex = column.findIndex(
                            (b) => b.id === over.id
                        );

                        if (oldIndex !== -1 && newIndex !== -1) {
                            actions.moveNestedBlock(
                                sourceParentId,
                                sourceColumnIndex,
                                oldIndex,
                                sourceColumnIndex,
                                newIndex
                            );
                        }
                        return;
                    }
                }

                // Moving to empty column - handled by Canvas component
                if (overData?.type === "column") {
                    // Cross-column move logic
                }
            }

            // Reordering in main canvas
            if (active.id !== over.id) {
                const oldIndex = blocks.findIndex((i) => i.id === active.id);
                const newIndex = blocks.findIndex((i) => i.id === over.id);

                if (oldIndex !== -1 && newIndex !== -1) {
                    actions.moveBlock(oldIndex, newIndex);
                }
            }
        },
        [blocks, actions]
    );

    // Custom collision detection
    const customCollisionDetection = useCallback((args) => {
        const pointerCollisions = pointerWithin(args);

        if (pointerCollisions.length > 0) {
            const nestedCollision = pointerCollisions.find(
                (c) =>
                    c.data?.droppableContainer?.data?.current?.type === "nested"
            );
            if (nestedCollision) return [nestedCollision];

            const columnCollision = pointerCollisions.find((c) =>
                c.id.toString().startsWith("column-")
            );
            if (columnCollision) return [columnCollision];

            return [pointerCollisions[0]];
        }

        return closestCenter(args);
    }, []);

    return {
        activeId,
        handleDragStart,
        handleDragEnd,
        customCollisionDetection,
    };
}

export default useDragAndDrop;
