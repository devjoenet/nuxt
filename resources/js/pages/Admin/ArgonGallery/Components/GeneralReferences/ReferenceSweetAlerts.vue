<script setup lang="ts">
import { Button } from "@/components/Ui/Button";
import { ref } from "vue";

const open = ref(false);
const selected = ref({ title: "A basic message", description: "This is the accessible Nuxt UI replacement for the source SweetAlert runtime." });
const alerts = [
  { title: "A basic message", description: "A concise informational dialog." },
  { title: "Good job!", description: "The success state confirms an inert gallery action." },
  { title: "Are you sure?", description: "The warning state presents clear confirm and cancel actions." },
  { title: "Enter your email", description: "The input variant keeps its label and validation relationship." },
];
function show(alert: (typeof alerts)[number]): void {
  selected.value = alert;
  open.value = true;
}
</script>

<template>
  <div class="grid gap-5">
    <UAlert color="info" description="SweetAlert is deliberately replaced by the existing accessible Nuxt UI modal boundary." title="Dependency divergence" variant="subtle" />
    <div class="grid gap-4 sm:grid-cols-2">
      <button
        v-for="(alert, index) in alerts"
        :key="alert.title"
        class="grid min-h-32 gap-3 rounded-xl border border-content-border p-5 text-start focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
        :data-testid="`sweet-alert-${index}`"
        type="button"
        @click="show(alert)"
      >
        <strong class="text-lg font-semibold text-content-heading">{{ alert.title }}</strong
        ><span class="text-base text-content-body sm:text-sm">{{ alert.description }}</span
        ><span :class="index === 0 ? 'text-primary' : 'text-content-subtle'" class="font-medium">Try dialog</span>
      </button>
    </div>
    <UModal
      v-model:open="open"
      :description="selected.description"
      :title="selected.title"
      :ui="{
        content: 'bg-white dark:bg-surface-elevated',
        title: 'text-slate-800 dark:text-white',
        description: 'text-slate-600 dark:text-grey-300',
        footer: 'justify-end',
      }"
      ><template #footer><Button label="Cancel" tone="neutral" variant="outline" @click="open = false" /><Button class="dark:[--ui-tone:var(--ui-color-primary-700)]" label="Confirm" @click="open = false" /></template
    ></UModal>
  </div>
</template>
