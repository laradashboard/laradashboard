/**
 * EmailPropertiesPanel - Properties panel for email template editing
 *
 * Shows email-specific fields when no block is selected, or
 * reuses the shared PropertiesPanel block editors when a block is selected.
 */

import PropertiesPanel from './PropertiesPanel';
import LayoutStylesSection from './LayoutStylesSection';
import { __ } from '@lara-builder/i18n';

const EmailPropertiesPanel = ({
    selectedBlock,
    onUpdate,
    onImageUpload,
    onVideoUpload,
    canvasSettings,
    onCanvasSettingsUpdate,
    // Email-specific props
    templateName,
    setTemplateName,
    templateSubject,
    setTemplateSubject,
    templateStatus,
    setTemplateStatus,
    context,
}) => {
    // Handle canvas layout styles update
    const handleCanvasLayoutStylesUpdate = (newLayoutStyles) => {
        onCanvasSettingsUpdate({
            ...canvasSettings,
            layoutStyles: newLayoutStyles,
        });
    };

    // If a block is selected, delegate to the shared PropertiesPanel for block editing
    if (selectedBlock) {
        return (
            <PropertiesPanel
                selectedBlock={selectedBlock}
                onUpdate={onUpdate}
                onImageUpload={onImageUpload}
                onVideoUpload={onVideoUpload}
                canvasSettings={canvasSettings}
                onCanvasSettingsUpdate={onCanvasSettingsUpdate}
            />
        );
    }

    // Show email settings when no block is selected
    return (
        <div className="h-full overflow-y-auto px-1">
            {/* Template Details Section */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__('Template Details')}
                    </span>
                </div>

                {/* Email Subject */}
                {context === 'email' && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            {__('Subject')}
                        </label>
                        <input
                            type="text"
                            value={templateSubject}
                            onChange={(e) => setTemplateSubject(e.target.value)}
                            placeholder={__('Email subject...')}
                            className="form-control"
                        />
                        <p className="text-xs text-gray-400 mt-1">
                            {__('Leave empty to use template name as subject')}
                        </p>
                    </div>
                )}

                {/* Status */}
                {context === 'email' && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            {__('Status')}
                        </label>
                        <div className="flex items-center gap-3">
                            <label className="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={templateStatus}
                                    onChange={(e) => setTemplateStatus(e.target.checked)}
                                    className="sr-only peer"
                                />
                                <div className="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span className="ms-2 text-sm text-gray-600">
                                    {templateStatus ? __('Active') : __('Inactive')}
                                </span>
                            </label>
                        </div>
                    </div>
                )}
            </div>

            {/* Email Settings Section */}
            <div className="mb-6">
                <div className="mb-4 pb-2 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__('Email Settings')}
                    </span>
                </div>

                {/* Width */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__('Email Width')}
                    </label>
                    <select
                        value={canvasSettings?.width || '700px'}
                        onChange={(e) =>
                            onCanvasSettingsUpdate({
                                ...canvasSettings,
                                width: e.target.value,
                            })
                        }
                        className="form-control"
                    >
                        <option value="500px">500px ({__('Narrow')})</option>
                        <option value="600px">600px ({__('Standard')})</option>
                        <option value="700px">700px ({__('Wide')})</option>
                        <option value="800px">800px ({__('Extra Wide')})</option>
                    </select>
                </div>
            </div>

            {/* Content Layout Styles - Same as blocks */}
            <LayoutStylesSection
                layoutStyles={canvasSettings?.layoutStyles || {}}
                onUpdate={handleCanvasLayoutStylesUpdate}
                onImageUpload={onImageUpload}
                defaultCollapsed={false}
            />

            <div className="mt-6 pt-4 border-t border-gray-200">
                <p className="text-xs text-gray-400 text-center">
                    {__('Click the block to edit')}
                </p>
            </div>
        </div>
    );
};

export default EmailPropertiesPanel;
