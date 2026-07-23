<script lang="ts" setup>
  defineProps<{
    panelId: string;
    navbarTitle: string;
    indexUrl: string;
    indexLabel: string;
    editUrl?: string;
  }>();
</script>

<template>
  <UDashboardPanel :id="panelId">
    <template #header>
      <UDashboardNavbar :title="navbarTitle">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton :label="indexLabel" :to="indexUrl" color="neutral" variant="ghost" />
          <UButton v-if="editUrl" :to="editUrl" color="neutral" label="Edit" variant="outline" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="mx-auto grid w-full max-w-6xl gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
        <main class="min-w-0 space-y-8">
          <slot name="summary" />
          <slot />
        </main>
        <aside class="min-w-0 xl:border-l xl:border-default xl:pl-8">
          <slot name="aside" />
        </aside>
      </div>
    </template>
  </UDashboardPanel>
</template>
