/**
 * Internal dependencies.
 */
import { __ } from "@lara-builder/i18n";

export default function editor() {
    return (
        <div className="text-gray-500 text-sm">
            {__("Select the text block to edit its content.")}
        </div>
    );
}
