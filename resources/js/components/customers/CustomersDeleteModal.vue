<script setup lang="ts">
  import { ref } from "vue";

  withDefaults(
    defineProps<{
      count?: number;
    }>(),
    {
      count: 0,
    },
  );

  const open = ref(false);

  async function onSubmit() {
    await new Promise((resolve) => setTimeout(resolve, 1000));
    open.value = false;
  }
</script>

<template>
  <UModal v-model:open="open" :description="`Are you sure? This action cannot be undone.`" :title="`Delete ${count} customer${(count ?? 0 > 1) ? 's' : ''}`">
    <slot />

    <template #body>
      <div class="flex justify-end gap-2">
        <UButton label="Cancel" color="neutral" variant="subtle" @click="open = false" />
        <UButton label="Delete" color="error" variant="solid" loading-auto @click="onSubmit" />
      </div>
    </template>
  </UModal>
</template>
