/**
 * SideDrawer - Animated right-side panel for builder overlays
 */

import { useEffect, useRef, useState } from "react";
import { __ } from "@lara-builder/i18n";

export default function SideDrawer({
    isOpen,
    onClose,
    title,
    subtitle,
    icon,
    iconClassName = "bg-gray-50 text-gray-600",
    iconStyle,
    children,
    footer,
    contentRef,
    widthClass = "sm:w-[32rem]",
    zIndexOverlay = 90,
    zIndexPanel = 100,
}) {
    const [mounted, setMounted] = useState(false);
    const [visible, setVisible] = useState(false);
    const panelRef = useRef(null);
    const visibleRef = useRef(false);

    visibleRef.current = visible;

    useEffect(() => {
        if (isOpen) {
            setMounted(true);
            const frame = requestAnimationFrame(() => {
                requestAnimationFrame(() => setVisible(true));
            });

            return () => cancelAnimationFrame(frame);
        }

        setVisible(false);
    }, [isOpen]);

    useEffect(() => {
        if (!isOpen && mounted) {
            const timeout = window.setTimeout(() => {
                setMounted(false);
            }, 320);

            return () => window.clearTimeout(timeout);
        }

        return undefined;
    }, [isOpen, mounted]);

    useEffect(() => {
        if (!mounted || !visible) {
            document.body.classList.remove("overflow-hidden");

            return undefined;
        }

        document.body.classList.add("overflow-hidden");

        return () => document.body.classList.remove("overflow-hidden");
    }, [mounted, visible]);

    useEffect(() => {
        const handleEscape = (event) => {
            if (event.key === "Escape" && isOpen) {
                onClose();
            }
        };

        document.addEventListener("keydown", handleEscape);

        return () => document.removeEventListener("keydown", handleEscape);
    }, [isOpen, onClose]);

    const handlePanelTransitionEnd = (event) => {
        if (event.target !== panelRef.current || event.propertyName !== "transform") {
            return;
        }

        if (!visibleRef.current) {
            setMounted(false);
        }
    };

    if (!mounted) {
        return null;
    }

    return (
        <>
            <div
                className={`fixed inset-0 bg-gray-900/30 backdrop-blur-sm transition-opacity duration-300 ease-out ${
                    visible
                        ? "opacity-100 pointer-events-auto"
                        : "opacity-0 pointer-events-none"
                }`}
                style={{ zIndex: zIndexOverlay }}
                onClick={onClose}
                aria-hidden="true"
            />

            <aside
                ref={panelRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby="side-drawer-title"
                className={`fixed top-0 right-0 bottom-0 w-full ${widthClass} max-w-full flex flex-col bg-white shadow-2xl border-l border-gray-200 transform transition-transform duration-300 ease-out ${
                    visible
                        ? "translate-x-0 pointer-events-auto"
                        : "translate-x-full pointer-events-none"
                }`}
                style={{ zIndex: zIndexPanel }}
                onTransitionEnd={handlePanelTransitionEnd}
                onClick={(event) => event.stopPropagation()}
            >
                <div className="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0">
                    <div className="flex items-center gap-3 min-w-0">
                        {icon && (
                            <div
                                className={`flex items-center justify-center w-9 h-9 rounded-lg shrink-0 ${iconClassName}`}
                                style={iconStyle}
                            >
                                {icon}
                            </div>
                        )}
                        <div className="min-w-0">
                            <h2
                                id="side-drawer-title"
                                className="text-base font-semibold text-gray-900 truncate"
                            >
                                {title}
                            </h2>
                            {subtitle && (
                                <p className="text-xs text-gray-500">{subtitle}</p>
                            )}
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                        title={__("Close")}
                    >
                        <iconify-icon
                            icon="mdi:close"
                            width="20"
                            height="20"
                        ></iconify-icon>
                    </button>
                </div>

                <div
                    ref={contentRef}
                    className="flex-1 overflow-y-auto min-h-0"
                >
                    {children}
                </div>

                {footer && <div className="shrink-0">{footer}</div>}
            </aside>
        </>
    );
}
