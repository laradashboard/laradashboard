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

import { useState, useCallback, useEffect, useMemo } from "react";
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
        removeFeaturedImage,
        postDirty,
        setTitle,
        setSlug,
        setStatus,
        setExcerpt,
        setPublishedAt,
        setParentId,
        setSelectedTerms,
        setFeaturedImage,
        setRemoveFeaturedImage,
        generateSlug,
        markPostSaved,
    } = usePostState({ postData, initialSelectedTerms, isPostContext });

    // Block operations
    const {
        findBlock,
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
    const [toast, setToast] = useState(null);

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
            const html = editorMode === "code" ? codeEditorHtml : getHtml();
            const designJson = getSaveData();

            let saveData = {};

            if (isEmailContext) {
                saveData = {
                    name: templateName,
                    // Use template name as subject if subject is empty
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

            if (isEmailContext) {
                markEmailSaved();
            } else if (isPostContext) {
                markPostSaved();
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
                            postData={postData}
                            postTypeModel={postTypeModel}
                            canUndo={canUndo}
                            canRedo={canRedo}
                            undo={undo}
                            redo={redo}
                            title={title}
                            setTitle={setTitle}
                            excerpt={excerpt}
                            setExcerpt={setExcerpt}
                            templateName={templateName}
                            setTemplateName={setTemplateName}
                            saving={saving}
                            onSave={handleSave}
                            editorMode={editorMode}
                            onEditorModeChange={handleEditorModeChange}
                            onCopyAllBlocks={handleCopyAllBlocks}
                            onPasteBlocks={handlePasteBlocks}
                            onInsertAIContent={handleInsertAIContent}
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
                            {/* Responsive Preview Toolbar - click to deselect blocks */}
                            <div
                                className="flex items-center justify-center gap-1 py-2 px-4 bg-gray-100 border-b border-gray-200"
                                onClick={() => actions.selectBlock(null)}
                            >
                                <div className="flex items-center bg-white rounded-lg shadow-sm border border-gray-200 p-0.5">
                                    {["desktop", "tablet", "mobile"].map(
                                        (mode) => (
                                            <button
                                                key={mode}
                                                type="button"
                                                onClick={() =>
                                                    setPreviewMode(mode)
                                                }
                                                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                                                    previewMode === mode
                                                        ? "bg-primary text-white"
                                                        : "text-gray-600 hover:bg-gray-100"
                                                }`}
                                                title={__(
                                                    `${
                                                        mode
                                                            .charAt(0)
                                                            .toUpperCase() +
                                                        mode.slice(1)
                                                    } Preview`
                                                )}
                                            >
                                                <iconify-icon
                                                    icon={`mdi:${
                                                        mode === "desktop"
                                                            ? "monitor"
                                                            : mode === "tablet"
                                                            ? "tablet"
                                                            : "cellphone"
                                                    }`}
                                                    width="16"
                                                    height="16"
                                                ></iconify-icon>
                                                <span className="hidden sm:inline">
                                                    {__(
                                                        mode
                                                            .charAt(0)
                                                            .toUpperCase() +
                                                            mode.slice(1)
                                                    )}
                                                </span>
                                            </button>
                                        )
                                    )}
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
                                onReplaceBlock={handleReplaceBlock}
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
