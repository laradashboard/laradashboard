/**
 * BuilderHeader - Header component for LaraBuilder
 *
 * Displays the builder header with back button, title, history buttons,
 * context-specific inputs, save button, and editor options menu.
 */

import { __ } from "@lara-builder/i18n";
import EditorOptionsMenu from "./EditorOptionsMenu";

function BuilderHeader({
    // Navigation
    listUrl,
    isFormDirty,
    labels,
    // Context info
    isPostContext,
    templateData,
    postData,
    postTypeModel,
    // History
    canUndo,
    canRedo,
    undo,
    redo,
    // Post state
    title,
    setTitle,
    // Save
    saving,
    onSave,
    // Editor mode
    editorMode,
    onEditorModeChange,
    onCopyAllBlocks,
    onPasteBlocks,
}) {
    return (
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
                    {templateData?.uuid || postData?.id ? __("Edit") : __("Create")}
                    <span className="hidden sm:inline">
                        {" "}
                        {postTypeModel?.label_singular || labels.title.split(" ")[0]}
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
                        <iconify-icon icon="mdi:undo" width="18" height="18"></iconify-icon>
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
                        <iconify-icon icon="mdi:redo" width="18" height="18"></iconify-icon>
                    </button>
                </div>

                {isFormDirty && (
                    <span className="text-xs text-orange-600 bg-orange-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md font-medium">
                        <span className="hidden sm:inline">{__("Unsaved changes")}</span>
                        <span className="sm:hidden">*</span>
                    </span>
                )}
            </div>

            <div className="flex items-center gap-2 sm:gap-4">
                {/* Post context: Title input */}
                {isPostContext && (
                    <div className="hidden md:flex items-center gap-2">
                        <input
                            type="text"
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                            placeholder={__(":type title...").replace(
                                ":type",
                                postTypeModel?.label_singular || "Post"
                            )}
                            className="form-control w-64"
                        />
                    </div>
                )}

                {/* Save button */}
                <button
                    onClick={onSave}
                    disabled={saving}
                    className={`gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 ${
                        saving ? "btn-default cursor-not-allowed" : "btn-primary"
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
                            <span className="hidden sm:inline">{__("Saving...")}</span>
                        </>
                    ) : (
                        <>
                            <iconify-icon
                                icon="mdi:content-save"
                                class="h-4 w-4"
                            ></iconify-icon>
                            <span className="hidden sm:inline">{labels.saveText}</span>
                        </>
                    )}
                </button>

                {/* Editor Options Menu */}
                <EditorOptionsMenu
                    editorMode={editorMode}
                    onEditorModeChange={onEditorModeChange}
                    onCopyAllBlocks={onCopyAllBlocks}
                    onPasteBlocks={onPasteBlocks}
                />
            </div>
        </header>
    );
}

export default BuilderHeader;
