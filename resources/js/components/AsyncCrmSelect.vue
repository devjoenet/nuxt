<script lang="ts" setup>
  import { useHttp } from "@inertiajs/vue3";
  import FormField from "@/components/FormField.vue";
  import { contacts, organizations } from "@/routes/crm/options";
  import type { CrmOption } from "@/types/crm";

  const props = withDefaults(
    defineProps<{
      id: string;
      name: string;
      label: string;
      error?: string | boolean;
      required?: boolean;
      placeholder: string;
      type: "contact" | "organization";
      initialOption?: CrmOption | null;
      clearable?: boolean;
    }>(),
    { error: undefined, initialOption: null, clearable: false },
  );

  const selectedValue = defineModel<string | null>({ default: null });
  const searchTerm = ref("");
  const options = ref<CrmOption[]>(props.initialOption ? [props.initialOption] : []);
  const request = useHttp<Record<string, never>, { data: CrmOption[] }>({});
  let timer: ReturnType<typeof setTimeout>;

  const endpoint = (query: string) => (props.type === "contact" ? contacts({ query: { query } }).url : organizations({ query: { query } }).url);

  const search = (query: string): void => {
    clearTimeout(timer);

    if (query.trim().length < 2) {
      options.value = props.initialOption ? [props.initialOption] : [];
      request.cancel();
      return;
    }

    timer = setTimeout(async () => {
      request.cancel();

      try {
        const response = await request.get(endpoint(query.trim()));
        options.value = response.data;
      } catch {
        // A newer search replaces cancelled requests; the selector remains usable.
      }
    }, 200);
  };

  watch(searchTerm, search);
</script>

<template>
  <FormField :id="id" :error="error" :label="label" :name="name" :required="required" :static-label="true">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <input :name="name" :value="selectedValue ?? ''" type="hidden" />
      <USelectMenu
        :id="fieldId"
        v-model="selectedValue"
        v-model:search-term="searchTerm"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :clear="clearable"
        :color="error ? 'error' : 'primary'"
        :disabled="request.processing"
        :ignore-filter="true"
        :items="options"
        :placeholder="placeholder"
        :required="required"
        :ui="{ base: 'min-h-14 w-full rounded-xl !bg-transparent px-4 py-4 text-base leading-6 focus:outline-none' }"
        value-key="value"
      >
        <template #empty>
          <span v-if="request.processing">Searching…</span>
          <span v-else-if="searchTerm.trim().length < 2">Enter at least 2 characters.</span>
          <span v-else>No matching {{ type }}s.</span>
        </template>
      </USelectMenu>
    </template>
  </FormField>
</template>
