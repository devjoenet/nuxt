import { usePage } from "@inertiajs/vue3";
import { shallowRef, watch } from "vue";
import type { AppPageProps } from "@/types";

interface LastToast {
  message: string;
  shownAt: number;
}

export function useFlashToasts(): void {
  const page = usePage<AppPageProps>();
  const toast = useToast();
  const lastToast = shallowRef<LastToast | null>(null);

  const showSuccess = (message: string | undefined): void => {
    if (!message) {
      return;
    }

    const now = Date.now();

    if (lastToast.value?.message === message && now - lastToast.value.shownAt < 1_000) {
      return;
    }

    lastToast.value = { message, shownAt: now };
    toast.add({ title: message, color: "success" });
  };

  watch(() => page.props.flash.success, showSuccess, { immediate: true });
}
