/**
 * Block Components for LaraBuilder
 *
 * All block components are exported from here for use in the builder.
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

export const getBlockComponent = (type) => blockComponents[type] || null;

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
