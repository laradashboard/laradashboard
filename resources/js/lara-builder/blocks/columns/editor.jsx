import { __ } from '@lara-builder/i18n';

const ColumnsEditor = ({ props, onUpdate }) => {
    const { columns = 2, gap = '20px' } = props;

    const handleColumnsChange = (newColumns) => {
        const columnCount = Math.min(Math.max(parseInt(newColumns) || 1, 1), 6);
        const currentChildren = props.children || [];

        const newChildren = Array.from({ length: columnCount }, (_, i) =>
            currentChildren[i] || []
        );

        onUpdate({
            ...props,
            columns: columnCount,
            children: newChildren
        });
    };

    const handleGapChange = (newGap) => {
        onUpdate({ ...props, gap: newGap });
    };

    return (
        <div className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__('Number of Columns')}
                </label>
                <div className="grid grid-cols-6 gap-2">
                    {[1, 2, 3, 4, 5, 6].map((count) => (
                        <button
                            key={count}
                            onClick={() => handleColumnsChange(count)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${columns === count
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                                }
                            `}
                        >
                            {count}
                        </button>
                    ))}
                </div>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__('Gap Between Columns')}
                </label>
                <select
                    value={gap}
                    onChange={(e) => handleGapChange(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                    <option value="0px">{__('None')}</option>
                    <option value="8px">{__('Small')} (8px)</option>
                    <option value="12px">{__('Medium')} (12px)</option>
                    <option value="16px">{__('Normal')} (16px)</option>
                    <option value="20px">{__('Large')} (20px)</option>
                    <option value="24px">{__('Extra Large')} (24px)</option>
                    <option value="32px">{__('2X Large')} (32px)</option>
                </select>
            </div>

            <div className="pt-4 border-t border-gray-200">
                <div className="flex items-start space-x-2 text-sm text-gray-600">
                    <svg
                        className="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <div>
                        <p className="font-medium text-gray-700">{__('Tip')}:</p>
                        <p className="mt-1">{__('Drag blocks from the sidebar into the column areas to build your layout. Each column can contain multiple blocks.')}</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ColumnsEditor;
