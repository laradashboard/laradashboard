/**
 * useEmailState - Hook for managing email template state
 *
 * Handles template name, subject, status, and dirty state tracking for email context.
 */

import { useState, useEffect, useRef } from "react";

/**
 * @param {Object} options
 * @param {Object} options.templateData - Initial template data
 * @param {boolean} options.isEmailContext - Whether we're in email context
 * @returns {Object} Email state and setters
 */
export function useEmailState({ templateData, isEmailContext }) {
    const [templateName, setTemplateName] = useState(templateData?.name || "");
    const [templateSubject, setTemplateSubject] = useState(
        templateData?.subject || ""
    );
    const [templateStatus, setTemplateStatus] = useState(
        templateData?.is_active !== undefined ? templateData.is_active : true
    );

    // Track template data changes for dirty detection
    const templateDataRef = useRef({
        name: templateData?.name || "",
        subject: templateData?.subject || "",
        is_active: templateData?.is_active !== undefined ? templateData.is_active : true,
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
            templateSubject !== templateDataRef.current.subject ||
            templateStatus !== templateDataRef.current.is_active;
        setTemplateDirty(hasTemplateChanges);
    }, [templateName, templateSubject, templateStatus, isEmailContext]);

    // Mark as saved - reset dirty tracking
    const markEmailSaved = () => {
        templateDataRef.current = {
            name: templateName,
            subject: templateSubject,
            is_active: templateStatus,
        };
        setTemplateDirty(false);
    };

    return {
        // State
        templateName,
        templateSubject,
        templateStatus,
        templateDirty,
        // Setters
        setTemplateName,
        setTemplateSubject,
        setTemplateStatus,
        // Actions
        markEmailSaved,
    };
}

export default useEmailState;
