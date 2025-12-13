import { useState } from "react";
import { useDroppable } from "@dnd-kit/core";
import {
    SortableContext,
    verticalListSortingStrategy,
    useSortable,
} from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { getBlockComponent } from "../index";
import BlockToolbar from "../../components/BlockToolbar";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

// Nested sortable block within a column
const NestedSortableBlock = ({
    block,
    columnIndex,
    parentId,
    onSelect,
    selectedBlockId,
    onUpdate,
    onDelete,
    onMoveNested,
    onDuplicateNested,
    blockIndex,
    totalBlocks,
}) => {
    const [textFormatProps, setTextFormatProps] = useState(null);
    const [alignProps, setAlignProps] = useState(null);

    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: block.id,
        data: {
            type: "nested",
            columnIndex,
            parentId,
        },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const BlockComponent = getBlockComponent(block.type);
    const isSelected = selectedBlockId === block.id;

    const canMoveUp = blockIndex > 0;
    const canMoveDown = blockIndex < totalBlocks - 1;

    // Handler for blocks to register their text format capabilities
    const handleRegisterTextFormat = (formatProps) => {
        if (formatProps) {
            setTextFormatProps({
                editorRef: formatProps.editorRef,
                align: formatProps.align,
                onAlignChange: formatProps.onAlignChange,
            });
        } else {
            setTextFormatProps(null);
        }
    };

    // Handler for blocks to register alignment-only capabilities
    const handleRegisterAlign = (alignData) => {
        if (alignData) {
            setAlignProps({
                align: alignData.align,
                onAlignChange: alignData.onAlignChange,
            });
        } else {
            setAlignProps(null);
        }
    };

    if (!BlockComponent) {
        return (
            <div
                ref={setNodeRef}
                style={style}
                className="p-2 bg-red-100 text-red-600 rounded text-xs"
            >
                Unknown: {block.type}
            </div>
        );
    }

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={`relative group cursor-grab active:cursor-grabbing ${
                isDragging ? "z-50" : ""
            }`}
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
                    onMoveUp={() =>
                        onMoveNested(block.id, parentId, columnIndex, "up")
                    }
                    onMoveDown={() =>
                        onMoveNested(block.id, parentId, columnIndex, "down")
                    }
                    onDelete={() => onDelete(block.id, parentId, columnIndex)}
                    onDuplicate={() =>
                        onDuplicateNested(block.id, parentId, columnIndex)
                    }
                    canMoveUp={canMoveUp}
                    canMoveDown={canMoveDown}
                    textFormatProps={textFormatProps}
                    alignProps={alignProps}
                />
            )}

            <BlockComponent
                props={block.props}
                isSelected={isSelected}
                onUpdate={(newProps) => onUpdate(block.id, newProps)}
                onRegisterTextFormat={handleRegisterTextFormat}
                onRegisterAlign={handleRegisterAlign}
            />
        </div>
    );
};

// Droppable column zone
const DroppableColumn = ({
    columnIndex,
    parentId,
    blocks,
    onSelect,
    selectedBlockId,
    onUpdate,
    onDelete,
    onMoveNested,
    onDuplicateNested,
}) => {
    const droppableId = `column-${parentId}-${columnIndex}`;

    const { setNodeRef, isOver } = useDroppable({
        id: droppableId,
        data: {
            type: "column",
            columnIndex,
            parentId,
        },
    });

    const blockIds = blocks.map((b) => b.id);

    return (
        <div
            ref={setNodeRef}
            className={`min-h-[60px] p-2 rounded-lg transition-colors ${
                isOver
                    ? "bg-primary/10 border-2 border-primary border-dashed"
                    : blocks.length === 0
                    ? "bg-gray-50 border-2 border-dashed border-gray-300"
                    : "bg-gray-50/50"
            }`}
        >
            <SortableContext
                items={blockIds}
                strategy={verticalListSortingStrategy}
            >
                {blocks.length > 0 ? (
                    <div className="space-y-2">
                        {blocks.map((block, index) => (
                            <NestedSortableBlock
                                key={block.id}
                                block={block}
                                blockIndex={index}
                                totalBlocks={blocks.length}
                                columnIndex={columnIndex}
                                parentId={parentId}
                                onSelect={onSelect}
                                selectedBlockId={selectedBlockId}
                                onUpdate={onUpdate}
                                onDelete={onDelete}
                                onMoveNested={onMoveNested}
                                onDuplicateNested={onDuplicateNested}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="flex items-center justify-center h-full min-h-[50px] text-gray-400 text-xs">
                        <div className="text-center">
                            <svg
                                className="mx-auto h-5 w-5 mb-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={1.5}
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            <span>{__("Drop here")}</span>
                        </div>
                    </div>
                )}
            </SortableContext>
        </div>
    );
};

const ColumnsBlock = ({
    props,
    isSelected,
    blockId,
    onSelect,
    selectedBlockId,
    onUpdateNested,
    onDeleteNested,
    onMoveNestedBlock,
    onDuplicateNestedBlock,
}) => {
    const {
        columns = 2,
        gap = "20px",
        children = [],
        verticalAlign = "stretch",
        horizontalAlign = "stretch",
    } = props;

    const columnCount = Math.min(Math.max(parseInt(columns) || 2, 1), 6);

    // Ensure children array has correct length
    const columnChildren = Array.from(
        { length: columnCount },
        (_, i) => children[i] || []
    );

    // Map alignment values to CSS
    const alignItemsMap = {
        start: "flex-start",
        center: "center",
        end: "flex-end",
        stretch: "stretch",
    };

    const justifyContentMap = {
        start: "flex-start",
        center: "center",
        end: "flex-end",
        stretch: "stretch",
        "space-between": "space-between",
        "space-around": "space-around",
    };

    // Base container styles
    const defaultContainerStyle = {
        padding: "8px 0",
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(
        defaultContainerStyle,
        props.layoutStyles
    );

    // Grid styles with alignment
    const gridStyle = {
        display: "flex",
        flexWrap: "wrap",
        gap: gap,
        alignItems: alignItemsMap[verticalAlign] || "stretch",
        justifyContent: justifyContentMap[horizontalAlign] || "stretch",
    };

    // Column style - flex basis for equal widths when stretch
    const getColumnStyle = () => {
        if (horizontalAlign === "stretch") {
            return {
                flex: `1 1 calc(${100 / columnCount}% - ${gap})`,
                minWidth: 0,
            };
        }
        return {
            flex: "0 0 auto",
            width: `calc(${100 / columnCount}% - ${gap})`,
            minWidth: 0,
        };
    };

    return (
        <div className="transition-all" style={containerStyle}>
            <div style={gridStyle}>
                {columnChildren.map((columnBlocks, index) => (
                    <div key={index} style={getColumnStyle()}>
                        <DroppableColumn
                            columnIndex={index}
                            parentId={blockId}
                            blocks={columnBlocks}
                            onSelect={onSelect}
                            selectedBlockId={selectedBlockId}
                            onUpdate={onUpdateNested}
                            onDelete={onDeleteNested}
                            onMoveNested={onMoveNestedBlock}
                            onDuplicateNested={onDuplicateNestedBlock}
                        />
                    </div>
                ))}
            </div>
        </div>
    );
};

export default ColumnsBlock;
