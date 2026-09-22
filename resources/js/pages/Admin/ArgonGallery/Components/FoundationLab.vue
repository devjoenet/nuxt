<script setup lang="ts">
import { FormSelect } from "@/components/Ui/FormSelect";
import { ref } from "vue";

type FoundationGroup = "color" | "surface" | "typography" | "motion";
const group = ref<FoundationGroup>("color");
const groups = [
  { label: "Color", value: "color" },
  { label: "Surfaces", value: "surface" },
  { label: "Typography", value: "typography" },
  { label: "Motion", value: "motion" },
];
const samples: Record<FoundationGroup, { label: string; class: string; value: string }[]> = {
  color: [
    { label: "Primary", class: "bg-primary", value: "--ui-primary" },
    { label: "Secondary", class: "bg-secondary", value: "--ui-secondary" },
    { label: "Success", class: "bg-success", value: "--ui-success" },
    { label: "Warning", class: "bg-warning", value: "--ui-warning" },
    { label: "Error", class: "bg-error", value: "--ui-error" },
  ],
  surface: [
    { label: "Canvas", class: "bg-surface-canvas", value: "--surface-canvas" },
    { label: "Elevated", class: "bg-surface-elevated", value: "--surface-elevated" },
    { label: "Subtle", class: "bg-surface-subtle", value: "--surface-subtle" },
  ],
  typography: [
    { label: "Display", class: "text-3xl font-semibold", value: "Open Sans display" },
    { label: "Body", class: "text-base", value: "Open Sans body" },
    { label: "Caption", class: "text-sm", value: "Compact metadata" },
  ],
  motion: [
    { label: "Fast", class: "duration-150", value: "150 ms" },
    { label: "Default", class: "duration-200", value: "200 ms" },
    { label: "Reduced", class: "motion-reduce:transition-none", value: "No motion" },
  ],
};
</script>

<template>
  <section aria-labelledby="foundation-lab-title" class="grid gap-5">
    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_14rem] sm:items-end">
      <div>
        <h3 id="foundation-lab-title" class="text-lg font-semibold text-balance text-content-heading">Foundation tokens</h3>
        <p class="text-base text-pretty text-content-body sm:text-sm">Choose a token family to inspect its semantic application.</p>
      </div>
      <FormSelect v-model="group" :items="groups" label="Token family" name="foundation-group" />
    </div>
    <div class="@container">
      <div class="grid gap-3 @sm:grid-cols-2 @3xl:grid-cols-4">
        <article v-for="sample in samples[group]" :key="sample.label" class="grid gap-3 rounded-lg border border-content-border p-3">
          <div :class="sample.class" class="h-16 rounded-md border border-content-border" />
          <div>
            <h4 class="font-medium text-content-heading">{{ sample.label }}</h4>
            <p class="text-base text-content-subtle sm:text-sm">{{ sample.value }}</p>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
