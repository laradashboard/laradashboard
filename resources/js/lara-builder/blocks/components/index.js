/**
 * Block Components for LaraBuilder
 *
 * All block components are exported from here for use in the builder.
 * External modules can register their own blocks via blockRegistry.
 */

import HeadingBlock from './HeadingBlock';
import TextBlock from './TextBlock';
import TextEditorBlock from './TextEditorBlock';
import ImageBlock from './ImageBlock';
import ButtonBlock from './ButtonBlock';
import DividerBlock from './DividerBlock';
import SpacerBlock from './SpacerBlock';
import ColumnsBlock from './ColumnsBlock';
import SocialBlock from './SocialBlock';
import HtmlBlock from './HtmlBlock';
import QuoteBlock from './QuoteBlock';
import ListBlock from './ListBlock';
import VideoBlock from './VideoBlock';
import FooterBlock from './FooterBlock';
import CountdownBlock from './CountdownBlock';
import TableBlock from './TableBlock';
import CodeBlock from './CodeBlock';
import PreformattedBlock from './PreformattedBlock';
import AccordionBlock from './AccordionBlock';
import { blockRegistry } from '../../registry/BlockRegistry';

export const blockComponents = {
    heading: HeadingBlock,
    text: TextBlock,
    'text-editor': TextEditorBlock,
    image: ImageBlock,
    button: ButtonBlock,
    divider: DividerBlock,
    spacer: SpacerBlock,
    columns: ColumnsBlock,
    social: SocialBlock,
    html: HtmlBlock,
    quote: QuoteBlock,
    list: ListBlock,
    video: VideoBlock,
    footer: FooterBlock,
    countdown: CountdownBlock,
    table: TableBlock,
    code: CodeBlock,
    preformatted: PreformattedBlock,
    accordion: AccordionBlock,
};

/**
 * Get block component - checks registry first, then built-in components
 * This allows external modules to register their own blocks
 */
export const getBlockComponent = (type) => {
    // First check the registry for custom blocks (external modules)
    const registryComponent = blockRegistry.getComponent(type);
    if (registryComponent) {
        return registryComponent;
    }

    // Fall back to built-in components
    return blockComponents[type] || null;
};

export {
    HeadingBlock,
    TextBlock,
    TextEditorBlock,
    ImageBlock,
    ButtonBlock,
    DividerBlock,
    SpacerBlock,
    ColumnsBlock,
    SocialBlock,
    HtmlBlock,
    QuoteBlock,
    ListBlock,
    VideoBlock,
    FooterBlock,
    CountdownBlock,
    TableBlock,
    CodeBlock,
    PreformattedBlock,
    AccordionBlock,
};
