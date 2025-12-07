import { useState, useEffect } from 'react';
import { applyLayoutStyles, layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const CountdownBlock = ({ props, isSelected }) => {
    const [timeLeft, setTimeLeft] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0 });
    const [isExpired, setIsExpired] = useState(false);

    // Get layout styles for textAlign
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    useEffect(() => {
        const calculateTimeLeft = () => {
            const targetDateTime = `${props.targetDate}T${props.targetTime || '23:59'}:00`;
            const targetDate = new Date(targetDateTime);
            const now = new Date();
            const difference = targetDate - now;

            if (difference > 0) {
                setIsExpired(false);
                return {
                    days: Math.floor(difference / (1000 * 60 * 60 * 24)),
                    hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
                    minutes: Math.floor((difference / 1000 / 60) % 60),
                    seconds: Math.floor((difference / 1000) % 60),
                };
            }
            setIsExpired(true);
            return { days: 0, hours: 0, minutes: 0, seconds: 0 };
        };

        setTimeLeft(calculateTimeLeft());
        const timer = setInterval(() => {
            setTimeLeft(calculateTimeLeft());
        }, 1000);

        return () => clearInterval(timer);
    }, [props.targetDate, props.targetTime]);

    // Base container styles
    const defaultContainerStyle = {
        padding: '24px',
        backgroundColor: props.backgroundColor || '#1e293b',
        borderRadius: '8px',
        textAlign: layoutStyles.textAlign || props.align || 'center',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const titleStyle = {
        color: props.textColor || '#ffffff',
        fontSize: '18px',
        fontWeight: '600',
        marginBottom: '16px',
        margin: '0 0 16px 0',
    };

    const countdownWrapperStyle = {
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        gap: '8px',
    };

    const unitBoxStyle = {
        backgroundColor: 'rgba(255,255,255,0.1)',
        borderRadius: '8px',
        padding: '12px 16px',
        minWidth: '70px',
    };

    const numberStyle = {
        color: props.numberColor || '#635bff',
        fontSize: '36px',
        fontWeight: '700',
        lineHeight: '1',
        display: 'block',
    };

    const labelStyle = {
        color: props.textColor || '#ffffff',
        fontSize: '11px',
        marginTop: '4px',
        textTransform: 'uppercase',
        letterSpacing: '1px',
        display: 'block',
    };

    const separatorStyle = {
        color: props.numberColor || '#635bff',
        fontSize: '28px',
        fontWeight: '700',
    };

    const expiredStyle = {
        color: props.textColor || '#ffffff',
        fontSize: '18px',
        fontWeight: '600',
        padding: '20px 0',
        margin: 0,
    };

    const noteStyle = {
        marginTop: '12px',
        padding: '6px 12px',
        backgroundColor: 'rgba(251, 191, 36, 0.15)',
        color: '#fbbf24',
        borderRadius: '4px',
        fontSize: '11px',
        display: 'inline-block',
    };

    const units = [
        { value: timeLeft.days, label: 'Days' },
        { value: timeLeft.hours, label: 'Hours' },
        { value: timeLeft.minutes, label: 'Mins' },
        { value: timeLeft.seconds, label: 'Secs' },
    ];

    if (isExpired) {
        return (
            <div style={containerStyle}>
                <p style={expiredStyle}>{props.expiredMessage || 'This offer has expired!'}</p>
            </div>
        );
    }

    return (
        <div style={containerStyle}>
            {props.title && <p style={titleStyle}>{props.title}</p>}
            <div style={countdownWrapperStyle}>
                {units.map((unit, index) => (
                    <div key={index} style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <div style={unitBoxStyle}>
                            <span style={numberStyle}>
                                {String(unit.value).padStart(2, '0')}
                            </span>
                            <span style={labelStyle}>{unit.label}</span>
                        </div>
                        {index < units.length - 1 && (
                            <span style={separatorStyle}>:</span>
                        )}
                    </div>
                ))}
            </div>
            <div style={noteStyle}>
                ⚠ Snapshot at send time (won't update live in email)
            </div>
        </div>
    );
};

export default CountdownBlock;
