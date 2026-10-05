import { useHttp } from "@inertiajs/vue3";
import { useDebounceFn } from "@vueuse/core";
import { computed, onScopeDispose, ref, watch, type Ref } from "vue";
import { contacts, organizations } from "@/routes/crm/options";
import type { CrmOption } from "@/types/crm";

interface OptionPage {
  data: CrmOption[];
  links: { next: string | null };
}

export function useCrmOptions(type: "contact" | "organization", selectedValue: Ref<string | null>, initialOption: () => CrmOption | null) {
  const searchTerm = ref("");
  const options = ref<CrmOption[]>([]);
  let lastOptions: CrmOption[] = [];
  const selectedOption = ref(initialOption());
  const open = ref(false);
  const loading = ref(false);
  const failed = ref(false);
  const nextPage = ref<number | null>(null);
  const currentPage = ref(1);
  const request = useHttp<Record<string, never>, OptionPage>({});
  let generation = 0;

  const selectedLabel = computed(() => (selectedOption.value?.value === selectedValue.value ? selectedOption.value.label : null));
  const emptyText = computed(() => (searchTerm.value.trim() ? `No matching ${type}s.` : `No ${type}s available.`));

  async function load(page = 1, version = generation): Promise<void> {
    if (version !== generation || !open.value) return;
    currentPage.value = page;
    loading.value = true;
    failed.value = false;
    const query = { query: searchTerm.value.trim(), page };
    const url = type === "contact" ? contacts({ query }).url : organizations({ query }).url;
    try {
      const response = await request.get(url);
      if (version !== generation) return;
      options.value = page === 1 ? response.data : [...options.value, ...response.data];
      lastOptions = options.value;
      nextPage.value = response.links.next ? page + 1 : null;
    } catch {
      if (version === generation) failed.value = true;
    } finally {
      if (version === generation) loading.value = false;
    }
  }

  const search = useDebounceFn((version: number) => load(1, version), 200);
  function invalidate(): void {
    generation++;
    request.cancel();
    loading.value = false;
  }
  watch(
    searchTerm,
    () => {
      invalidate();
      options.value = [];
      nextPage.value = null;
      failed.value = false;
      if (open.value) {
        loading.value = true;
        void search(generation);
      }
    },
    { flush: "sync" },
  );
  watch(
    selectedValue,
    (value) => {
      selectedOption.value = lastOptions.find((option) => option.value === value) ?? (selectedOption.value?.value === value ? selectedOption.value : initialOption());
    },
    { flush: "sync" },
  );
  watch(initialOption, (option) => {
    if (option?.value === selectedValue.value) selectedOption.value = option;
  });

  function setOpen(value: boolean): void {
    open.value = value;
    invalidate();
    if (value) void load();
  }
  function loadMore(): void {
    if (nextPage.value && !loading.value) void load(nextPage.value);
  }
  function retry(): void {
    if (!loading.value) void load(currentPage.value);
  }
  onScopeDispose(invalidate);
  return { searchTerm, options, selectedLabel, loading, failed, emptyText, nextPage, setOpen, loadMore, retry };
}
