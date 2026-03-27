/**
 * Latest Posts Block - Canvas Preview Component
 *
 * Shows a placeholder preview in the LaraBuilder editor canvas.
 * Actual posts are rendered server-side via render.php.
 */

import { __ } from '@lara-builder/i18n';

const LatestPostsBlock = ({ props, isSelected }) => {
    const {
        postsCount = 6,
        columns = 3,
        headingText = '',
        layout = 'grid',
        showImage = true,
        showExcerpt = true,
        showDate = true,
        categorySlug = '',
        interactive = false,
        showSearch = true,
        showCategoryFilter = true,
        showSort = true,
    } = props;

    const placeholders = Array.from({ length: Math.min(postsCount, 6) }, (_, i) => i);

    return (
        <div style={{ padding: '24px 0' }}>
            {headingText && (
                <h2 style={{
                    fontSize: '24px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    marginBottom: '24px',
                    color: '#111827',
                }}>
                    {headingText}
                </h2>
            )}

            {interactive && (
                <div style={{
                    display: 'flex',
                    gap: '8px',
                    marginBottom: '16px',
                    flexWrap: 'wrap',
                }}>
                    {showSearch && (
                        <div style={{
                            flex: 1,
                            minWidth: '200px',
                            padding: '8px 12px',
                            border: '1px solid #d1d5db',
                            borderRadius: '8px',
                            fontSize: '12px',
                            color: '#9ca3af',
                        }}>
                            🔍 {__('Search posts...')}
                        </div>
                    )}
                    {showCategoryFilter && (
                        <div style={{
                            padding: '8px 12px',
                            border: '1px solid #d1d5db',
                            borderRadius: '8px',
                            fontSize: '12px',
                            color: '#6b7280',
                            minWidth: '140px',
                        }}>
                            {__('All Categories')} ▾
                        </div>
                    )}
                    {showSort && (
                        <div style={{
                            padding: '8px 12px',
                            border: '1px solid #d1d5db',
                            borderRadius: '8px',
                            fontSize: '12px',
                            color: '#6b7280',
                            minWidth: '100px',
                        }}>
                            {__('Latest')} ▾
                        </div>
                    )}
                </div>
            )}

            {categorySlug && (
                <div style={{
                    textAlign: 'center',
                    marginBottom: '16px',
                    fontSize: '12px',
                    color: '#6b7280',
                }}>
                    {__('Filtered by category')}: <strong>{categorySlug}</strong>
                </div>
            )}

            <div style={{
                display: layout === 'list' ? 'flex' : 'grid',
                flexDirection: layout === 'list' ? 'column' : undefined,
                gridTemplateColumns: layout === 'grid' ? `repeat(${columns}, 1fr)` : undefined,
                gap: '16px',
            }}>
                {placeholders.map((i) => (
                    <div
                        key={i}
                        style={{
                            display: layout === 'list' ? 'flex' : 'block',
                            gap: layout === 'list' ? '16px' : undefined,
                            border: '1px solid #e5e7eb',
                            borderRadius: '12px',
                            overflow: 'hidden',
                            background: '#fff',
                        }}
                    >
                        {showImage && (
                            <div style={{
                                width: layout === 'list' ? '160px' : '100%',
                                minHeight: layout === 'list' ? '100px' : undefined,
                                aspectRatio: layout === 'grid' ? '16/9' : undefined,
                                background: 'linear-gradient(135deg, #f3f4f6, #e5e7eb)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                flexShrink: 0,
                                color: '#9ca3af',
                                fontSize: '24px',
                            }}>
                                &#128247;
                            </div>
                        )}
                        <div style={{ padding: '12px' }}>
                            {showDate && (
                                <div style={{ fontSize: '11px', color: '#9ca3af', marginBottom: '6px' }}>
                                    {__('Jan 01, 2025')}
                                </div>
                            )}
                            <div style={{
                                fontSize: '14px',
                                fontWeight: '600',
                                color: '#111827',
                                marginBottom: '6px',
                            }}>
                                {__('Post Title')} {i + 1}
                            </div>
                            {showExcerpt && (
                                <div style={{ fontSize: '12px', color: '#6b7280' }}>
                                    {__('Post excerpt preview text...')}
                                </div>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {interactive && (
                <div style={{
                    display: 'flex',
                    justifyContent: 'center',
                    gap: '8px',
                    marginTop: '16px',
                }}>
                    <span style={{ padding: '4px 12px', fontSize: '12px', color: '#9ca3af', background: '#f3f4f6', borderRadius: '6px' }}>← {__('Previous')}</span>
                    <span style={{ padding: '4px 12px', fontSize: '12px', color: '#6b7280' }}>{__('Page 1 of N')}</span>
                    <span style={{ padding: '4px 12px', fontSize: '12px', color: '#374151', background: '#f3f4f6', borderRadius: '6px' }}>{__('Next')} →</span>
                </div>
            )}

            <div style={{
                textAlign: 'center',
                marginTop: '12px',
                fontSize: '11px',
                color: '#9ca3af',
            }}>
                {__('Showing')} {postsCount} {__('posts')} &middot; {columns} {__('columns')} &middot; {layout}
                {interactive && <> &middot; {__('interactive')}</>}
            </div>
        </div>
    );
};

export default LatestPostsBlock;
