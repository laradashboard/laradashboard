import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const QuoteBlock = ({ props, isSelected }) => {
    // Base container styles
    const defaultContainerStyle = {
        padding: '20px',
        paddingLeft: '24px',
        backgroundColor: props.backgroundColor || '#f8fafc',
        borderLeft: `4px solid ${props.borderColor || '#635bff'}`,
        textAlign: props.align || 'left',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
    };

    // Apply layout styles (typography, background, spacing, border, shadow)
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base quote text styles
    const defaultQuoteStyle = {
        color: props.textColor || '#475569',
        fontSize: '16px',
        fontStyle: 'italic',
        lineHeight: '1.6',
        margin: '0 0 12px 0',
    };

    // Apply typography from layout styles to quote
    const quoteStyle = applyLayoutStyles(defaultQuoteStyle, props.layoutStyles);

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
