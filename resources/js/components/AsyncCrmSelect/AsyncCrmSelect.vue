<script lang="ts" setup>
import { FormAsyncSelect } from "@/components/Ui/FormAsyncSelect";
import type { CrmOption } from "@/types/crm";
import { useCrmOptions } from "./useCrmOptions";

const props = withDefaults(defineProps<{ id: string; name: string; label: string; error?: string | boolean; required?: boolean; placeholder: string; type: "contact" | "organization"; initialOption?: CrmOption | null; clearable?: boolean }>(), {
  error: undefined,
  initialOption: null,
  clearable: false,
});
const selectedValue = defineModel<string | null>({ default: null });
const { searchTerm, options, selectedLabel, loading, failed, emptyText, nextPage, setOpen, loadMore, retry } = useCrmOptions(props.type, selectedValue, () => props.initialOption);
</script>

<template>
  <FormAsyncSelect v-model="selectedValue" v-model:search-term="searchTerm" :clearable="clearable" :empty-text="emptyText" :error="error" :id="id" :items="options" :label="label" :loading="loading" :name="name" :placeholder="placeholder" :required="required" @update:open="setOpen">
    <template #default
      ><span class="w-0 min-w-0 flex-1 truncate text-left" :class="selectedLabel ? '' : 'text-content-subtle'">{{ selectedLabel ?? placeholder }}</span></template
    >
    <template #empty
      ><span role="status">{{ loading ? "Loading options…" : failed ? "Options could not be loaded." : emptyText }}</span></template
    >
    <template #content-bottom>
      <div v-if="failed || nextPage" class="border-t border-content-border p-2">
        <p v-if="failed" class="mb-2 text-sm text-content-subtle" role="alert">Options could not be loaded. Try again.</p>
        <UButton v-if="failed" block color="neutral" size="sm" variant="ghost" @click="retry">Retry</UButton>
        <UButton v-else block color="neutral" :loading="loading" :disabled="loading" size="sm" variant="ghost" @click="loadMore">Load more options</UButton>
      </div>
    </template>
  </FormAsyncSelect>
</template>
