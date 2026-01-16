import { useDroppable } from "@dnd-kit/core";
import {
    SortableContext,
    verticalListSortingStrategy,
    useSortable,
} from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { useState } from "react";
import { getBlockComponent } from "../index";
import { getBlockSupports } from "../blockLoader";
import BlockToolbar from "../../components/BlockToolbar";
import { __ } from "@lara-builder/i18n";

// Nested sortable block within the section
const NestedSortableBlock = ({
    block,
    parentId,
    onSelect,
    selectedBlockId,
    onUpdate,
    onDelete,
    onMoveNested,
    onDuplicateNested,
    blockIndex,
    totalBlocks,
    // Props for deeply nested blocks (e.g., columns inside section)
    onUpdateNested,
    onDeleteNested,
    onMoveNestedBlock,
    onDuplicateNestedBlock,
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
            columnIndex: 0,
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
            {isSelected && (
                <BlockToolbar
                    block={block}
                    onMoveUp={() => onMoveNested(block.id, parentId, 0, "up")}
                    onMoveDown={() => onMoveNested(block.id, parentId, 0, "down")}
                    onDelete={() => onDelete(block.id, parentId, 0)}
                    onDuplicate={() => onDuplicateNested(block.id, parentId, 0)}
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
                {...(getBlockSupports(block.type).nesting || getBlockSupports(block.type).columnCount ? {
                    blockId: block.id,
                    onSelect: onSelect,
                    selectedBlockId: selectedBlockId,
                    onUpdateNested: onUpdateNested || onUpdate,
                    onDeleteNested: onDeleteNested || onDelete,
                    onMoveNestedBlock: onMoveNestedBlock || onMoveNested,
                    onDuplicateNestedBlock: onDuplicateNestedBlock || onDuplicateNested,
                } : {})}
            />
        </div>
    );
};

// Droppable section zone
const DroppableSection = ({
    parentId,
    blocks,
    onSelect,
    selectedBlockId,
    onUpdate,
    onDelete,
    onMoveNested,
    onDuplicateNested,
    // Props for deeply nested blocks
    onUpdateNested,
    onDeleteNested,
    onMoveNestedBlock,
    onDuplicateNestedBlock,
}) => {
    const droppableId = `section-${parentId}`;

    const { setNodeRef, isOver } = useDroppable({
        id: droppableId,
        data: {
            type: "column",
            columnIndex: 0,
            parentId,
        },
    });

    const blockIds = blocks.map((b) => b.id);

    return (
        <div
            ref={setNodeRef}
            className={`min-h-[80px] p-4 rounded-lg transition-colors ${
                isOver
                    ? "bg-primary/10 border-2 border-primary border-dashed"
                    : blocks.length === 0
                    ? "bg-gray-50/50 border-2 border-dashed border-gray-300"
                    : ""
            }`}
        >
            <SortableContext
                items={blockIds}
                strategy={verticalListSortingStrategy}
            >
                {blocks.length > 0 ? (
                    <div className="space-y-3">
                        {blocks.map((block, index) => (
                            <NestedSortableBlock
                                key={block.id}
                                block={block}
                                blockIndex={index}
                                totalBlocks={blocks.length}
                                parentId={parentId}
                                onSelect={onSelect}
                                selectedBlockId={selectedBlockId}
                                onUpdate={onUpdate}
                                onDelete={onDelete}
                                onMoveNested={onMoveNested}
                                onDuplicateNested={onDuplicateNested}
                                // Props for deeply nested blocks
                                onUpdateNested={onUpdateNested}
                                onDeleteNested={onDeleteNested}
                                onMoveNestedBlock={onMoveNestedBlock}
                                onDuplicateNestedBlock={onDuplicateNestedBlock}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="flex items-center justify-center h-full min-h-[60px] text-gray-400 text-sm">
                        <div className="text-center">
                            <svg
                                className="mx-auto h-6 w-6 mb-2"
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
                            <span>{__("Drop blocks here")}</span>
                        </div>
                    </div>
                )}
            </SortableContext>
        </div>
    );
};

const SectionBlock = ({
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
        fullWidth = true,
        containerMaxWidth = "1280px",
        contentAlign = "center",
        backgroundType = "solid",
        backgroundColor = "#ffffff",
        gradientFrom = "#f9fafb",
        gradientTo = "#f3f4f6",
        gradientDirection = "to-br",
        children = [[]],
    } = props;

    // Gradient direction mapping
    const gradientDirectionMap = {
        "to-t": "to top",
        "to-tr": "to top right",
        "to-r": "to right",
        "to-br": "to bottom right",
        "to-b": "to bottom",
        "to-bl": "to bottom left",
        "to-l": "to left",
        "to-tl": "to top left",
    };

    // Build background style
    const getBackgroundStyle = () => {
        if (backgroundType === "gradient") {
            const direction = gradientDirectionMap[gradientDirection] || "to bottom right";
            return `linear-gradient(${direction}, ${gradientFrom}, ${gradientTo})`;
        }
        return backgroundColor;
    };

    // Outer section style (full-width)
    const sectionStyle = {
        background: backgroundType === "gradient" ? getBackgroundStyle() : backgroundColor,
        padding: props.layoutStyles?.padding ? undefined : "48px 16px",
        ...(props.layoutStyles?.padding && {
            paddingTop: props.layoutStyles.padding.top || "48px",
            paddingRight: props.layoutStyles.padding.right || "16px",
            paddingBottom: props.layoutStyles.padding.bottom || "48px",
            paddingLeft: props.layoutStyles.padding.left || "16px",
        }),
    };

    // Inner container style
    const containerStyle = {
        maxWidth: fullWidth ? containerMaxWidth : "100%",
        margin: contentAlign === "center" ? "0 auto" : undefined,
        marginLeft: contentAlign === "left" ? "0" : undefined,
        marginRight: contentAlign === "right" ? "0" : undefined,
        width: "100%",
    };

    // Get the child blocks from the first (and only) column
    // Section uses same structure as columns: children = [[block1, block2, ...]]
    const childBlocks = Array.isArray(children[0]) ? children[0] : [];

    return (
        <div
            className={`transition-all rounded-lg ${isSelected ? "ring-2 ring-primary ring-offset-2" : ""}`}
            style={sectionStyle}
        >
            <div style={containerStyle}>
                <DroppableSection
                    parentId={blockId}
                    blocks={childBlocks}
                    onSelect={onSelect}
                    selectedBlockId={selectedBlockId}
                    onUpdate={onUpdateNested}
                    onDelete={onDeleteNested}
                    onMoveNested={onMoveNestedBlock}
                    onDuplicateNested={onDuplicateNestedBlock}
                    // Props for deeply nested blocks (e.g., blocks inside columns inside section)
                    onUpdateNested={onUpdateNested}
                    onDeleteNested={onDeleteNested}
                    onMoveNestedBlock={onMoveNestedBlock}
                    onDuplicateNestedBlock={onDuplicateNestedBlock}
                />
            </div>
        </div>
    );
};

export default SectionBlock;
