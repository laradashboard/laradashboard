/**
 * Block Registry - Allows registration of custom blocks
 * Users can extend this to add their own blocks
 */

const blockRegistry = new Map();

export const registerBlock = (type, config) => {
    blockRegistry.set(type, {
        type,
        ...config,
    });
};

export const getBlock = (type) => blockRegistry.get(type);

export const getAllBlocks = () => Array.from(blockRegistry.values());

export const getBlocksByCategory = (category) => {
    return getAllBlocks().filter(block => block.category === category);
};

export const getCategories = () => {
    const categories = new Set();
    getAllBlocks().forEach(block => categories.add(block.category));
    return Array.from(categories);
};

// Default block configurations with Iconify icons
export const defaultBlocks = [
    {
        type: 'heading',
        label: 'Heading',
        category: 'Content',
        icon: 'mdi:format-header-1',
        defaultProps: {
            text: 'Your Heading Here',
            level: 'h1',
            align: 'left',
            color: '#333333',
            fontSize: '32px',
            fontWeight: 'bold',
        },
    },
    {
        type: 'text',
        label: 'Text',
        category: 'Content',
        icon: 'mdi:format-text',
        defaultProps: {
            content: 'Click to edit this text.',
            align: 'left',
            color: '#666666',
            fontSize: '16px',
            lineHeight: '1.6',
        },
    },
    {
        type: 'image',
        label: 'Image',
        category: 'Content',
        icon: 'mdi:image-outline',
        defaultProps: {
            src: '',
            alt: 'Image description',
            width: '100%',
            height: 'auto',
            customWidth: '',
            customHeight: '',
            align: 'center',
            link: '',
        },
    },
    {
        type: 'button',
        label: 'Button',
        category: 'Content',
        icon: 'mdi:button-cursor',
        defaultProps: {
            text: 'Click Here',
            link: '#',
            backgroundColor: '#3b82f6',
            textColor: '#ffffff',
            borderRadius: '6px',
            padding: '12px 24px',
            align: 'center',
            fontSize: '16px',
            fontWeight: '600',
        },
    },
    {
        type: 'social',
        label: 'Social',
        category: 'Content',
        icon: 'mdi:share-variant-outline',
        defaultProps: {
            align: 'center',
            iconSize: '32px',
            gap: '12px',
            links: {
                facebook: '',
                twitter: '',
                instagram: '',
                linkedin: '',
                youtube: '',
            },
        },
    },
    {
        type: 'divider',
        label: 'Divider',
        category: 'Layout',
        icon: 'mdi:minus',
        defaultProps: {
            style: 'solid',
            color: '#e5e7eb',
            thickness: '1px',
            width: '100%',
            margin: '20px 0',
        },
    },
    {
        type: 'spacer',
        label: 'Spacer',
        category: 'Layout',
        icon: 'mdi:arrow-expand-vertical',
        defaultProps: {
            height: '40px',
        },
    },
    {
        type: 'columns',
        label: 'Columns',
        category: 'Layout',
        icon: 'mdi:view-column-outline',
        defaultProps: {
            columns: 2,
            gap: '20px',
            children: [[], []],
        },
    },
    {
        type: 'html',
        label: 'HTML',
        category: 'Advanced',
        icon: 'mdi:code-tags',
        defaultProps: {
            code: '<div style="padding: 20px; text-align: center;">Custom HTML content</div>',
        },
    },
    {
        type: 'quote',
        label: 'Quote',
        category: 'Content',
        icon: 'mdi:format-quote-close',
        defaultProps: {
            text: 'This is a quote or testimonial that stands out from the rest of the content.',
            author: 'John Doe',
            authorTitle: 'CEO, Company',
            borderColor: '#3b82f6',
            backgroundColor: '#f8fafc',
            textColor: '#475569',
            authorColor: '#1e293b',
            align: 'left',
        },
    },
    {
        type: 'list',
        label: 'List',
        category: 'Content',
        icon: 'mdi:format-list-bulleted',
        defaultProps: {
            items: ['First item in the list', 'Second item in the list', 'Third item in the list'],
            listType: 'bullet',
            color: '#333333',
            fontSize: '16px',
            iconColor: '#3b82f6',
        },
    },
    {
        type: 'video',
        label: 'Video',
        category: 'Content',
        icon: 'mdi:play-circle-outline',
        defaultProps: {
            thumbnailUrl: '',
            videoUrl: '',
            alt: 'Video thumbnail',
            width: '100%',
            align: 'center',
            playButtonColor: '',
        },
    },
    {
        type: 'footer',
        label: 'Footer',
        category: 'Layout',
        icon: 'mdi:page-layout-footer',
        defaultProps: {
            companyName: 'Your Company Name',
            address: '123 Street Name, City, Country',
            phone: '+1 234 567 890',
            email: 'contact@company.com',
            unsubscribeText: 'Unsubscribe from these emails',
            unsubscribeUrl: '#unsubscribe',
            copyright: '© 2024 Your Company. All rights reserved.',
            textColor: '#6b7280',
            linkColor: '#3b82f6',
            fontSize: '12px',
            align: 'center',
        },
    },
    {
        type: 'countdown',
        label: 'Countdown',
        category: 'Advanced',
        icon: 'mdi:timer-outline',
        defaultProps: {
            targetDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            targetTime: '23:59',
            title: 'Sale Ends In',
            backgroundColor: '#1e293b',
            textColor: '#ffffff',
            numberColor: '#3b82f6',
            align: 'center',
            expiredMessage: 'This offer has expired!',
        },
    },
    {
        type: 'table',
        label: 'Table',
        category: 'Advanced',
        icon: 'mdi:table',
        defaultProps: {
            headers: ['Product', 'Qty', 'Price'],
            rows: [
                ['Product Name 1', '2', '$29.99'],
                ['Product Name 2', '1', '$49.99'],
            ],
            showHeader: true,
            headerBgColor: '#f1f5f9',
            headerTextColor: '#1e293b',
            borderColor: '#e2e8f0',
            cellPadding: '12px',
            fontSize: '14px',
        },
    },
];

// Register default blocks
defaultBlocks.forEach(block => registerBlock(block.type, block));

export default blockRegistry;
