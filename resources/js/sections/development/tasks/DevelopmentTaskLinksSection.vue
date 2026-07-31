<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import { store as storeLink } from "@/routes/development/tasks/links";
import DevelopmentTaskSectionCard from "@/sections/development/tasks/DevelopmentTaskSectionCard.vue";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

defineProps<{
  task: DevelopmentTask;
  linkTypes: DevelopmentOption[];
  class?: string;
}>();
</script>

<template>
  <DevelopmentTaskSectionCard :class="$props.class" description="Branches, pull requests, commits, deployments, and supporting references stay attached to this task." eyebrow="03 / Delivery" heading-id="task-links-heading" title="Task links" tone="secondary">
    <Form v-if="task.can.attach_link" #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3 rounded-lg bg-elevated/50 p-4 ring ring-default sm:grid-cols-2 sm:p-5" reset-on-success v-bind="storeLink.form(task.id)">
      <FormSelect :error="errors.type" :items="linkTypes" label="Link type" name="type" required />
      <FormInput :error="errors.label" label="Label" name="label" placeholder="Optional display label" />
      <FormInput :error="errors.url" class="sm:col-span-2" label="URL" name="url" placeholder="https://…" required type="url" />
      <UButton :loading="processing" class="justify-self-end sm:col-span-2" color="neutral" icon="i-ph-link" label="Attach link" size="sm" type="submit" variant="soft" />
    </Form>

    <ul v-if="task.links?.length" class="divide-y divide-default overflow-hidden rounded-lg bg-default ring ring-default" role="list">
      <li v-for="link in task.links" :key="link.id" class="flex items-start justify-between gap-4 p-4">
        <a :href="link.url" class="group grid min-w-0 gap-1" rel="noreferrer" target="_blank">
          <p class="flex items-center gap-2 font-medium text-highlighted underline decoration-primary/50 underline-offset-4">
            {{ link.label || link.type_label }}
            <UIcon class="size-4 shrink-0" name="i-ph-arrow-square-out" />
            <span class="sr-only">(opens in a new tab)</span>
          </p>
          <p class="truncate text-base/7 text-muted sm:text-sm/6">{{ link.url }}</p>
        </a>
        <UBadge :label="link.type_label" class="shrink-0" color="neutral" variant="subtle" />
      </li>
    </ul>
    <UAlert
      v-else
      color="secondary"
      description="Attach the first working branch, pull request, deployment, or supporting document."
      icon="i-ph-link-break"
      title="No delivery references"
      :ui="{ description: 'text-base/7 text-pretty sm:text-sm/6', icon: 'size-12', title: 'text-sm font-bold' }"
      variant="subtle"
    />
  </DevelopmentTaskSectionCard>
</template>
