/**
 * List Block - Canvas Component
 */

import { useRef, useEffect, useLayoutEffect, useCallback } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import {
    normalizeHexColor,
    resolvePageTextColor,
} from "@lara-builder/tokens/contentTokens";

const DEFAULT_ITEMS = ["List item"];
/** Legacy default that should follow list text color instead of primary. */
const LEGACY_LIST_ICON_COLOR = "#635bff";

const serializeItems = (items) => JSON.stringify(items ?? []);

/**
 * Checklist icon follows text color by default (currentColor).
 * Only returns an explicit color when a non-legacy iconColor is set.
 */
const resolveListIconColor = (iconColor, textColor, typographyColor) => {
    const normalized = normalizeHexColor(iconColor);

    if (
        !normalized ||
        normalized === "inherit" ||
        normalized === "currentcolor" ||
        normalized === LEGACY_LIST_ICON_COLOR
    ) {
        return resolvePageTextColor(textColor, typographyColor);
    }

    return resolvePageTextColor(iconColor, typographyColor) || iconColor;
};

const buildListHtml = (items, listType = "bullet") => {
    const normalized = items?.length ? items : DEFAULT_ITEMS;

    if (listType === "check") {
        return normalized
            .map(
                (item) =>
                    `<li class="lb-list-check-item">` +
                    `<span class="lb-list-check-icon" contenteditable="false">✓</span>` +
                    `<span>${item || "<br>"}</span>` +
                    `</li>`
            )
            .join("");
    }

    return normalized.map((item) => `<li>${item || "<br>"}</li>`).join("");
};

export default function ListBlock({
    props,
    isSelected,
    onUpdate,
    onRegisterTextFormat,
}) {
    const editorRef = useRef(null);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);
    const lastPropsItems = useRef(serializeItems(props.items));
    const lastListType = useRef(props.listType || "bullet");
    const typographyColor = props.layoutStyles?.typography?.color;
    const resolvedIconColor = resolveListIconColor(
        props.iconColor,
        props.color,
        typographyColor
    );
    const lastIconColor = useRef(resolvedIconColor);
    const liveItemsRef = useRef(props.items || DEFAULT_ITEMS);

    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const items = props.items?.length ? props.items : DEFAULT_ITEMS;
    const listType = props.listType || "bullet";
    const iconColor = resolvedIconColor;
    const ListTag = listType === "number" ? "ol" : "ul";

    const extractItemsFromEditor = useCallback(() => {
        if (!editorRef.current) {
            return liveItemsRef.current;
        }

        const lis = editorRef.current.querySelectorAll(":scope > li");
        const newItems = Array.from(lis)
            .map((li) => {
                const clone = li.cloneNode(true);
                clone.querySelectorAll("ul, ol").forEach((node) => node.remove());

                if (listType === "check") {
                    const contentSpan = clone.querySelector("span:last-child");
                    return (contentSpan?.innerHTML ?? clone.innerHTML).trim();
                }

                return clone.innerHTML.trim();
            })
            .filter((html) => html && html !== "<br>");

        return newItems.length > 0 ? newItems : [""];
    }, [listType]);

    const syncEditorFromItems = useCallback(
        (itemsToRender) => {
            if (!editorRef.current) {
                return;
            }

            editorRef.current.innerHTML = buildListHtml(
                itemsToRender,
                listType
            );
        },
        [listType]
    );

    const saveItems = useCallback(() => {
        if (!editorRef.current) {
            return;
        }

        const finalItems = extractItemsFromEditor();
        const serialized = serializeItems(finalItems);

        liveItemsRef.current = finalItems;

        if (serialized !== lastPropsItems.current) {
            lastPropsItems.current = serialized;
            onUpdateRef.current({ ...propsRef.current, items: finalItems });
        }
    }, [extractItemsFromEditor]);

    const handleKeyDown = useCallback((e) => {
        const isMod = e.ctrlKey || e.metaKey;

        // Allow native browser undo/redo inside list items
        if (isMod && (e.key === "z" || e.key === "y")) {
            e.stopPropagation();
            return;
        }
    }, []);

    // Initialize editor when selected
    useEffect(() => {
        if (!isSelected || !editorRef.current) {
            return;
        }

        const isEmptyEditor =
            editorRef.current.innerHTML === "" ||
            editorRef.current.innerHTML === "<br>";

        if (isEmptyEditor) {
            syncEditorFromItems(items);
            lastPropsItems.current = serializeItems(items);
            liveItemsRef.current = items;
        }

        lastListType.current = listType;

        requestAnimationFrame(() => {
            editorRef.current?.focus();
        });
        // Only initialize when selection changes — item/listType sync is handled separately.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isSelected]);

    // Sync editor when props change externally (list type, sidebar, builder undo/redo)
    useLayoutEffect(() => {
        if (!editorRef.current) {
            return;
        }

        const serializedItems = serializeItems(items);
        const itemsChanged = serializedItems !== lastPropsItems.current;
        const listTypeChanged = listType !== lastListType.current;
        const iconColorChanged = iconColor !== lastIconColor.current;

        if (!itemsChanged && !listTypeChanged && !iconColorChanged) {
            return;
        }

        if (listTypeChanged) {
            const preservedItems =
                items?.length > 0 && items.some((item) => item !== "")
                    ? items
                    : liveItemsRef.current;

            syncEditorFromItems(preservedItems);
            lastPropsItems.current = serializeItems(preservedItems);
            liveItemsRef.current = preservedItems;
            lastListType.current = listType;
            lastIconColor.current = iconColor;
            return;
        }

        if (itemsChanged || iconColorChanged) {
            syncEditorFromItems(items);
            lastPropsItems.current = serializedItems;
            liveItemsRef.current = items;
            lastIconColor.current = iconColor;
        }
    }, [items, listType, iconColor, typographyColor, syncEditorFromItems]);

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

    const containerStyle = applyLayoutStyles(
        { padding: "8px", borderRadius: "4px" },
        props.layoutStyles
    );

    const baseListStyle = {
        fontSize: props.fontSize || "16px",
        lineHeight: "1.8",
        margin: 0,
        textAlign: props.align || "left",
    };

    const resolvedColor = resolvePageTextColor(
        props.color,
        props.layoutStyles?.typography?.color
    );
    if (resolvedColor) {
        baseListStyle.color = resolvedColor;
    }

    const listClassName =
        listType === "check" ? "lb-list lb-list-check" : "lb-list";

    const listStyle = {
        ...applyLayoutStyles(baseListStyle, props.layoutStyles),
        listStyleType:
            listType === "bullet"
                ? "disc"
                : listType === "number"
                  ? "decimal"
                  : "none",
        ...(listType === "check" && iconColor
            ? { "--lb-list-icon-color": iconColor }
            : {}),
    };

    if (isSelected) {
        return (
            <div style={containerStyle} data-text-editing="true">
                <ListTag
                    ref={editorRef}
                    className={listClassName}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={saveItems}
                    onBlur={saveItems}
                    onKeyDown={handleKeyDown}
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
                className={listClassName}
                style={listStyle}
                suppressContentEditableWarning
                dangerouslySetInnerHTML={{
                    __html: buildListHtml(items, listType),
                }}
            />
        </div>
    );
}
