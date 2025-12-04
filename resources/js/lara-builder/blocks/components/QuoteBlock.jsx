import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const QuoteBlock = ({ props, isSelected }) => {
    // Get layout styles (typography, background, spacing, etc.)
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const containerStyle = {
        padding: '20px',
        paddingLeft: '24px',
        backgroundColor: layoutStyles.backgroundColor || props.backgroundColor || '#f8fafc',
        backgroundImage: layoutStyles.backgroundImage,
        backgroundSize: layoutStyles.backgroundSize,
        backgroundPosition: layoutStyles.backgroundPosition,
        backgroundRepeat: layoutStyles.backgroundRepeat,
        borderLeft: `4px solid ${props.borderColor || '#635bff'}`,
        textAlign: layoutStyles.textAlign || props.align || 'left',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        // Apply margin/padding from layout styles
        ...( layoutStyles.marginTop && { marginTop: layoutStyles.marginTop }),
        ...( layoutStyles.marginRight && { marginRight: layoutStyles.marginRight }),
        ...( layoutStyles.marginBottom && { marginBottom: layoutStyles.marginBottom }),
        ...( layoutStyles.marginLeft && { marginLeft: layoutStyles.marginLeft }),
        ...( layoutStyles.paddingTop && { paddingTop: layoutStyles.paddingTop }),
        ...( layoutStyles.paddingRight && { paddingRight: layoutStyles.paddingRight }),
        ...( layoutStyles.paddingBottom && { paddingBottom: layoutStyles.paddingBottom }),
        ...( layoutStyles.paddingLeft && { paddingLeft: layoutStyles.paddingLeft }),
    };

    const quoteStyle = {
        color: layoutStyles.color || props.textColor || '#475569',
        fontSize: layoutStyles.fontSize || '16px',
        fontStyle: layoutStyles.fontStyle || 'italic',
        fontFamily: layoutStyles.fontFamily,
        fontWeight: layoutStyles.fontWeight,
        letterSpacing: layoutStyles.letterSpacing,
        textTransform: layoutStyles.textTransform,
        textDecoration: layoutStyles.textDecoration,
        lineHeight: layoutStyles.lineHeight || '1.6',
        margin: '0 0 12px 0',
    };

    const authorStyle = {
        color: props.authorColor || '#1e293b',
        fontSize: '14px',
        fontWeight: '600',
        margin: 0,
    };

    const titleStyle = {
        color: props.textColor || '#475569',
        fontSize: '12px',
        margin: 0,
    };

    return (
        <div style={containerStyle}>
            <p style={quoteStyle}>"{props.text}"</p>
            {(props.author || props.authorTitle) && (
                <div>
                    {props.author && <p style={authorStyle}>{props.author}</p>}
                    {props.authorTitle && <p style={titleStyle}>{props.authorTitle}</p>}
                </div>
            )}
        </div>
    );
};

export default QuoteBlock;
