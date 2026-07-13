/**
 * LaraBuilder - Unified Visual Builder Component
 *
 * A reusable, extensible visual builder for posts, pages, emails, and custom content.
 * Supports multiple contexts with different block sets, property panels, and output formats.
 *
 * @example
 * // Post builder (default)
 * <LaraBuilder
 *   context="post"
 *   initialData={data}
 *   onSave={handleSave}
 *   postData={{ id: 1, title: 'My Post' }}
 * />
 *
 * // Email builder
 * <LaraBuilder
 *   context="email"
 *   initialData={data}
 *   onSave={handleSave}
 *   templateData={{ name: 'My Template', subject: 'Hello' }}
 * />
 *
 * // Page builder
 * <LaraBuilder
 *   context="page"
 *   initialData={data}
 *   onSave={handleSave}
 *   postData={postData}
 *   taxonomies={taxonomies}
 *   PropertiesPanelComponent={PostPropertiesPanel}
 * />
 */

import { useState, useCallback, useEffect, useMemo, useRef } from "react";
import {
    DndContext,
    DragOverlay,
    PointerSensor,
    useSensor,
    useSensors,
} from "@dnd-kit/core";

import { BuilderProvider, useBuilder } from "./BuilderContext";
import { useHistory } from "./hooks/useHistory";
import { useBlocks } from "./hooks/useBlocks";
import { useEmailState } from "./hooks/useEmailState";
import { usePostState } from "./hooks/usePostState";
import { useBlockOperations } from "./hooks/useBlockOperations";
import { useDragAndDrop } from "./hooks/useDragAndDrop";
import { useAutoSave, shouldAutoSavePost } from "./hooks/useAutoSave";
import { LaraHooks } from "../hooks-system/LaraHooks";
import { BuilderHooks } from "../hooks-system/HookNames";
import { blockRegistry } from "../registry/BlockRegistry";
import { __ } from "@lara-builder/i18n";

// Import components
import Canvas from "../components/Canvas";
import PropertiesPanel from "../components/PropertiesPanel";
import Toast from "../components/Toast";
import CodeEditor from "../components/CodeEditor";
import BuilderHeader from "../components/BuilderHeader";
import {
    LeftSidebar,
    RightSidebar,
    LeftDrawer,
    RightDrawer,
    MobileToggleButtons,
} from "../components/BuilderSidebars";

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
    const stateRef = useRef(state);
    stateRef.current = state;

    // Enable keyboard shortcuts for history
    useHistory({ enableKeyboardShortcuts: true });

    // Use blocks hook for add block functionality
    const { addBlockAfterSelected } = useBlocks();

    // Context checks
    const isEmailContext = context === "email" || context === "campaign";
    const isPostContext = context === "page" || context === "post";

    // Email state management
    const {
        templateName,
        templateSubject,
        templateStatus,
        templateDirty,
        setTemplateName,
        setTemplateSubject,
        setTemplateStatus,
        markEmailSaved,
    } = useEmailState({ templateData, isEmailContext });

    // Post state management
    const {
        title,
        slug,
        status,
        excerpt,
        publishedAt,
        parentId,
        selectedTerms,
        featuredImage,
        featuredImageId,
        removeFeaturedImage,
        postDirty,
        seoTitle,
        seoDescription,
        seoKeywords,
        seoOgTitle,
        seoOgDescription,
        seoCanonical,
        seoNoindex,
        seoNofollow,
        seoSchemaType,
        setTitle,
        setSlug,
        setStatus,
        setExcerpt,
        setPublishedAt,
        setParentId,
        setSelectedTerms,
        setFeaturedImage,
        setFeaturedImageId,
        setRemoveFeaturedImage,
        setSeoTitle,
        setSeoDescription,
        setSeoKeywords,
        setSeoOgTitle,
        setSeoOgDescription,
        setSeoCanonical,
        setSeoNoindex,
        setSeoNofollow,
        setSeoSchemaType,
        generateSlug,
        markPostSaved,
    } = usePostState({ postData, initialSelectedTerms, isPostContext });

    // Block operations
    const {
        findBlock,
        findBlockLocation,
        handleUpdateBlock,
        handleDeleteBlock,
        handleDeleteNestedBlock,
        handleMoveBlock,
        handleMoveNestedBlock,
        handleDuplicateBlock,
        handleDuplicateNestedBlock,
        handleAddBlock,
        handleInsertBlockAfter,
        handleReplaceBlock,
        handleMergeBlockWithPrevious,
    } = useBlockOperations({ blocks, actions, addBlockAfterSelected });

    // Drag and drop
    const {
        activeId,
        handleDragStart,
        handleDragEnd,
        customCollisionDetection,
    } = useDragAndDrop({ blocks, actions });

    // DnD sensors
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        })
    );

    const selectedBlock = findBlock(selectedBlockId);

    // ========================
    // Local UI state
    // ========================
    const [saving, setSaving] = useState(false);
    const [lastSavedAt, setLastSavedAt] = useState(null);
    const [currentPostId, setCurrentPostId] = useState(postData?.id ?? null);
    const [toast, setToast] = useState(null);
    const [allBlocksSelected, setAllBlocksSelected] = useState(false);

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

    // Validation refs for focusing invalid fields
    const headerTitleRef = useRef(null);
    const sidebarTitleRef = useRef(null);
    const headerTemplateNameRef = useRef(null);
    const [titleError, setTitleError] = useState(null);
    const [templateNameError, setTemplateNameError] = useState(null);
    const suppressBeforeUnloadRef = useRef(false);

    // Show toast helper
    const showToast = useCallback((variant, titleText, message) => {
        setToast({ variant, title: titleText, message });
    }, []);

    // Combined dirty state
    const isFormDirty = isDirty || templateDirty || postDirty;

    // Warn user before leaving with unsaved changes
    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (suppressBeforeUnloadRef.current || !isFormDirty) {
                return undefined;
            }

            e.preventDefault();
            e.returnValue = "";
            return "";
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () =>
            window.removeEventListener("beforeunload", handleBeforeUnload);
    }, [isFormDirty]);

    // Clear allBlocksSelected when selection changes or blocks change
    useEffect(() => {
        setAllBlocksSelected(false);
    }, [selectedBlockId, blocks.length]);

    // Keyboard shortcuts for block operations
    useEffect(() => {
        const handleKeyDown = (e) => {
            // Check if user is typing in an input/contenteditable
            const activeElement = document.activeElement;
            const isEditing =
                activeElement?.tagName === "INPUT" ||
                activeElement?.tagName === "TEXTAREA" ||
                activeElement?.isContentEditable ||
                activeElement?.closest('[contenteditable="true"]') ||
                activeElement?.closest(".ProseMirror") ||
                activeElement?.closest(".ql-editor") ||
                activeElement?.closest('[data-text-editing="true"]');

            // Ctrl/Cmd + A: Select all blocks
            if ((e.ctrlKey || e.metaKey) && e.key === "a") {
                // If user is editing text, first Ctrl+A selects text (browser default).
                // Second Ctrl+A (when text is already fully selected) selects all blocks.
                if (isEditing) {
                    const selection = window.getSelection();
                    const editableEl = activeElement?.closest('[contenteditable="true"]') || activeElement;

                    // Check if all text in the editable element is already selected
                    if (selection && editableEl) {
                        const range = document.createRange();
                        range.selectNodeContents(editableEl);
                        const currentRange = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;

                        const isAllSelected = currentRange &&
                            currentRange.toString().length >= editableEl.textContent.trim().length &&
                            editableEl.textContent.trim().length > 0;

                        if (isAllSelected) {
                            // Text already fully selected — escalate to select all blocks
                            e.preventDefault();
                            editableEl.blur();
                            setAllBlocksSelected(true);
                            return;
                        }
                    }
                    // Let browser handle first Ctrl+A (select text within block)
                    return;
                }

                // Not editing — select all blocks
                if (selectedBlockId) {
                    e.preventDefault();
                    setAllBlocksSelected(true);
                }
                return;
            }

            // Delete/Backspace when all blocks are selected — clear all
            if (allBlocksSelected && (e.key === "Backspace" || e.key === "Delete")) {
                e.preventDefault();
                actions.setBlocks([]);
                setAllBlocksSelected(false);
                return;
            }

            // Any other key clears the all-blocks-selected state
            if (allBlocksSelected && !e.ctrlKey && !e.metaKey && !e.altKey) {
                setAllBlocksSelected(false);
            }

            if (!selectedBlockId) return;
            if (isEditing) return;

            // Find block location (works at any nesting depth)
            const location = findBlockLocation(selectedBlockId);
            const isNested = location?.isNested || false;
            const parentBlockId = location?.parentBlockId || null;
            const columnIndex = location?.columnIndex ?? null;
            const blockIndex = location?.blockIndex ?? -1;

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
    }, [selectedBlockId, blocks, actions, allBlocksSelected, findBlockLocation]);

    // Editor mode handlers
    const handleEditorModeChange = useCallback(
        (mode) => {
            if (mode === "code" && editorMode === "visual") {
                const html = getHtml();
                setCodeEditorHtml(html);
            } else if (mode === "visual" && editorMode === "code") {
                if (!codeEditorHtml.trim()) {
                    actions.setBlocks([]);
                }
            }
            setEditorMode(mode);
        },
        [editorMode, getHtml, codeEditorHtml, actions]
    );

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

    // Insert AI-generated content as blocks
    const handleInsertAIContent = useCallback(
        (blocksToInsert) => {
            if (!Array.isArray(blocksToInsert) || blocksToInsert.length === 0) {
                return;
            }

            blocksToInsert.forEach((block) => {
                if (block) {
                    actions.addBlock(block);
                }
            });

            showToast(
                "success",
                __("AI Content Inserted"),
                __(":count blocks added").replace(":count", blocksToInsert.length)
            );
        },
        [actions, showToast]
    );

    const handleCodeEditorHtmlChange = useCallback((html) => {
        setCodeEditorHtml(html);
    }, []);

    const focusTitleField = useCallback(() => {
        actions.selectBlock(null);

        const useSidebarTitle = window.matchMedia("(max-width: 767px)").matches;

        if (useSidebarTitle) {
            setRightDrawerOpen(true);
            window.setTimeout(() => {
                sidebarTitleRef.current?.focus();
                sidebarTitleRef.current?.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }, 200);
            return;
        }

        headerTitleRef.current?.focus();
        headerTitleRef.current?.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });
    }, [actions]);

    const focusTemplateNameField = useCallback(() => {
        headerTemplateNameRef.current?.focus();
        headerTemplateNameRef.current?.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });
    }, []);

    const hasAutoFocusedTitleRef = useRef(false);

    useEffect(() => {
        if (
            hasAutoFocusedTitleRef.current ||
            !isPostContext ||
            postData?.id ||
            currentPostId
        ) {
            return undefined;
        }

        hasAutoFocusedTitleRef.current = true;

        const timer = window.setTimeout(() => {
            focusTitleField();
        }, 150);

        return () => window.clearTimeout(timer);
    }, [isPostContext, postData?.id, currentPostId, focusTitleField]);

    useEffect(() => {
        if (titleError && title.trim()) {
            setTitleError(null);
        }
    }, [title, titleError]);

    useEffect(() => {
        if (templateNameError && templateName.trim()) {
            setTemplateNameError(null);
        }
    }, [templateName, templateNameError]);

    // Save handler - accepts optional statusOverride for header button actions
    const handleSave = useCallback(
        async (statusOverride, options = {}) => {
            const { silent = false, autoSave = false, preferInPlaceNavigation = false } =
                options;
            const stayInPlace = autoSave || preferInPlaceNavigation;

            // Context-specific validation
            if (isEmailContext && !templateName.trim()) {
                const message = __("Template name is required");
                setTemplateNameError(message);
                focusTemplateNameField();
                if (!silent) {
                    showToast("error", __("Validation Error"), message);
                }
                return;
            }
            if (isPostContext && !title.trim()) {
                if (!silent) {
                    const message = __("Title is required");
                    setTitleError(message);
                    focusTitleField();
                    showToast("error", __("Validation Error"), message);
                }
                return;
            }

            setTitleError(null);
            setTemplateNameError(null);

            // Apply status override for this save without mutating UI early.
            const effectiveStatus =
                isPostContext && statusOverride ? statusOverride : status;

            LaraHooks.doAction(BuilderHooks.ACTION_BEFORE_SAVE, stateRef.current);

            const wasEdit = !!(templateData?.uuid || currentPostId);

            setSaving(true);

            try {
                const html = editorMode === "code" ? codeEditorHtml : getHtml();
                const designJson = getSaveData();

                let saveData = {};

                if (isEmailContext) {
                    saveData = {
                        name: templateName,
                        subject: templateSubject || templateName,
                        is_active: templateStatus,
                        body_html: html,
                        design_json: designJson,
                    };
                } else if (isPostContext) {
                    const taxonomyData = {};
                    Object.entries(selectedTerms).forEach(
                        ([taxonomyName, termIds]) => {
                            taxonomyData[`taxonomy_${taxonomyName}`] = termIds;
                        }
                    );

                    saveData = {
                        title,
                        slug: slug || undefined,
                        status: effectiveStatus,
                        excerpt,
                        content: html,
                        design_json: designJson,
                        published_at:
                            effectiveStatus === "scheduled"
                                ? publishedAt
                                : undefined,
                        parent_id: parentId || undefined,
                        featured_image: featuredImage || undefined,
                        featured_image_id: featuredImageId || undefined,
                        remove_featured_image: removeFeaturedImage,
                        seo_title: seoTitle,
                        seo_description: seoDescription,
                        seo_keywords: seoKeywords,
                        seo_og_title: seoOgTitle,
                        seo_og_description: seoOgDescription,
                        seo_canonical: seoCanonical,
                        seo_noindex: seoNoindex,
                        seo_nofollow: seoNofollow,
                        seo_schema_type: seoSchemaType,
                        ...taxonomyData,
                    };
                }

                const result = await onSave(saveData, {
                    postId: currentPostId,
                    autoSave,
                });

                actions.markSaved();

                if (isEmailContext) {
                    markEmailSaved();
                } else if (isPostContext) {
                    const savedFeaturedImage =
                        result?.featured_image_url || featuredImage;
                    const savedFeaturedImageId = result?.featured_image_id
                        ? String(result.featured_image_id)
                        : featuredImageId;

                    if (result?.featured_image_url) {
                        setFeaturedImage(savedFeaturedImage);
                    }

                    if (result?.featured_image_id) {
                        setFeaturedImageId(savedFeaturedImageId);
                    }

                    if (effectiveStatus !== status) {
                        setStatus(effectiveStatus);
                    }

                    markPostSaved({
                        status: effectiveStatus,
                        featuredImage: savedFeaturedImage,
                        featuredImageId: savedFeaturedImageId,
                    });
                }

                setLastSavedAt(new Date());

                if (isPostContext && result?.id && !currentPostId) {
                    setCurrentPostId(result.id);

                    if (listUrl) {
                        suppressBeforeUnloadRef.current = true;
                        window.history.replaceState(
                            null,
                            "",
                            result.redirect ||
                                `${listUrl.replace(/\/$/, "")}/${result.id}/edit`
                        );
                    }
                }

                if (!silent) {
                    const draftSaved =
                        isPostContext && effectiveStatus === "draft";
                    showToast(
                        "success",
                        draftSaved
                            ? __("Draft saved")
                            : wasEdit || result?.id
                              ? __("Saved")
                              : __("Created"),
                        result?.message ||
                            (draftSaved
                                ? __("Your draft has been saved.")
                                : wasEdit || result?.id
                                  ? __("Saved successfully!")
                                  : __("Created successfully!"))
                    );
                }

                LaraHooks.doAction(BuilderHooks.ACTION_AFTER_SAVE, result);

                if (!wasEdit && !stayInPlace && !isPostContext) {
                    if (result?.id && listUrl) {
                        suppressBeforeUnloadRef.current = true;
                        setTimeout(() => {
                            window.location.href = `${listUrl.replace(/\/$/, "")}/${
                                result.id
                            }/edit`;
                        }, 500);
                    } else if (result?.redirect) {
                        suppressBeforeUnloadRef.current = true;
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 500);
                    }
                }
            } catch (error) {
                if (!silent) {
                    showToast(
                        "error",
                        __("Save Failed"),
                        error.message || __("Failed to save")
                    );
                }
                LaraHooks.doAction(BuilderHooks.ACTION_SAVE_ERROR, error);
                throw error;
            } finally {
                setSaving(false);
            }
        },
        [
            isEmailContext,
            isPostContext,
            templateName,
            title,
            status,
            editorMode,
            codeEditorHtml,
            getHtml,
            getSaveData,
            templateSubject,
            templateStatus,
            selectedTerms,
            slug,
            excerpt,
            publishedAt,
            parentId,
            featuredImage,
            featuredImageId,
            removeFeaturedImage,
            seoTitle,
            seoDescription,
            seoKeywords,
            seoOgTitle,
            seoOgDescription,
            seoCanonical,
            seoNoindex,
            seoNofollow,
            seoSchemaType,
            onSave,
            currentPostId,
            actions,
            markEmailSaved,
            markPostSaved,
            showToast,
            templateData?.uuid,
            listUrl,
            focusTemplateNameField,
            focusTitleField,
            setStatus,
        ]
    );

    const handleAutoSave = useCallback(async () => {
        await handleSave(undefined, { silent: true, autoSave: true });
    }, [handleSave]);

    useAutoSave({
        enabled: isPostContext && shouldAutoSavePost(status),
        isDirty: isFormDirty,
        canSave: Boolean(title.trim()),
        isSaving: saving,
        onAutoSave: handleAutoSave,
    });

    const builderPostData = useMemo(
        () =>
            isPostContext && currentPostId
                ? { ...(postData || {}), id: currentPostId }
                : postData,
        [isPostContext, currentPostId, postData]
    );

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

    const { contentLength, blockCount } = useMemo(() => {
        let count = 0;
        let textLength = (title || "").length + (excerpt || "").length;

        const walkBlocks = (items) => {
            if (!Array.isArray(items)) return;

            for (const item of items) {
                if (Array.isArray(item)) {
                    walkBlocks(item);
                    continue;
                }

                if (!item || typeof item !== "object" || !item.type) {
                    continue;
                }

                count++;

                const props = item.props || {};
                for (const key of ["content", "text", "html"]) {
                    if (typeof props[key] === "string") {
                        textLength += props[key].replace(/[<>]/g, "").length;
                    }
                }

                if (Array.isArray(props.children)) {
                    walkBlocks(props.children);
                }
            }
        };

        walkBlocks(blocks);

        return { contentLength: textLength, blockCount: count };
    }, [blocks, title, excerpt]);

    // Determine which properties panel to use
    const ActivePropertiesPanel = PropertiesPanelComponent || PropertiesPanel;

    // Build properties panel props based on context
    const propertiesPanelProps = {
        selectedBlock,
        onUpdate: handleUpdateBlock,
        onReplaceBlock: handleReplaceBlock,
        onImageUpload,
        onVideoUpload,
        canvasSettings,
        onCanvasSettingsUpdate: actions.updateCanvasSettings,
    };

    if (isEmailContext) {
        Object.assign(propertiesPanelProps, {
            templateName,
            setTemplateName,
            templateSubject,
            setTemplateSubject,
            templateStatus,
            setTemplateStatus,
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
            featuredImageId,
            setFeaturedImage,
            setFeaturedImageId,
            removeFeaturedImage,
            setRemoveFeaturedImage,
            taxonomies,
            parentPosts,
            postTypeModel,
            statuses,
            postData,
            postType,
            titleInputRef: sidebarTitleRef,
            titleError,
            lastSavedAt,
            saving,
            isFormDirty,
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
                {/* Header - click to deselect blocks */}
                {showHeader && (
                    <div onClick={() => actions.selectBlock(null)}>
                        <BuilderHeader
                            listUrl={listUrl}
                            isFormDirty={isFormDirty}
                            labels={labels}
                            isPostContext={isPostContext}
                            isEmailContext={isEmailContext}
                            templateData={templateData}
                            postData={builderPostData}
                            postTypeModel={postTypeModel}
                            canUndo={canUndo}
                            canRedo={canRedo}
                            undo={undo}
                            redo={redo}
                            title={title}
                            setTitle={setTitle}
                            titleInputRef={headerTitleRef}
                            titleError={titleError}
                            excerpt={excerpt}
                            setExcerpt={setExcerpt}
                            templateName={templateName}
                            setTemplateName={setTemplateName}
                            templateNameInputRef={headerTemplateNameRef}
                            templateNameError={templateNameError}
                            saving={saving}
                            onSave={handleSave}
                            editorMode={editorMode}
                            onEditorModeChange={handleEditorModeChange}
                            onCopyAllBlocks={handleCopyAllBlocks}
                            onPasteBlocks={handlePasteBlocks}
                            onInsertAIContent={handleInsertAIContent}
                            previewMode={previewMode}
                            setPreviewMode={setPreviewMode}
                            status={status}
                            slug={slug}
                            seoTitle={seoTitle}
                            setSeoTitle={setSeoTitle}
                            seoDescription={seoDescription}
                            setSeoDescription={setSeoDescription}
                            seoKeywords={seoKeywords}
                            setSeoKeywords={setSeoKeywords}
                            seoOgTitle={seoOgTitle}
                            setSeoOgTitle={setSeoOgTitle}
                            seoOgDescription={seoOgDescription}
                            setSeoOgDescription={setSeoOgDescription}
                            seoCanonical={seoCanonical}
                            setSeoCanonical={setSeoCanonical}
                            seoNoindex={seoNoindex}
                            setSeoNoindex={setSeoNoindex}
                            seoNofollow={seoNofollow}
                            setSeoNofollow={setSeoNofollow}
                            seoSchemaType={seoSchemaType}
                            setSeoSchemaType={setSeoSchemaType}
                            contentHtml={
                                editorMode === "code" ? codeEditorHtml : getHtml()
                            }
                            postType={postType}
                            contentLength={contentLength}
                            blockCount={blockCount}
                            showToast={showToast}
                            onFocusTitle={focusTitleField}
                            featuredImage={featuredImage}
                            removeFeaturedImage={removeFeaturedImage}
                        />
                    </div>
                )}

                {/* Main content */}
                <div className="flex-1 flex overflow-hidden relative">
                    {/* Mobile toggle buttons */}
                    <MobileToggleButtons
                        onOpenLeftDrawer={() => setLeftDrawerOpen(true)}
                        onOpenRightDrawer={() => setRightDrawerOpen(true)}
                    />

                    {/* Left sidebar - Block palette (Desktop) - click to deselect blocks */}
                    <div
                        className={`hidden lg:flex bg-white border-r border-gray-200 overflow-hidden flex-col flex-shrink-0 transition-all duration-200 ${
                            leftSidebarCollapsed ? "w-12" : "w-64"
                        }`}
                        onClick={() => actions.selectBlock(null)}
                    >
                        <LeftSidebar
                            collapsed={leftSidebarCollapsed}
                            setCollapsed={setLeftSidebarCollapsed}
                            onAddBlock={handleAddBlock}
                            context={context}
                        />
                    </div>

                    {/* Left Drawer - Mobile */}
                    <LeftDrawer
                        isOpen={leftDrawerOpen}
                        onClose={() => setLeftDrawerOpen(false)}
                        onAddBlock={handleAddBlock}
                        context={context}
                    />

                    {/* Canvas or Code Editor based on mode */}
                    {editorMode === "visual" ? (
                        <div className="flex-1 flex flex-col overflow-hidden">
                            <Canvas
                                blocks={blocks}
                                selectedBlockId={selectedBlockId}
                                allBlocksSelected={allBlocksSelected}
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
                                onReplaceBlock={handleReplaceBlock}
                                onMergeBlockWithPrevious={handleMergeBlockWithPrevious}
                                canvasSettings={canvasSettings}
                                previewMode={previewMode}
                                context={context}
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
                        <RightSidebar
                            collapsed={rightSidebarCollapsed}
                            setCollapsed={setRightSidebarCollapsed}
                            PropertiesPanel={ActivePropertiesPanel}
                            propertiesPanelProps={propertiesPanelProps}
                        />
                    </div>

                    {/* Right Drawer - Mobile */}
                    <RightDrawer
                        isOpen={rightDrawerOpen}
                        onClose={() => setRightDrawerOpen(false)}
                        context={context}
                        isEmailContext={isEmailContext}
                        isPostContext={isPostContext}
                        templateName={templateName}
                        setTemplateName={setTemplateName}
                        templateSubject={templateSubject}
                        setTemplateSubject={setTemplateSubject}
                        title={title}
                        setTitle={setTitle}
                        postTypeModel={postTypeModel}
                        PropertiesPanel={ActivePropertiesPanel}
                        propertiesPanelProps={propertiesPanelProps}
                    />
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
    context = "post",
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
    const isNewDocument = !postData?.id && !templateData?.uuid;
    const isPostLikeContext = context === "page" || context === "post";
    const autoSelectFirstBlock = isNewDocument && !isPostLikeContext;

    // Fire init action
    useEffect(() => {
        LaraHooks.doAction(BuilderHooks.ACTION_INIT, { context, initialData });
    }, []);

    return (
        <BuilderProvider
            context={context}
            initialData={initialData}
            config={{ ...config, autoSelectFirstBlock }}
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
