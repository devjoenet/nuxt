<script lang="ts" setup>
import { useHttp } from "@inertiajs/vue3";
import { useDebounceFn } from "@vueuse/core";
import { ref, watch } from "vue";
import { FormAsyncSelect } from "@/components/Ui/FormAsyncSelect";
import { contacts, organizations } from "@/routes/crm/options";
import type { CrmOption } from "@/types/crm";

const props = withDefaults(defineProps<{ id: string; name: string; label: string; error?: string | boolean; required?: boolean; placeholder: string; type: "contact" | "organization"; initialOption?: CrmOption | null; clearable?: boolean }>(), {
  error: undefined,
  initialOption: null,
  clearable: false,
});
const selectedValue = defineModel<string | null>({ default: null });
const searchTerm = ref("");
const options = ref<CrmOption[]>(props.initialOption ? [props.initialOption] : []);
const request = useHttp<Record<string, never>, { data: CrmOption[] }>({});
const endpoint = (query: string): string => (props.type === "contact" ? contacts({ query: { query } }).url : organizations({ query: { query } }).url);
const search = useDebounceFn(async (query: string): Promise<void> => {
  if (query.trim().length < 2) {
    options.value = props.initialOption ? [props.initialOption] : [];
    return;
  }
  try {
    options.value = (await request.get(endpoint(query.trim()))).data;
  } catch {
    /* Superseded searches are expected to cancel. */
  }
}, 200);
watch(searchTerm, (query) => {
  request.cancel();
  void search(query);
});
</script>

<template>
  <FormAsyncSelect
    v-model="selectedValue"
    v-model:search-term="searchTerm"
    :clearable="clearable"
    :empty-text="`No matching ${type}s.`"
    :error="error"
    :id="id"
    :items="options"
    :label="label"
    :loading="request.processing"
    :name="name"
    :placeholder="placeholder"
    :required="required"
  />
</template>
