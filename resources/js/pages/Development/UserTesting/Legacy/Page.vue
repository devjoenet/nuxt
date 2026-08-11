<script lang="ts" setup>
import UserTestingPageCard from "./UserTestingPageCard.vue";

interface UserTestingPage {
  id: number;
  category: string;
  title: string;
  roadmap_task_number: number | null;
  status: "Needs eyes" | "In Development" | "Needs verification" | "Reviewed" | "Needs Approval";
  path: string;
  url: string | null;
  tested: boolean;
  tested_at: string | null;
  tested_by: string | null;
  approved: boolean;
  approved_at: string | null;
  roadmap_task: boolean;
  roadmap_task_at: string | null;
  notes: string | null;
}

const props = defineProps<{ pages: UserTestingPage[] }>();
const testedCount = computed(() => props.pages.filter((page) => page.tested).length);
const categories = computed(() => {
  const grouped = new Map<string, UserTestingPage[]>();

  for (const page of props.pages) {
    grouped.set(page.category, [...(grouped.get(page.category) ?? []), page]);
  }

  return [...grouped.entries()].map(([name, pages]) => ({ name, pages }));
});
</script>

<template>
  <WorkspacePage id="development-user-testing-legacy" description="Temporary access during the task-round migration." title="Legacy user-testing checklist">
    <div class="grid gap-6">
      <UAlert color="warning" description="This checklist is retained only until the Development workspace cleanup task removes the old records." title="Legacy workflow" variant="subtle" />

      <UPageCard class="overflow-hidden" variant="subtle">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-end">
          <div>
            <p class="text-xs font-bold tracking-[0.22em] text-highlighted uppercase">Legacy release gate</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-highlighted">Page-by-page checklist</h1>
            <p class="mt-3 max-w-2xl leading-7 text-muted">Existing review records remain available during migration. New testing work belongs in task-specific rounds.</p>
          </div>
          <div class="rounded-2xl bg-elevated p-5">
            <p class="text-sm text-muted">Review progress</p>
            <p class="mt-1 text-4xl font-semibold text-highlighted">
              {{ testedCount }}<span class="text-xl text-muted"> / {{ pages.length }}</span>
            </p>
            <UProgress :model-value="pages.length ? (testedCount / pages.length) * 100 : 0" class="mt-3" color="primary" />
          </div>
        </div>
      </UPageCard>

      <UPageCard v-if="pages.length === 0" class="p-8 text-center" variant="subtle">
        <UIcon class="mx-auto size-10 text-success" name="i-ph-check-circle" />
        <h2 class="mt-4 text-xl font-semibold text-highlighted">The legacy queue is clear.</h2>
      </UPageCard>

      <section v-for="category in categories" :key="category.name" class="grid gap-4">
        <div class="flex items-end justify-between gap-4">
          <div>
            <p class="text-xs font-bold tracking-[0.18em] text-muted uppercase">{{ category.name }}</p>
            <h2 class="mt-1 text-xl font-semibold text-highlighted">{{ category.pages.length }} {{ category.pages.length === 1 ? "page" : "pages" }}</h2>
          </div>
          <p class="text-sm text-muted">{{ category.pages.filter((page) => page.tested).length }} reviewed</p>
        </div>
        <div class="grid gap-4 xl:grid-cols-2">
          <UserTestingPageCard v-for="page in category.pages" :key="page.id" :page="page" />
        </div>
      </section>
    </div>
  </WorkspacePage>
</template>
