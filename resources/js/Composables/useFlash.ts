import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
const page = usePage<PageProps>();

// Display flash success message as sonner or toast
export function useFlashError() {
    watch(
        () => page.props.flash.error,
        () => showError()
    );
}

// Display flash success message as sonner or toast
export function useFlashSuccess() {
   watch(
    () => page.props.flash.success,
    () => showSuccess()
    );
}

export function useFlashSuccessOnMount() {
    onMounted(() => showSuccess());
}

export function useFlashErrorOnMount() {
    onMounted(() => showError());
}

export function showError() {
    if (page.props.flash.error) {
        toast.error(page.props.flash.error, {
            style: {
                background: "#ef4444",
                color: "white",
            },
            position: "top-center",
        });

        setTimeout(() => {
            page.props.flash.error = null;
        }, 300);
    }
}

export function showSuccess() {
    if (page.props.flash.success) {
        toast.success(page.props.flash.success, {
            style: {
                background: "#22c55e",
                color: "white",
            },
            position: "top-center",
        });

        setTimeout(() => {
            page.props.flash.success = null;
        }, 300);
    }
}
