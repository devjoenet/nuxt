<script lang="ts" setup>
defineProps<{
  panelId: string;
  navbarTitle: string;
  documentTitle: string;
  indexUrl: string;
  indexLabel: string;
  editUrl?: string;
}>();
</script>

<template>
  <WorkspacePage :id="panelId" :document-title="documentTitle" :title="navbarTitle" content-width="wide">
    <template #actions>
      <UButton :label="indexLabel" :to="indexUrl" color="neutral" variant="ghost" />
      <UButton v-if="editUrl" :to="editUrl" color="neutral" label="Edit" variant="outline" />
    </template>

    <div class="grid w-full gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
      <div class="min-w-0 space-y-8">
        <slot name="summary" />
        <slot />
      </div>
      <aside class="min-w-0 xl:border-l xl:border-default xl:pl-8">
        <slot name="aside" />
      </aside>
    </div>
  </WorkspacePage>
</template>
