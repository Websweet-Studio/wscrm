import { readonly, ref, watch } from 'vue';

// Get initial state from cookie
const getInitialState = (): boolean => {
    if (typeof document === 'undefined') return false;
    const cookie = document.cookie.split('; ').find((row) => row.startsWith('sidebar-minimized='));
    return cookie ? cookie.split('=')[1] === 'true' : false;
};

// Save state to cookie
const saveToCookie = (value: boolean): void => {
    if (typeof document === 'undefined') return;
    document.cookie = `sidebar-minimized=${value}; path=/; max-age=${60 * 60 * 24 * 365}`; // 1 year
};

const isMinimized = ref(getInitialState());

// Watch for changes and save to cookie
watch(
    isMinimized,
    (newValue) => {
        saveToCookie(newValue);
    },
    { immediate: false },
);

export function useSidebar() {
    const toggleSidebar = () => {
        isMinimized.value = !isMinimized.value;
    };

    const minimize = () => {
        isMinimized.value = true;
    };

    const expand = () => {
        isMinimized.value = false;
    };

    return {
        isMinimized: readonly(isMinimized),
        toggleSidebar,
        minimize,
        expand,
    };
}
