<script lang="ts" setup>
import { computed } from "vue";
import { Form } from "@inertiajs/vue3";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { update } from "@/routes/development/userTesting";

type ReviewStatus = "Needs eyes" | "In Development" | "Needs verification" | "Reviewed" | "Needs Approval";

interface UserTestingPage {
  id: number;
  title: string;
  roadmap_task_number: number | null;
  status: ReviewStatus;
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

const props = defineProps<{ page: UserTestingPage }>();

const statusColors: Record<ReviewStatus, "warning" | "info" | "success" | "primary"> = {
  "Needs eyes": "warning",
  "In Development": "info",
  "Needs verification": "info",
  Reviewed: "success",
  "Needs Approval": "primary",
};

const statusColor = computed(() => statusColors[props.page.status]);
</script>

<template>
  <AppPageCard :variant="statusColor" class="h-full">
    <Form #default="{ errors, processing }" class="flex h-full flex-col gap-4" v-bind="update.form(page.id)">
      <div class="flex items-start justify-between gap-4">
        <div>
          <a v-if="page.url" :href="page.url" class="text-sm font-semibold text-highlighted hover:text-primary hover:underline" rel="noopener noreferrer" target="_blank">
            {{ page.title }}
            <span v-if="page.roadmap_task_number" class="ms-1 text-xs font-normal text-muted">(Task {{ page.roadmap_task_number }})</span>
            <span aria-hidden="true" class="ms-1 text-xs text-muted">↗</span>
          </a>
          <p v-else class="text-sm font-semibold text-muted">
            {{ page.title }}
            <span v-if="page.roadmap_task_number" class="ms-1 text-xs font-normal">(Task {{ page.roadmap_task_number }})</span>
          </p>
          <p class="mt-1 font-mono text-xs text-muted">{{ page.path }}</p>
        </div>
        <UBadge :color="statusColor" :label="page.status" variant="subtle" />
      </div>

      <FormTextarea :default-value="page.notes ?? undefined" :error="errors.notes" :rows="4" label="Notes, feedback, or changes" name="notes" />

      <div class="mt-auto flex flex-wrap items-center justify-between gap-3">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
          <input :checked="page.tested" class="size-4 rounded border-default text-primary focus:ring-primary" name="tested" type="checkbox" value="1" />
          <span>I tested this page</span>
        </label>
        <div class="flex flex-wrap items-center gap-3">
          <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
            <input :checked="page.roadmap_task" class="size-4 rounded border-default text-primary focus:ring-primary" name="roadmap_task" type="checkbox" value="1" />
            <span>Added feedback to roadmap</span>
          </label>
          <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
            <input class="size-4 rounded border-default text-primary focus:ring-primary" name="approved" type="checkbox" value="1" />
            <span>Approve and remove from queue</span>
          </label>
          <UButton :loading="processing" label="Save review" size="sm" type="submit" />
        </div>
      </div>

      <p v-if="page.tested_by && page.tested_at" class="text-xs text-muted">Last reviewed by {{ page.tested_by }} · {{ page.tested_at }}</p>
      <p v-if="page.roadmap_task_at" class="text-xs text-muted">Roadmap task recorded · {{ page.roadmap_task_at }}</p>
    </Form>
  </AppPageCard>
</template>
