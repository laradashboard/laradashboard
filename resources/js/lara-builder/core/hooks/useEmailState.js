/**
 * useEmailState - Hook for managing email template state
 *
 * Handles template name, subject, and dirty state tracking for email context.
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

    // Mark as saved - reset dirty tracking
    const markEmailSaved = () => {
        templateDataRef.current = {
            name: templateName,
            subject: templateSubject,
        };
        setTemplateDirty(false);
    };

    return {
        // State
        templateName,
        templateSubject,
        templateDirty,
        // Setters
        setTemplateName,
        setTemplateSubject,
        // Actions
        markEmailSaved,
    };
}

export default useEmailState;
