import { ref } from 'vue';

export interface ToastItem {
    id: string;
    title?: string;
    description?: string;
    variant?: 'default' | 'destructive' | 'success';
    duration?: number;
}

const toasts = ref<ToastItem[]>([]);

let toastId = 0;

function generateToastId(): string {
    return `toast-${++toastId}`;
}

export function useToast() {
    const addToast = (toast: Omit<ToastItem, 'id'>) => {
        const id = generateToastId();
        const newToast: ToastItem = {
            id,
            duration: 5000,
            ...toast,
        };

        toasts.value.push(newToast);

        // Auto remove toast after duration
        if (newToast.duration && newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }

        return id;
    };

    const removeToast = (id: string) => {
        const index = toasts.value.findIndex((toast) => toast.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (title: string, description?: string) => {
        return addToast({
            title,
            description,
            variant: 'success',
        });
    };

    const error = (title: string, description?: string) => {
        return addToast({
            title,
            description,
            variant: 'destructive',
        });
    };

    const info = (title: string, description?: string) => {
        return addToast({
            title,
            description,
            variant: 'default',
        });
    };

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        info,
    };
}
