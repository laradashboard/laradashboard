/**
 * List Block - Canvas Component
 */

import { useRef, useEffect, useCallback } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";

export default function ListBlock({
    props,
    isSelected,
    onUpdate,
    onRegisterTextFormat,
}) {
    const editorRef = useRef(null);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const items = props.items || ["List item"];
    const listType = props.listType || "bullet";
    const ListTag = listType === "number" ? "ol" : "ul";

    // Save items from editor DOM
    const saveItems = useCallback(() => {
        if (!editorRef.current) return;

        const lis = editorRef.current.querySelectorAll(":scope > li");
        const newItems = Array.from(lis)
            .map((li) => {
                const clone = li.cloneNode(true);
                clone.querySelectorAll("ul, ol").forEach((n) => n.remove());
                return clone.innerHTML.trim();
            })
            .filter((html) => html && html !== "<br>");

        const finalItems = newItems.length > 0 ? newItems : [""];

        if (
            JSON.stringify(finalItems) !==
            JSON.stringify(propsRef.current.items)
        ) {
            onUpdateRef.current({ ...propsRef.current, items: finalItems });
        }
    }, []);

    // Initialize editor when selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            editorRef.current.innerHTML = items
                .map((item) => `<li>${item || "<br>"}</li>`)
                .join("");
            // Use requestAnimationFrame to ensure focus happens after click event completes
            // This is necessary when inserting blocks via click from the BlockPanel
            requestAnimationFrame(() => {
                if (editorRef.current) {
                    editorRef.current.focus();
                }
            });
        }
    }, [isSelected]);

    // First time load the items when not selected.
    useEffect(() => {
        if (!isSelected && editorRef.current) {
            editorRef.current.innerHTML = items
                .map((item) => `<li>${item || "<br>"}</li>`)
                .join("");
        }
    }, []);

    // Register toolbar
    useEffect(() => {
        if (onRegisterTextFormat) {
            onRegisterTextFormat(
                isSelected
                    ? {
                          editorRef,
                          isContentEditable: true,
                          align: props.align || "left",
                          onAlignChange: (align) =>
                              onUpdateRef.current({
                                  ...propsRef.current,
                                  align,
                              }),
                      }
                    : null
            );
        }
    }, [isSelected, onRegisterTextFormat, props.align]);

    // Styles
    const containerStyle = applyLayoutStyles(
        { padding: "8px", borderRadius: "4px" },
        props.layoutStyles
    );

    const listStyle = {
        ...applyLayoutStyles(
            {
                color: props.color || "#333333",
                fontSize: props.fontSize || "16px",
                lineHeight: "1.8",
                margin: 0,
                textAlign: props.align || "left",
            },
            props.layoutStyles
        ),
        paddingLeft: listType === "check" ? "0" : "24px",
        listStyleType:
            listType === "bullet"
                ? "disc"
                : listType === "number"
                ? "decimal"
                : "none",
    };

    if (isSelected) {
        return (
            <div style={containerStyle} data-text-editing="true">
                <ListTag
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={saveItems}
                    onBlur={saveItems}
                    style={{
                        ...listStyle,
                        paddingLeft: listType === "check" ? "8px" : "32px",
                        paddingTop: "8px",
                        paddingRight: "8px",
                        paddingBottom: "8px",
                        border: "2px solid var(--color-primary, #635bff)",
                        borderRadius: "4px",
                        outline: "none",
                        background: "white",
                        minHeight: "40px",
                    }}
                />
            </div>
        );
    }

    return (
        <div style={containerStyle}>
            <ListTag
                style={listStyle}
                suppressContentEditableWarning
                ref={editorRef}
            ></ListTag>
        </div>
    );
}
