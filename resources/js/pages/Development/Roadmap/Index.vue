<script lang="ts" setup>
  import { Head, Link } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { index as userTestingIndex } from "@/routes/development/userTesting";

  defineOptions({ layout: Layout });

  const auth = useAuth();

  defineProps<{
    content: string;
    source: string;
  }>();
</script>

<template>
  <Head title="Development roadmap" />

  <UDashboardPanel id="development-roadmap">
    <template #header>
      <UDashboardNavbar description="The checked-in plan for Southeast Code development." title="Development roadmap">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <Link v-if="auth.user?.can?.['development.user_testing.manage']" :href="userTestingIndex.url()" class="text-sm font-medium text-primary hover:underline">
            User testing
          </Link>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="min-h-0 flex-1 overflow-y-auto pe-1">
        <UPageCard class="overflow-hidden" variant="subtle">
          <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-default pb-4">
            <div>
              <p class="text-xs font-bold tracking-[0.18em] text-primary uppercase">Source of truth</p>
              <p class="mt-1 text-sm text-muted">{{ source }}</p>
            </div>
            <Link v-if="auth.user?.can?.['development.user_testing.manage']" :href="userTestingIndex.url()" class="text-sm text-muted hover:text-primary hover:underline">
              Review page feedback →
            </Link>
          </div>
          <article
            class="max-w-none text-base leading-7 text-muted [&_a]:text-primary [&_a]:underline [&_blockquote]:my-5 [&_blockquote]:border-s-2 [&_blockquote]:border-primary/40 [&_blockquote]:ps-4 [&_code]:rounded [&_code]:bg-elevated [&_code]:px-1 [&_code]:py-0.5 [&_h1]:mb-6 [&_h1]:text-3xl [&_h1]:font-semibold [&_h1]:tracking-tight [&_h1]:text-highlighted [&_h2]:mb-4 [&_h2]:mt-10 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-highlighted [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-highlighted [&_li]:my-1 [&_ol]:my-5 [&_ol]:list-decimal [&_ol]:ps-6 [&_p]:my-4 [&_pre]:my-5 [&_pre]:overflow-x-auto [&_pre]:rounded-xl [&_pre]:bg-elevated [&_pre]:p-4 [&_strong]:font-semibold [&_strong]:text-highlighted [&_table]:my-5 [&_table]:w-full [&_td]:border [&_td]:border-default [&_td]:p-2 [&_th]:border [&_th]:border-default [&_th]:bg-elevated [&_th]:p-2 [&_ul]:my-5 [&_ul]:list-disc [&_ul]:ps-6"
            v-html="content"
          />
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
