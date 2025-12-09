/**
 * Table of Contents Block - Canvas Component
 *
 * Displays a live preview of the table of contents generated from actual headings.
 */

import { useMemo } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";
import { useBuilder } from "../../core/BuilderContext";

/**
 * Recursively extract headings from blocks
 */
const extractHeadings = (blocks, minLevel, maxLevel) => {
    const headings = [];

    const processBlocks = (blockList) => {
        if (!Array.isArray(blockList)) return;

        for (const block of blockList) {
            if (block.type === "heading" && block.props?.text) {
                const levelStr = block.props.level || "h2";
                const level = parseInt(levelStr.replace("h", ""), 10);

                if (level >= minLevel && level <= maxLevel) {
                    // Strip HTML tags from text (loop until fully sanitized)
                    let text = block.props.text;
                    let prevText;
                    do {
                        prevText = text;
                        text = text.replace(/<[^>]*>/g, "");
                    } while (text !== prevText);
                    if (text.trim()) {
                        headings.push({
                            id: block.id,
                            level,
                            text: text.trim(),
                        });
                    }
                }
            }

            // Check nested blocks in columns
            if (block.props?.children && Array.isArray(block.props.children)) {
                for (const column of block.props.children) {
                    if (Array.isArray(column)) {
                        processBlocks(column);
                    }
                }
            }
        }
    };

    processBlocks(blocks);
    return headings;
};

export default function TocBlock({ props, isSelected }) {
    const {
        title = __("Table of Contents"),
        showTitle = true,
        minLevel = "h1",
        maxLevel = "h4",
        listStyle = "bullet",
        backgroundColor = "#f8fafc",
        borderColor = "#e2e8f0",
        titleColor = "#1e293b",
        linkColor = "#635bff",
        layoutStyles,
    } = props;

    // Get all blocks from builder context
    const { state } = useBuilder();
    const allBlocks = state.blocks;

    // Parse level numbers
    const minLevelNum = parseInt(minLevel.replace("h", ""), 10);
    const maxLevelNum = parseInt(maxLevel.replace("h", ""), 10);

    // Extract actual headings from content
    const headings = useMemo(
        () => extractHeadings(allBlocks, minLevelNum, maxLevelNum),
        [allBlocks, minLevelNum, maxLevelNum]
    );

    // Container styles
    const containerStyle = applyLayoutStyles(
        {
            backgroundColor,
            border: `1px solid ${borderColor}`,
            borderRadius: "8px",
            padding: "16px 20px",
        },
        layoutStyles
    );

    // Title styles
    const titleStyle = {
        color: titleColor,
        fontSize: "18px",
        fontWeight: "600",
        marginBottom: "12px",
        margin: 0,
        paddingBottom: "8px",
        borderBottom: `1px solid ${borderColor}`,
    };

    // List styles
    const listStyle_ = {
        margin: 0,
        padding: 0,
        listStyle:
            listStyle === "none"
                ? "none"
                : listStyle === "number"
                ? "decimal"
                : "disc",
        paddingLeft: listStyle === "none" ? "0" : "20px",
    };

    // Item styles based on level
    const getItemStyle = (level) => ({
        marginLeft: `${(level - minLevelNum) * 16}px`,
        marginBottom: "6px",
        lineHeight: "1.6",
    });

    const linkStyle = {
        color: linkColor,
        textDecoration: "none",
    };

    const ListTag = listStyle === "number" ? "ol" : "ul";

    return (
        <div style={containerStyle}>
            {showTitle && <h4 style={titleStyle}>{title}</h4>}

            <nav>
                <ListTag style={listStyle_}>
                    {headings.length === 0 ? (
                        <li style={{ color: "#94a3b8", fontStyle: "italic" }}>
                            {__("No headings found.")}
                        </li>
                    ) : (
                        headings.map((heading) => (
                            <li key={heading.id} style={getItemStyle(heading.level)}>
                                <span style={linkStyle}>{heading.text}</span>
                            </li>
                        ))
                    )}
                </ListTag>
            </nav>
        </div>
    );
}
