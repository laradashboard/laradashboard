/**
 * LaraBuilder - Unified Visual Builder Component
 *
 * A reusable, extensible visual builder for emails, pages, posts, and custom content.
 * Supports multiple contexts with different block sets, property panels, and output formats.
 *
 * @example
 * // Email builder
 * <LaraBuilder
 *   context="email"
 *   initialData={data}
 *   onSave={handleSave}
 *   templateData={{ name: 'My Template', subject: 'Hello' }}
 * />
 *
 * // Post builder
 * <LaraBuilder
 *   context="page"
 *   initialData={data}
 *   onSave={handleSave}
 *   postData={postData}
 *   taxonomies={taxonomies}
 *   PropertiesPanelComponent={PostPropertiesPanel}
 * />
 */

import { useState, useCallback, useEffect, useRef, useMemo } from "react";
import {
    DndContext,
    DragOverlay,
    closestCenter,
    pointerWithin,
    PointerSensor,
    useSensor,
    useSensors,
} from "@dnd-kit/core";

import { BuilderProvider, useBuilder } from "./BuilderContext";
import { useHistory } from "./hooks/useHistory";
import { useBlocks } from "./hooks/useBlocks";
import { LaraHooks } from "../hooks-system/LaraHooks";
import { BuilderHooks } from "../hooks-system/HookNames";
import { blockRegistry } from "../registry/BlockRegistry";
import { __ } from "@lara-builder/i18n";

// Import components
import BlockPanel from "../components/BlockPanel";
import Canvas from "../components/Canvas";
import PropertiesPanel from "../components/PropertiesPanel";
import Toast from "../components/Toast";
import EditorOptionsMenu from "../components/EditorOptionsMenu";
import CodeEditor from "../components/CodeEditor";

/**
 * LaraBuilder Inner Component (uses context)
 */
function LaraBuilderInner({
    onSave,
    onImageUpload,
    onVideoUpload,
    listUrl,
    showHeader = true,
    // Email-specific props
    templateData,
    // Post-specific props
    postData,
    taxonomies,
    selectedTerms: initialSelectedTerms,
    parentPosts,
    postType,
    postTypeModel,
    statuses,
    // Custom properties panel
    PropertiesPanelComponent,
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

    // ========================
    // Email-specific state
    // ========================
    const isEmailContext = context === "email" || context === "campaign";

    const [templateName, setTemplateName] = useState(templateData?.name || "");
    const [templateSubject, setTemplateSubject] = useState(
        templateData?.subject || ""
    );

    // Track template data changes for dirty detection
    const templateDataRef = useRef({
        name: templateData?.name || "",
        subject: templateData?.subject || "",
    });
    const [templateDirty, setTemplateDirty] = useState(false);

    useEffect(() => {
        // Only track template dirty state for email context
        if (!isEmailContext) {
            setTemplateDirty(false);
            return;
        }
        const hasTemplateChanges =
            templateName !== templateDataRef.current.name ||
            templateSubject !== templateDataRef.current.subject;
        setTemplateDirty(hasTemplateChanges);
    }, [templateName, templateSubject, isEmailContext]);

    // ========================
    // Post-specific state
    // ========================
    const isPostContext = context === "page" || context === "post";

    const [title, setTitle] = useState(postData?.title || "");
    const [slug, setSlug] = useState(postData?.slug || "");
    const [status, setStatus] = useState(postData?.status || "draft");
    const [excerpt, setExcerpt] = useState(postData?.excerpt || "");
    const [publishedAt, setPublishedAt] = useState(
        postData?.published_at || ""
    );
    const [parentId, setParentId] = useState(String(postData?.parent_id || ""));
    const [selectedTerms, setSelectedTerms] = useState(
        initialSelectedTerms || {}
    );
    const [featuredImage, setFeaturedImage] = useState(
        postData?.featured_image_url || ""
    );
    const [removeFeaturedImage, setRemoveFeaturedImage] = useState(false);

    // Track saved post data for dirty detection (use state so changes trigger re-render)
    const [savedPostData, setSavedPostData] = useState(() => ({
        title: postData?.title || "",
        slug: postData?.slug || "",
        status: postData?.status || "draft",
        excerpt: postData?.excerpt || "",
        publishedAt: postData?.published_at || "",
        parentId: String(postData?.parent_id || ""),
        featuredImage: postData?.featured_image_url || "",
    }));

    // Calculate post-specific dirty state
    const postDirty = useMemo(() => {
        if (!isPostContext) return false;
        return (
            title !== savedPostData.title ||
            slug !== savedPostData.slug ||
            status !== savedPostData.status ||
            excerpt !== savedPostData.excerpt ||
            publishedAt !== savedPostData.publishedAt ||
            parentId !== savedPostData.parentId ||
            featuredImage !== savedPostData.featuredImage ||
            removeFeaturedImage
        );
    }, [
        isPostContext,
        title,
        slug,
        status,
        excerpt,
        publishedAt,
        parentId,
        featuredImage,
        removeFeaturedImage,
        savedPostData,
    ]);

    // Auto-generate slug from title
    const generateSlug = useCallback(() => {
        const generatedSlug = title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, "")
            .replace(/\s+/g, "-")
            .replace(/-+/g, "-")
            .trim();
        setSlug(generatedSlug);
    }, [title]);

    // ========================
    // Local UI state
    // ========================
    const [saving, setSaving] = useState(false);
    const [toast, setToast] = useState(null);
    const [activeId, setActiveId] = useState(null);

    // Mobile drawer states
    const [leftDrawerOpen, setLeftDrawerOpen] = useState(false);
    const [rightDrawerOpen, setRightDrawerOpen] = useState(false);

    // Desktop sidebar collapse states
    const [leftSidebarCollapsed, setLeftSidebarCollapsed] = useState(false);
    const [rightSidebarCollapsed, setRightSidebarCollapsed] = useState(false);

    // Editor mode: 'visual' or 'code'
    const [editorMode, setEditorMode] = useState("visual");
    const [codeEditorHtml, setCodeEditorHtml] = useState("");

    // Preview mode: 'desktop', 'tablet', 'mobile'
    const [previewMode, setPreviewMode] = useState("desktop");

    // Show toast helper
    const showToast = useCallback((variant, titleText, message) => {
        setToast({ variant, title: titleText, message });
    }, []);

    // Combined dirty state
    const isFormDirty = isDirty || templateDirty || postDirty;

    // Warn user before leaving with unsaved changes
    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = "";
                return "";
            }
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () =>
            window.removeEventListener("beforeunload", handleBeforeUnload);
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

    // Keyboard shortcuts for block operations
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (!selectedBlockId) return;

            // Check if user is typing
            const activeElement = document.activeElement;
            const isEditing =
                activeElement?.tagName === "INPUT" ||
                activeElement?.tagName === "TEXTAREA" ||
                activeElement?.isContentEditable ||
                activeElement?.closest('[contenteditable="true"]') ||
                activeElement?.closest(".ProseMirror") ||
                activeElement?.closest(".ql-editor") ||
                activeElement?.closest('[data-text-editing="true"]');

            if (isEditing) return;

            // Find block location
            let isNested = false;
            let parentBlockId = null;
            let columnIndex = null;
            let blockIndex = blocks.findIndex((b) => b.id === selectedBlockId);

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

            // Delete on Backspace/Delete
            if (e.key === "Backspace" || e.key === "Delete") {
                e.preventDefault();

                if (
                    isNested &&
                    parentBlockId !== null &&
                    columnIndex !== null
                ) {
                    actions.deleteNestedBlock(
                        parentBlockId,
                        columnIndex,
                        selectedBlockId
                    );
                } else {
                    actions.deleteBlock(selectedBlockId);
                }
            }

            // Create new text block on Enter
            if (e.key === "Enter") {
                e.preventDefault();

                const textBlockDef = blockRegistry.get("text");
                if (textBlockDef) {
                    const newBlock = blockRegistry.createInstance("text", {
                        content: "",
                    });

                    if (
                        isNested &&
                        parentBlockId !== null &&
                        columnIndex !== null
                    ) {
                        actions.addNestedBlock(
                            parentBlockId,
                            columnIndex,
                            newBlock,
                            blockIndex + 1
                        );
                    } else {
                        actions.addBlock(newBlock, blockIndex + 1);
                    }
                }
            }
        };

        window.addEventListener("keydown", handleKeyDown);
        return () => window.removeEventListener("keydown", handleKeyDown);
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
            const { parentId: sourceParentId, columnIndex: sourceColumnIndex } =
                active.data.current;

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
                    const newIndex = column.findIndex((b) => b.id === over.id);

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

            // Moving to empty column
            if (overData?.type === "column") {
                // This requires cross-column move logic
                // For now, this is handled by the Canvas component
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
    };

    // Update block handler (for compatibility with existing components)
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

            const newIndex = direction === "up" ? index - 1 : index + 1;
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

            const newIndex = direction === "up" ? index - 1 : index + 1;
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

            const duplicatedBlock = blockRegistry.createInstance(
                blockToDuplicate.type,
                blockToDuplicate.props
            );
            if (duplicatedBlock) {
                const index = column.findIndex((b) => b.id === blockId);
                actions.addNestedBlock(pId, colIdx, duplicatedBlock, index + 1);
            }
        },
        [blocks, actions]
    );

    // Add block handler (for click-to-add) - inserts below selected block if one is selected
    const handleAddBlock = useCallback(
        (blockType) => {
            addBlockAfterSelected(blockType);
        },
        [addBlockAfterSelected]
    );

    // Insert a new block after a specific block (for Enter key in text/heading)
    const handleInsertBlockAfter = useCallback(
        (afterBlockId, blockType) => {
            const newBlock = blockRegistry.createInstance(blockType);
            if (!newBlock) return;

            // Check if it's a top-level block
            const topLevelIndex = blocks.findIndex(
                (b) => b.id === afterBlockId
            );
            if (topLevelIndex !== -1) {
                actions.addBlock(newBlock, topLevelIndex + 1);
                actions.selectBlock(newBlock.id);
                return;
            }

            // Check nested blocks in columns
            for (const block of blocks) {
                if (block.type === "columns" && block.props.children) {
                    for (
                        let colIdx = 0;
                        colIdx < block.props.children.length;
                        colIdx++
                    ) {
                        const column = block.props.children[colIdx];
                        const nestedIndex = column.findIndex(
                            (b) => b.id === afterBlockId
                        );
                        if (nestedIndex !== -1) {
                            actions.addNestedBlock(
                                block.id,
                                colIdx,
                                newBlock,
                                nestedIndex + 1
                            );
                            actions.selectBlock(newBlock.id);
                            return;
                        }
                    }
                }
            }
        },
        [blocks, actions]
    );

    // Editor mode handlers
    const handleEditorModeChange = useCallback(
        (mode) => {
            if (mode === "code" && editorMode === "visual") {
                // Switching to code mode - generate HTML from blocks
                const html = getHtml();
                setCodeEditorHtml(html);
            } else if (mode === "visual" && editorMode === "code") {
                // Switching to visual mode from code mode
                if (!codeEditorHtml.trim()) {
                    actions.setBlocks([]);
                }
            }
            setEditorMode(mode);
        },
        [editorMode, getHtml, codeEditorHtml, actions]
    );

    // Exit code editor - switch back to visual mode
    const handleExitCodeEditor = useCallback(() => {
        if (!codeEditorHtml.trim()) {
            actions.setBlocks([]);
        }
        setEditorMode("visual");
    }, [codeEditorHtml, actions]);

    // Copy all blocks to clipboard
    const handleCopyAllBlocks = useCallback(async () => {
        const blocksJson = JSON.stringify(blocks, null, 2);
        await navigator.clipboard.writeText(blocksJson);
        showToast(
            "success",
            __("Copied!"),
            __("All blocks copied to clipboard")
        );
    }, [blocks, showToast]);

    // Paste blocks from clipboard
    const handlePasteBlocks = useCallback(
        (text) => {
            try {
                const parsed = JSON.parse(text);
                if (Array.isArray(parsed)) {
                    parsed.forEach((blockData) => {
                        if (blockData.type) {
                            const newBlock = blockRegistry.createInstance(
                                blockData.type,
                                blockData.props
                            );
                            if (newBlock) {
                                actions.addBlock(newBlock);
                            }
                        }
                    });
                    showToast(
                        "success",
                        __("Pasted!"),
                        __(":count blocks pasted").replace(
                            ":count",
                            parsed.length
                        )
                    );
                }
            } catch (e) {
                if (editorMode === "code") {
                    setCodeEditorHtml(text);
                    showToast(
                        "success",
                        __("Pasted!"),
                        __("HTML content pasted")
                    );
                } else {
                    const newBlock = blockRegistry.createInstance("html", {
                        code: text,
                    });
                    if (newBlock) {
                        actions.addBlock(newBlock);
                        showToast(
                            "success",
                            __("Pasted!"),
                            __("HTML block created")
                        );
                    }
                }
            }
        },
        [actions, editorMode, showToast]
    );

    // Handle code editor HTML change
    const handleCodeEditorHtmlChange = useCallback((html) => {
        setCodeEditorHtml(html);
    }, []);

    // Save handler
    const handleSave = async () => {
        // Context-specific validation
        if (isEmailContext && !templateName.trim()) {
            showToast(
                "error",
                __("Validation Error"),
                __("Template name is required")
            );
            return;
        }
        if (isPostContext && !title.trim()) {
            showToast("error", __("Validation Error"), __("Title is required"));
            return;
        }

        LaraHooks.doAction(BuilderHooks.ACTION_BEFORE_SAVE, state);

        setSaving(true);

        try {
            // Use code editor HTML if in code mode, otherwise generate from blocks
            const html = editorMode === "code" ? codeEditorHtml : getHtml();
            const designJson = getSaveData();

            let saveData = {};

            // Context-specific save data
            if (isEmailContext) {
                saveData = {
                    name: templateName,
                    subject: templateSubject,
                    body_html: html,
                    design_json: designJson,
                };
            } else if (isPostContext) {
                // Collect taxonomy term IDs
                const taxonomyData = {};
                Object.entries(selectedTerms).forEach(
                    ([taxonomyName, termIds]) => {
                        taxonomyData[`taxonomy_${taxonomyName}`] = termIds;
                    }
                );

                saveData = {
                    title,
                    slug: slug || undefined,
                    status,
                    excerpt,
                    content: html,
                    design_json: designJson,
                    published_at:
                        status === "scheduled" ? publishedAt : undefined,
                    parent_id: parentId || undefined,
                    featured_image: featuredImage || undefined,
                    remove_featured_image: removeFeaturedImage,
                    ...taxonomyData,
                };
            }

            const result = await onSave(saveData);

            // Mark as saved
            actions.markSaved();

            // Update dirty tracking state
            if (isEmailContext) {
                templateDataRef.current = {
                    name: templateName,
                    subject: templateSubject,
                };
                setTemplateDirty(false);
            } else if (isPostContext) {
                setSavedPostData({
                    title,
                    slug,
                    status,
                    excerpt,
                    publishedAt,
                    parentId,
                    featuredImage,
                });
                setRemoveFeaturedImage(false);
            }

            // Show success toast
            const isEdit = !!(templateData?.uuid || postData?.id);
            showToast(
                "success",
                isEdit ? __("Saved") : __("Created"),
                result?.message ||
                    (isEdit
                        ? __("Saved successfully!")
                        : __("Created successfully!"))
            );

            LaraHooks.doAction(BuilderHooks.ACTION_AFTER_SAVE, result);

            // Redirect for new items
            if (!isEdit) {
                if (result?.id && listUrl) {
                    setTimeout(() => {
                        window.location.href = `${listUrl.replace(/\/$/, "")}/${
                            result.id
                        }/edit`;
                    }, 500);
                } else if (result?.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 500);
                }
            }
        } catch (error) {
            showToast(
                "error",
                __("Save Failed"),
                error.message || __("Failed to save")
            );
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

    // Context-specific labels
    const labels = useMemo(() => {
        const contextLabels = {
            email: {
                title: __("Email Builder"),
                backText: __("Back to Templates"),
                saveText: __("Save"),
            },
            page: {
                title: __("Page Builder"),
                backText: postTypeModel?.label
                    ? __("Back to :type").replace(":type", postTypeModel.label)
                    : __("Back to Posts"),
                saveText: postData?.id ? __("Update") : __("Publish"),
            },
            post: {
                title: __("Post Builder"),
                backText: postTypeModel?.label
                    ? __("Back to :type").replace(":type", postTypeModel.label)
                    : __("Back to Posts"),
                saveText: postData?.id ? __("Update") : __("Publish"),
            },
            campaign: {
                title: __("Campaign Editor"),
                backText: __("Back to Campaign"),
                saveText: __("Save"),
            },
        };

        return LaraHooks.applyFilters(
            `${BuilderHooks.FILTER_CONFIG}.${context}`,
            contextLabels[context] || contextLabels.email
        );
    }, [context, postTypeModel, postData]);

    // Determine which properties panel to use
    const ActivePropertiesPanel = PropertiesPanelComponent || PropertiesPanel;

    // Build properties panel props based on context
    const propertiesPanelProps = {
        selectedBlock,
        onUpdate: handleUpdateBlock,
        onImageUpload,
        onVideoUpload,
        canvasSettings,
        onCanvasSettingsUpdate: actions.updateCanvasSettings,
    };

    // Add context-specific props
    if (isEmailContext) {
        Object.assign(propertiesPanelProps, {
            templateName,
            setTemplateName,
            templateSubject,
            setTemplateSubject,
            context,
        });
    } else if (isPostContext) {
        Object.assign(propertiesPanelProps, {
            title,
            setTitle,
            slug,
            setSlug,
            generateSlug,
            status,
            setStatus,
            excerpt,
            setExcerpt,
            publishedAt,
            setPublishedAt,
            parentId,
            setParentId,
            selectedTerms,
            setSelectedTerms,
            featuredImage,
            setFeaturedImage,
            removeFeaturedImage,
            setRemoveFeaturedImage,
            taxonomies,
            parentPosts,
            postTypeModel,
            statuses,
            postData,
            postType,
        });
    }

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
                {showHeader && (
                    <header className="bg-white border-b border-gray-200 px-2 sm:px-4 py-2 sm:py-3 flex items-center justify-between shadow-sm flex-shrink-0">
                        <div className="flex items-center gap-2 sm:gap-4">
                            {listUrl && (
                                <a
                                    href={listUrl}
                                    onClick={(e) => {
                                        if (isFormDirty) {
                                            const confirmed = window.confirm(
                                                __(
                                                    "You have unsaved changes. Are you sure you want to leave?"
                                                )
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
                                        {labels.backText}
                                    </span>
                                </a>
                            )}
                            <div className="h-6 w-px bg-gray-300 hidden sm:block"></div>
                            <h1 className="text-sm sm:text-lg font-semibold text-gray-800">
                                {templateData?.uuid || postData?.id
                                    ? __("Edit")
                                    : __("Create")}
                                <span className="hidden sm:inline">
                                    {" "}
                                    {postTypeModel?.label_singular ||
                                        labels.title.split(" ")[0]}
                                </span>
                            </h1>

                            {/* History buttons */}
                            <div className="hidden sm:flex items-center gap-1 ml-2">
                                <button
                                    onClick={undo}
                                    disabled={!canUndo}
                                    className={`p-1.5 pb-0 rounded-md transition-colors ${
                                        canUndo
                                            ? "hover:bg-gray-100 text-gray-600"
                                            : "text-gray-300 cursor-not-allowed"
                                    }`}
                                    title={__("Undo (Ctrl+Z)")}
                                >
                                    <iconify-icon
                                        icon="mdi:undo"
                                        width="18"
                                        height="18"
                                    ></iconify-icon>
                                </button>
                                <button
                                    onClick={redo}
                                    disabled={!canRedo}
                                    className={`p-1.5 pb-0 rounded-md transition-colors ${
                                        canRedo
                                            ? "hover:bg-gray-100 text-gray-600"
                                            : "text-gray-300 cursor-not-allowed"
                                    }`}
                                    title={__("Redo (Ctrl+Shift+Z)")}
                                >
                                    <iconify-icon
                                        icon="mdi:redo"
                                        width="18"
                                        height="18"
                                    ></iconify-icon>
                                </button>
                            </div>

                            {isFormDirty && (
                                <span className="text-xs text-orange-600 bg-orange-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md font-medium">
                                    <span className="hidden sm:inline">
                                        {__("Unsaved changes")}
                                    </span>
                                    <span className="sm:hidden">*</span>
                                </span>
                            )}
                        </div>

                        <div className="flex items-center gap-2 sm:gap-4">
                            {/* Email context: Name input */}
                            {isEmailContext && (
                                <div className="hidden md:flex items-center gap-2">
                                    <label className="text-sm font-medium text-gray-600">
                                        {__("Name")}:
                                    </label>
                                    <input
                                        type="text"
                                        value={templateName}
                                        onChange={(e) =>
                                            setTemplateName(e.target.value)
                                        }
                                        placeholder={__("Template name...")}
                                        className="form-control"
                                    />
                                </div>
                            )}

                            {/* Email context: Subject input */}
                            {context === "email" && (
                                <div className="hidden lg:flex items-center gap-2">
                                    <label className="text-sm font-medium text-gray-600">
                                        {__("Subject")}:
                                    </label>
                                    <input
                                        type="text"
                                        value={templateSubject}
                                        onChange={(e) =>
                                            setTemplateSubject(e.target.value)
                                        }
                                        placeholder={__("Email subject...")}
                                        className="form-control"
                                    />
                                </div>
                            )}

                            {/* Post context: Title input */}
                            {isPostContext && (
                                <div className="hidden md:flex items-center gap-2">
                                    <input
                                        type="text"
                                        value={title}
                                        onChange={(e) =>
                                            setTitle(e.target.value)
                                        }
                                        placeholder={__(
                                            ":type title..."
                                        ).replace(
                                            ":type",
                                            postTypeModel?.label_singular ||
                                                "Post"
                                        )}
                                        className="form-control w-64"
                                    />
                                </div>
                            )}

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
                                        <span className="hidden sm:inline">
                                            {__("Saving...")}
                                        </span>
                                    </>
                                ) : (
                                    <>
                                        <iconify-icon
                                            icon="mdi:content-save"
                                            class="h-4 w-4"
                                        ></iconify-icon>
                                        <span className="hidden sm:inline">
                                            {labels.saveText}
                                        </span>
                                    </>
                                )}
                            </button>

                            {/* Editor Options Menu */}
                            <EditorOptionsMenu
                                editorMode={editorMode}
                                onEditorModeChange={handleEditorModeChange}
                                onCopyAllBlocks={handleCopyAllBlocks}
                                onPasteBlocks={handlePasteBlocks}
                            />
                        </div>
                    </header>
                )}

                {/* Main content */}
                <div className="flex-1 flex overflow-hidden relative">
                    {/* Mobile toggle buttons */}
                    <div className="lg:hidden fixed bottom-4 left-4 right-4 z-40 flex justify-between pointer-events-none">
                        <button
                            onClick={() => setLeftDrawerOpen(true)}
                            className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg shadow-lg hover:bg-primary/90 transition-colors"
                        >
                            <iconify-icon
                                icon="mdi:plus-box-multiple"
                                width="20"
                                height="20"
                            ></iconify-icon>
                            <span className="text-sm font-medium">
                                {__("Blocks")}
                            </span>
                        </button>
                        <button
                            onClick={() => setRightDrawerOpen(true)}
                            className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white rounded-lg shadow-lg hover:bg-gray-800 transition-colors"
                        >
                            <iconify-icon
                                icon="mdi:cog"
                                width="20"
                                height="20"
                            ></iconify-icon>
                            <span className="text-sm font-medium">
                                {__("Properties")}
                            </span>
                        </button>
                    </div>

                    {/* Left sidebar - Block palette (Desktop) */}
                    <div
                        className={`hidden lg:flex bg-white border-r border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${
                            leftSidebarCollapsed ? "w-12" : "w-64"
                        }`}
                    >
                        {leftSidebarCollapsed ? (
                            <div className="flex flex-col items-center py-4">
                                <button
                                    onClick={() =>
                                        setLeftSidebarCollapsed(false)
                                    }
                                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                                    title={__("Show Blocks")}
                                >
                                    <iconify-icon
                                        icon="mdi:chevron-right"
                                        width="20"
                                        height="20"
                                    ></iconify-icon>
                                </button>
                                <button
                                    onClick={() =>
                                        setLeftSidebarCollapsed(false)
                                    }
                                    className="mt-2 p-2 rounded-md hover:bg-primary/10 text-primary"
                                    title={__("Show Blocks")}
                                >
                                    <iconify-icon
                                        icon="mdi:plus-box-multiple"
                                        width="20"
                                        height="20"
                                    ></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div className="flex flex-col h-full p-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        {__("Blocks")}
                                    </h3>
                                    <button
                                        onClick={() =>
                                            setLeftSidebarCollapsed(true)
                                        }
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title={__("Hide Blocks")}
                                    >
                                        <iconify-icon
                                            icon="mdi:chevron-left"
                                            width="18"
                                            height="18"
                                        ></iconify-icon>
                                    </button>
                                </div>
                                <BlockPanel
                                    onAddBlock={handleAddBlock}
                                    context={context}
                                />
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
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        {__("Blocks")}
                                    </h3>
                                    <button
                                        onClick={() => setLeftDrawerOpen(false)}
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon
                                            icon="mdi:close"
                                            width="20"
                                            height="20"
                                        ></iconify-icon>
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

                    {/* Canvas or Code Editor based on mode */}
                    {editorMode === "visual" ? (
                        <div className="flex-1 flex flex-col overflow-hidden">
                            {/* Responsive Preview Toolbar */}
                            <div className="flex items-center justify-center gap-1 py-2 px-4 bg-gray-100 border-b border-gray-200">
                                <div className="flex items-center bg-white rounded-lg shadow-sm border border-gray-200 p-0.5">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setPreviewMode("desktop")
                                        }
                                        className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                                            previewMode === "desktop"
                                                ? "bg-primary text-white"
                                                : "text-gray-600 hover:bg-gray-100"
                                        }`}
                                        title={__("Desktop Preview")}
                                    >
                                        <iconify-icon
                                            icon="mdi:monitor"
                                            width="16"
                                            height="16"
                                        ></iconify-icon>
                                        <span className="hidden sm:inline">
                                            {__("Desktop")}
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setPreviewMode("tablet")}
                                        className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                                            previewMode === "tablet"
                                                ? "bg-primary text-white"
                                                : "text-gray-600 hover:bg-gray-100"
                                        }`}
                                        title={__("Tablet Preview")}
                                    >
                                        <iconify-icon
                                            icon="mdi:tablet"
                                            width="16"
                                            height="16"
                                        ></iconify-icon>
                                        <span className="hidden sm:inline">
                                            {__("Tablet")}
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setPreviewMode("mobile")}
                                        className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                                            previewMode === "mobile"
                                                ? "bg-primary text-white"
                                                : "text-gray-600 hover:bg-gray-100"
                                        }`}
                                        title={__("Mobile Preview")}
                                    >
                                        <iconify-icon
                                            icon="mdi:cellphone"
                                            width="16"
                                            height="16"
                                        ></iconify-icon>
                                        <span className="hidden sm:inline">
                                            {__("Mobile")}
                                        </span>
                                    </button>
                                </div>
                            </div>
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
                                onDuplicateNestedBlock={
                                    handleDuplicateNestedBlock
                                }
                                onInsertBlockAfter={handleInsertBlockAfter}
                                canvasSettings={canvasSettings}
                                previewMode={previewMode}
                            />
                        </div>
                    ) : (
                        <CodeEditor
                            html={codeEditorHtml}
                            onHtmlChange={handleCodeEditorHtmlChange}
                            canvasSettings={canvasSettings}
                            onExitCodeEditor={handleExitCodeEditor}
                        />
                    )}

                    {/* Right sidebar - Properties (Desktop) */}
                    <div
                        className={`hidden lg:flex bg-white border-l border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${
                            rightSidebarCollapsed ? "w-12" : "w-80"
                        }`}
                    >
                        {rightSidebarCollapsed ? (
                            <div className="flex flex-col items-center py-4">
                                <button
                                    onClick={() =>
                                        setRightSidebarCollapsed(false)
                                    }
                                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                                    title={__("Show Properties")}
                                >
                                    <iconify-icon
                                        icon="mdi:chevron-left"
                                        width="20"
                                        height="20"
                                    ></iconify-icon>
                                </button>
                                <button
                                    onClick={() =>
                                        setRightSidebarCollapsed(false)
                                    }
                                    className="mt-2 p-2 rounded-md hover:bg-gray-50 text-gray-600"
                                    title={__("Show Properties")}
                                >
                                    <iconify-icon
                                        icon="mdi:cog"
                                        width="20"
                                        height="20"
                                    ></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div className="flex flex-col h-full pt-4 pr-4 pb-4 pl-2 overflow-hidden">
                                <div className="flex items-center justify-between mb-4 pl-2">
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        {__("Properties")}
                                    </h3>
                                    <button
                                        onClick={() =>
                                            setRightSidebarCollapsed(true)
                                        }
                                        className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                                        title={__("Hide Properties")}
                                    >
                                        <iconify-icon
                                            icon="mdi:chevron-right"
                                            width="18"
                                            height="18"
                                        ></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto pl-2">
                                    <ActivePropertiesPanel
                                        {...propertiesPanelProps}
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
                                    <h3 className="text-sm font-semibold text-gray-900">
                                        {__("Properties")}
                                    </h3>
                                    <button
                                        onClick={() =>
                                            setRightDrawerOpen(false)
                                        }
                                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                                    >
                                        <iconify-icon
                                            icon="mdi:close"
                                            width="20"
                                            height="20"
                                        ></iconify-icon>
                                    </button>
                                </div>
                                <div className="flex-1 px-4 py-4 overflow-y-auto">
                                    {/* Mobile-only template inputs for email context */}
                                    {isEmailContext && (
                                        <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                                            <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                                {__("Template Details")}
                                            </h4>
                                            <div className="space-y-3">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                                        {__("Name")}
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={templateName}
                                                        onChange={(e) =>
                                                            setTemplateName(
                                                                e.target.value
                                                            )
                                                        }
                                                        placeholder={__(
                                                            "Template name..."
                                                        )}
                                                        className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                                    />
                                                </div>
                                                {context === "email" && (
                                                    <div>
                                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                                            {__("Subject")}
                                                        </label>
                                                        <input
                                                            type="text"
                                                            value={
                                                                templateSubject
                                                            }
                                                            onChange={(e) =>
                                                                setTemplateSubject(
                                                                    e.target
                                                                        .value
                                                                )
                                                            }
                                                            placeholder={__(
                                                                "Email subject..."
                                                            )}
                                                            className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                    {/* Mobile-only title input for post context */}
                                    {isPostContext && (
                                        <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                {__("Title")}
                                            </label>
                                            <input
                                                type="text"
                                                value={title}
                                                onChange={(e) =>
                                                    setTitle(e.target.value)
                                                }
                                                placeholder={__(
                                                    ":type title..."
                                                ).replace(
                                                    ":type",
                                                    postTypeModel?.label_singular ||
                                                        "Post"
                                                )}
                                                className="form-control w-full"
                                            />
                                        </div>
                                    )}
                                    <ActivePropertiesPanel
                                        {...propertiesPanelProps}
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
                            {
                                blockRegistry.get(
                                    activeId.replace("palette-", "")
                                )?.label
                            }
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
 * LaraBuilder - Main exported component
 */
function LaraBuilder({
    context = "email",
    initialData = null,
    onSave,
    onImageUpload,
    onVideoUpload,
    listUrl,
    config = {},
    showHeader = true,
    // Email-specific props
    templateData,
    // Post-specific props
    postData = null,
    taxonomies = [],
    selectedTerms = {},
    parentPosts = {},
    postType = "post",
    postTypeModel = {},
    statuses = {},
    // Custom properties panel component
    PropertiesPanelComponent,
}) {
    // Fire init action
    useEffect(() => {
        LaraHooks.doAction(BuilderHooks.ACTION_INIT, { context, initialData });
    }, []);

    return (
        <BuilderProvider
            context={context}
            initialData={initialData}
            config={config}
        >
            <LaraBuilderInner
                onSave={onSave}
                onImageUpload={onImageUpload}
                onVideoUpload={onVideoUpload}
                listUrl={listUrl}
                showHeader={showHeader}
                templateData={templateData}
                postData={postData}
                taxonomies={taxonomies}
                selectedTerms={selectedTerms}
                parentPosts={parentPosts}
                postType={postType}
                postTypeModel={postTypeModel}
                statuses={statuses}
                PropertiesPanelComponent={PropertiesPanelComponent}
            />
        </BuilderProvider>
    );
}

export default LaraBuilder;
export { LaraBuilder, LaraBuilderInner };
