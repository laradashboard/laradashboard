/**
 * TaxonomySection - Complete taxonomy management for the post editor
 *
 * Features:
 * - Hierarchical term display with proper indentation
 * - Collapsible taxonomy sections
 * - Term creation drawer (slide-in panel)
 * - Parent term selection for hierarchical taxonomies
 * - Search/filter terms
 */

import { useState, useEffect, useCallback } from 'react';
import { __ } from '@lara-builder/i18n';

/**
 * TermDrawer - Slide-in drawer for creating new terms
 */
const TermDrawer = ({
    isOpen,
    onClose,
    taxonomy,
    postType,
    postId,
    onTermCreated,
}) => {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        parent_id: '',
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Reset form when drawer opens
    useEffect(() => {
        if (isOpen) {
            setFormData({ name: '', description: '', parent_id: '' });
            setErrors({});
        }
    }, [isOpen]);

    // Handle body scroll lock
    useEffect(() => {
        if (isOpen) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
        return () => document.body.classList.remove('overflow-hidden');
    }, [isOpen]);

    // Handle escape key
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && isOpen) {
                onClose();
            }
        };
        document.addEventListener('keydown', handleEscape);
        return () => document.removeEventListener('keydown', handleEscape);
    }, [isOpen, onClose]);

    const handleSubmit = async () => {
        if (isSubmitting) return;

        // Validate
        if (!formData.name || formData.name.trim() === '') {
            setErrors({ name: __('Name is required') });
            return;
        }

        setIsSubmitting(true);
        setErrors({});

        try {
            // Get CSRF token
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta?.getAttribute('content') || '';

            const response = await fetch(`/api/admin/terms/${taxonomy.name}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    name: formData.name.trim(),
                    description: formData.description.trim(),
                    parent_id: formData.parent_id || null,
                    taxonomy: taxonomy.name,
                    post_type: postType,
                    post_id: postId,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    setErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'An error occurred');
            }

            // Success - notify parent and close
            if (data.term) {
                onTermCreated(data.term);
                onClose();

                // Show success toast if available
                if (typeof window.showToast === 'function') {
                    window.showToast('success', __('Success'), data.message || __('Term created successfully'));
                }
            }
        } catch (error) {
            console.error('Error saving term:', error);
            if (typeof window.showToast === 'function') {
                window.showToast('error', __('Error'), error.message || __('Failed to create term'));
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    // Build hierarchical options for parent select
    const buildParentOptions = (terms, parentId = null, depth = 0) => {
        const options = [];
        const children = terms.filter((t) => t.parent_id === parentId);

        children.forEach((term) => {
            const indent = '\u2014 '.repeat(depth);
            options.push({
                value: term.id,
                label: indent + term.name,
            });
            options.push(...buildParentOptions(terms, term.id, depth + 1));
        });

        return options;
    };

    const parentOptions = taxonomy.hierarchical ? buildParentOptions(taxonomy.terms || []) : [];

    if (!isOpen) return null;

    return (
        <>
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-gray-900/30 backdrop-blur-sm z-[60]"
                onClick={onClose}
            />

            {/* Drawer */}
            <div className="fixed top-0 right-0 bottom-0 w-full sm:w-96 max-w-full z-[70] flex flex-col bg-white dark:bg-gray-800 shadow-xl border-l border-gray-200 dark:border-gray-700 transform transition-transform duration-300">
                {/* Header */}
                <div className="px-5 py-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                    <h3 className="text-base font-medium text-gray-700 dark:text-white">
                        {__('Add New :taxonomy').replace(':taxonomy', taxonomy.label_singular || taxonomy.label)}
                    </h3>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-200"
                    >
                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto p-5 space-y-4">
                    {/* Name field */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('Name')} <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            className="form-control"
                            placeholder={__('Enter name')}
                            autoFocus
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                    </div>

                    {/* Description field */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('Description')}
                        </label>
                        <textarea
                            value={formData.description}
                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            rows={3}
                            className="form-control !h-20"
                            placeholder={__('Enter description (optional)')}
                        />
                        {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                    </div>

                    {/* Parent term selector (for hierarchical taxonomies) */}
                    {taxonomy.hierarchical && parentOptions.length > 0 && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {__('Parent :taxonomy').replace(':taxonomy', taxonomy.label_singular || taxonomy.label)}
                            </label>
                            <select
                                value={formData.parent_id}
                                onChange={(e) => setFormData({ ...formData, parent_id: e.target.value })}
                                className="form-control"
                            >
                                <option value="">{__('None')}</option>
                                {parentOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div className="flex gap-3">
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={isSubmitting}
                            className="btn-primary flex items-center gap-2"
                        >
                            {isSubmitting ? (
                                <>
                                    <iconify-icon icon="mdi:loading" className="animate-spin" width="16" height="16"></iconify-icon>
                                    {__('Saving...')}
                                </>
                            ) : (
                                __('Save')
                            )}
                        </button>
                        <button type="button" onClick={onClose} className="btn-default">
                            {__('Cancel')}
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
};

/**
 * HierarchicalTermList - Renders terms with proper hierarchy
 */
const HierarchicalTermList = ({ terms, taxonomyName, selectedTerms, onTermToggle, parentId = null, level = 0 }) => {
    // Normalize parentId for comparison (handle both null and integer)
    const normalizedParentId = parentId === null || parentId === '' || parentId === undefined ? null : parseInt(parentId, 10);

    const filteredTerms = terms.filter((term) => {
        const termParentId = term.parent_id === null || term.parent_id === '' || term.parent_id === undefined
            ? null
            : parseInt(term.parent_id, 10);
        return termParentId === normalizedParentId;
    });

    if (filteredTerms.length === 0) return null;

    return (
        <>
            {filteredTerms.map((term) => {
                const termId = parseInt(term.id, 10);
                const isSelected = (selectedTerms[taxonomyName] || []).includes(termId);
                const hasChildren = terms.some((t) => {
                    const tParentId = t.parent_id === null || t.parent_id === '' || t.parent_id === undefined
                        ? null
                        : parseInt(t.parent_id, 10);
                    return tParentId === termId;
                });

                return (
                    <div key={term.id}>
                        <div
                            className={`flex items-start py-1 ${level > 0 ? 'ml-4 border-l border-gray-200 dark:border-gray-700 pl-2' : ''}`}
                        >
                            <label className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded flex-1">
                                <input
                                    type="checkbox"
                                    checked={isSelected}
                                    onChange={() => onTermToggle(taxonomyName, term.id)}
                                    className="form-checkbox h-4 w-4 text-primary rounded border-gray-300 dark:border-gray-600"
                                />
                                <span className="text-sm text-gray-700 dark:text-gray-300">{term.name}</span>
                            </label>
                        </div>
                        {hasChildren && (
                            <HierarchicalTermList
                                terms={terms}
                                taxonomyName={taxonomyName}
                                selectedTerms={selectedTerms}
                                onTermToggle={onTermToggle}
                                parentId={term.id}
                                level={level + 1}
                            />
                        )}
                    </div>
                );
            })}
        </>
    );
};

/**
 * FlatTermList - Renders flat list of terms (for non-hierarchical taxonomies like tags)
 */
const FlatTermList = ({ terms, taxonomyName, selectedTerms, onTermToggle }) => {
    return (
        <>
            {terms.map((term) => {
                const isSelected = (selectedTerms[taxonomyName] || []).includes(term.id);

                return (
                    <label
                        key={term.id}
                        className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded"
                    >
                        <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={() => onTermToggle(taxonomyName, term.id)}
                            className="form-checkbox h-4 w-4 text-primary rounded border-gray-300 dark:border-gray-600"
                        />
                        <span className="text-sm text-gray-700 dark:text-gray-300">{term.name}</span>
                    </label>
                );
            })}
        </>
    );
};

/**
 * TaxonomySection - Main component for displaying and managing taxonomies
 */
const TaxonomySection = ({
    taxonomies,
    selectedTerms,
    onTermToggle,
    onTermsUpdate,
    postType,
    postId,
}) => {
    const [expandedTaxonomies, setExpandedTaxonomies] = useState({});
    const [drawerTaxonomy, setDrawerTaxonomy] = useState(null);
    const [searchTerms, setSearchTerms] = useState({});
    const [localTaxonomies, setLocalTaxonomies] = useState(taxonomies);

    // Update local taxonomies when props change
    useEffect(() => {
        setLocalTaxonomies(taxonomies);
    }, [taxonomies]);

    const toggleExpanded = useCallback((taxonomyName) => {
        setExpandedTaxonomies((prev) => ({
            ...prev,
            [taxonomyName]: prev[taxonomyName] === undefined ? false : !prev[taxonomyName],
        }));
    }, []);

    const handleTermCreated = useCallback((newTerm) => {
        // Normalize the term to ensure parent_id is an integer or null
        const normalizedTerm = {
            ...newTerm,
            id: parseInt(newTerm.id, 10),
            parent_id: newTerm.parent_id ? parseInt(newTerm.parent_id, 10) : null,
        };

        // Add the new term to the local taxonomy list
        setLocalTaxonomies((prev) =>
            prev.map((tax) => {
                if (tax.name === normalizedTerm.taxonomy) {
                    return {
                        ...tax,
                        terms: [...(tax.terms || []), normalizedTerm],
                    };
                }
                return tax;
            })
        );

        // Auto-select the newly created term
        onTermToggle(normalizedTerm.taxonomy, normalizedTerm.id);

        // Notify parent of updated taxonomies if callback provided
        if (onTermsUpdate) {
            onTermsUpdate(localTaxonomies);
        }
    }, [localTaxonomies, onTermToggle, onTermsUpdate]);

    const handleSearchChange = useCallback((taxonomyName, value) => {
        setSearchTerms((prev) => ({
            ...prev,
            [taxonomyName]: value,
        }));
    }, []);

    const filterTerms = useCallback((terms, searchQuery) => {
        if (!searchQuery) return terms;
        const query = searchQuery.toLowerCase();
        return terms.filter((term) => term.name.toLowerCase().includes(query));
    }, []);

    if (!localTaxonomies || localTaxonomies.length === 0) {
        return null;
    }

    return (
        <div className="mb-6">
            <div className="mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <span className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {__('Taxonomies')}
                </span>
            </div>

            {localTaxonomies.map((taxonomy) => {
                const isExpanded = expandedTaxonomies[taxonomy.name] !== false;
                const searchQuery = searchTerms[taxonomy.name] || '';
                const terms = taxonomy.terms || [];
                const filteredTermsList = filterTerms(terms, searchQuery);
                const selectedCount = (selectedTerms[taxonomy.name] || []).length;

                return (
                    <div key={taxonomy.name} className="mb-4 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        {/* Taxonomy Header */}
                        <div className="flex items-center justify-between bg-gray-50 dark:bg-gray-800 px-3 py-2">
                            <button
                                type="button"
                                onClick={() => toggleExpanded(taxonomy.name)}
                                className="flex items-center gap-2 flex-1 text-left text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white"
                            >
                                <iconify-icon
                                    icon={isExpanded ? 'mdi:chevron-down' : 'mdi:chevron-right'}
                                    width="18"
                                    height="18"
                                ></iconify-icon>
                                <span>{taxonomy.label}</span>
                                {selectedCount > 0 && (
                                    <span className="ml-2 px-1.5 py-0.5 text-xs bg-primary/10 text-primary rounded-full">
                                        {selectedCount}
                                    </span>
                                )}
                            </button>
                            <button
                                type="button"
                                onClick={() => setDrawerTaxonomy(taxonomy)}
                                className="p-1 text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors"
                                title={__('Add New :taxonomy').replace(':taxonomy', taxonomy.label_singular || taxonomy.label)}
                            >
                                <iconify-icon icon="mdi:plus-circle" width="20" height="20"></iconify-icon>
                            </button>
                        </div>

                        {/* Taxonomy Content */}
                        {isExpanded && (
                            <div className="p-3">
                                {/* Search box (show when many terms) */}
                                {terms.length > 8 && (
                                    <div className="mb-2">
                                        <div className="relative">
                                            <iconify-icon
                                                icon="mdi:magnify"
                                                width="16"
                                                height="16"
                                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"
                                            ></iconify-icon>
                                            <input
                                                type="text"
                                                value={searchQuery}
                                                onChange={(e) => handleSearchChange(taxonomy.name, e.target.value)}
                                                placeholder={__('Search :items...').replace(':items', taxonomy.label.toLowerCase())}
                                                className="form-control pl-8 py-1.5 text-sm"
                                            />
                                        </div>
                                    </div>
                                )}

                                {/* Terms list */}
                                <div className="max-h-48 overflow-y-auto">
                                    {filteredTermsList.length > 0 ? (
                                        taxonomy.hierarchical ? (
                                            <HierarchicalTermList
                                                terms={filteredTermsList}
                                                taxonomyName={taxonomy.name}
                                                selectedTerms={selectedTerms}
                                                onTermToggle={onTermToggle}
                                            />
                                        ) : (
                                            <FlatTermList
                                                terms={filteredTermsList}
                                                taxonomyName={taxonomy.name}
                                                selectedTerms={selectedTerms}
                                                onTermToggle={onTermToggle}
                                            />
                                        )
                                    ) : (
                                        <p className="text-sm text-gray-400 dark:text-gray-500 py-2 text-center">
                                            {searchQuery
                                                ? __('No matching :items').replace(':items', taxonomy.label.toLowerCase())
                                                : __('No :items found').replace(':items', taxonomy.label.toLowerCase())}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                );
            })}

            {/* Term Drawer */}
            {drawerTaxonomy && (
                <TermDrawer
                    isOpen={!!drawerTaxonomy}
                    onClose={() => setDrawerTaxonomy(null)}
                    taxonomy={drawerTaxonomy}
                    postType={postType}
                    postId={postId}
                    onTermCreated={handleTermCreated}
                />
            )}
        </div>
    );
};

export default TaxonomySection;
