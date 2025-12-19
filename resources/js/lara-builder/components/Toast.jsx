import { useEffect } from 'react';

const Toast = ({ toast, onClose }) => {
    useEffect(() => {
        if (toast) {
            const timer = setTimeout(() => {
                onClose();
            }, 4000);
            return () => clearTimeout(timer);
        }
    }, [toast, onClose]);

    if (!toast) return null;

    const bgColor = toast.variant === 'success' ? 'bg-green-500' :
                    toast.variant === 'error' ? 'bg-red-500' :
                    toast.variant === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';

    const icon = toast.variant === 'success' ? 'mdi:check-circle' :
                 toast.variant === 'error' ? 'mdi:alert-circle' :
                 toast.variant === 'warning' ? 'mdi:alert' : 'mdi:information';

    return (
        <div className="fixed bottom-4 right-4 z-[100] animate-slide-in-right">
            <div className={`${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3 max-w-sm`}>
                <iconify-icon icon={icon} width="20" height="20" class="flex-shrink-0 mt-0.5"></iconify-icon>
                <div className="flex-1 min-w-0">
                    <p className="font-medium text-sm">{toast.title}</p>
                    {toast.message && (
                        <p className="text-sm opacity-90 mt-0.5">{toast.message}</p>
                    )}
                </div>
                <button
                    onClick={onClose}
                    className="flex-shrink-0 p-1 hover:bg-white/20 rounded transition-colors"
                >
                    <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                </button>
            </div>
        </div>
    );
};

export default Toast;
