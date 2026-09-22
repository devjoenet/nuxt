<script setup lang="ts">
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { computed, ref, watch } from "vue";
const props = defineProps<{ title: string; rows: Record<string, string | number>[] }>();
const search = ref("");
const page = ref(1);
const sortKey = ref("");
const descending = ref(false);
const keys = computed(() => Object.keys(props.rows[0] ?? {}));
const filtered = computed(() => {
  const rows = props.rows.filter((row) => Object.values(row).some((value) => String(value).toLowerCase().includes(search.value.toLowerCase())));
  if (!sortKey.value) return rows;
  return rows.toSorted((a, b) => {
    const left = String(a[sortKey.value]);
    const right = String(b[sortKey.value]);
    const numeric = /^\$?[\d,.]+%?$/;
    const difference = numeric.test(left) && numeric.test(right) ? Number(left.replace(/[$,%]/g, "")) - Number(right.replace(/[$,%]/g, "")) : left.localeCompare(right, "en", { numeric: true });
    return difference * (descending.value ? -1 : 1);
  });
});
const visible = computed(() => filtered.value.slice((page.value - 1) * 5, page.value * 5));
watch(search, () => {
  page.value = 1;
});
function sort(key: string): void {
  descending.value = sortKey.value === key ? !descending.value : false;
  sortKey.value = key;
  page.value = 1;
}
</script>
<template>
  <Card class="min-w-0" variant="outline">
    <template #header
      ><div class="grid gap-3 sm:grid-cols-2 sm:items-center">
        <h3 class="text-lg font-semibold text-content-heading">{{ title }}</h3>
        <FormInput v-model="search" :label="`Search ${title}`" type="search" /></div
    ></template>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <caption class="sr-only">
          {{
            title
          }}
          — searchable, sortable demo records
        </caption>
        <thead>
          <tr>
            <th v-for="key in keys" :key="key" class="border-b border-content-border p-3" :aria-sort="sortKey === key ? (descending ? 'descending' : 'ascending') : 'none'">
              <button class="rounded whitespace-nowrap text-content-heading focus-visible:outline-2 focus-visible:outline-primary" type="button" @click="sort(key)">
                {{ key }} <span aria-hidden="true">{{ sortKey === key ? (descending ? "↓" : "↑") : "↕" }}</span>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in visible" :key="index">
            <td v-for="key in keys" :key="key" class="border-b border-content-divider p-3 text-content-body">{{ row[key] }}</td>
          </tr>
          <tr v-if="!visible.length">
            <td :colspan="keys.length" class="p-6 text-center text-content-subtle">No matching records.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <template #footer
      ><div class="flex flex-wrap items-center justify-between gap-3">
        <p aria-live="polite" class="text-sm text-content-subtle">{{ filtered.length }} records · Page {{ page }}</p>
        <UPagination v-model:page="page" :items-per-page="5" :total="filtered.length" :sibling-count="0" /></div
    ></template>
  </Card>
</template>
