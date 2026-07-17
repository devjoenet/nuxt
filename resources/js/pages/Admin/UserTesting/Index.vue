<script lang="ts" setup>
  import { computed } from "vue";
  import { Head } from "@inertiajs/vue3";
  import UserTestingPageCard from "@/components/UserTestingPageCard.vue";
  import Layout from "@/layouts/Default.vue";

  defineOptions({ layout: Layout });

  interface UserTestingPage {
    id: number;
    category: string;
    title: string;
    path: string;
    url: string | null;
    tested: boolean;
    tested_at: string | null;
    tested_by: string | null;
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
  <Head title="User testing" />

  <UDashboardPanel id="admin-user-testing">
    <template #header>
      <UDashboardNavbar description="Walk every route, record feedback, and approve the current experience." title="User testing">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-6">
        <UPageCard class="overflow-hidden" variant="subtle">
          <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-end">
            <div>
              <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">Release gate</p>
              <h1 class="mt-3 text-3xl font-semibold tracking-tight text-highlighted">Every page gets a deliberate set of eyes.</h1>
              <p class="mt-3 max-w-2xl leading-7 text-muted">
                Use this checklist for exploratory testing, responsive review, and final approval. Notes are saved per page so another teammate—or an automated poller—can pick up
                the latest feedback.
              </p>
            </div>
            <div class="rounded-2xl bg-elevated p-5">
              <p class="text-sm text-muted">Approval progress</p>
              <p class="mt-1 text-4xl font-semibold text-highlighted">
                {{ testedCount }}<span class="text-xl text-muted"> / {{ pages.length }}</span>
              </p>
              <UProgress :model-value="pages.length ? (testedCount / pages.length) * 100 : 0" class="mt-3" color="primary" />
            </div>
          </div>
        </UPageCard>

        <section v-for="category in categories" :key="category.name" class="grid gap-4">
          <div class="flex items-end justify-between gap-4">
            <div>
              <p class="text-xs font-bold tracking-[0.18em] text-muted uppercase">{{ category.name }}</p>
              <h2 class="mt-1 text-xl font-semibold text-highlighted">{{ category.pages.length }} {{ category.pages.length === 1 ? "page" : "pages" }}</h2>
            </div>
            <p class="text-sm text-muted">{{ category.pages.filter((page) => page.tested).length }} approved</p>
          </div>
          <div class="grid gap-4 xl:grid-cols-2">
            <UserTestingPageCard v-for="page in category.pages" :key="page.id" :page="page" />
          </div>
        </section>
      </div>
    </template>
  </UDashboardPanel>
</template>
