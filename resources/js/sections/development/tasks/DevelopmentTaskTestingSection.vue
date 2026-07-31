<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import FormTextarea from "@/components/FormTextarea.vue";
import { update as updateTestRun } from "@/routes/development/testRuns";
import DevelopmentTaskSectionCard from "@/sections/development/tasks/DevelopmentTaskSectionCard.vue";
import type { DevelopmentTask } from "@/types/development";

defineProps<{
  task: DevelopmentTask;
  class?: string;
}>();

const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
const isWebReference = (value: string | null): boolean => value?.startsWith("https://") === true || value?.startsWith("http://") === true;
const testRunStatusColor = (status: string): "success" | "warning" | "info" => {
  if (status === "accepted") return "success";
  if (status === "changes_requested") return "warning";

  return "info";
};
</script>

<template>
  <DevelopmentTaskSectionCard :class="$props.class" description="Every round remains attached to the task, including feedback that caused another implementation pass." eyebrow="04 / Verification" heading-id="task-testing-heading" title="User testing history" tone="warning">
    <ol v-if="task.test_runs?.length" class="grid gap-4" role="list">
      <li v-for="testRun in task.test_runs" :key="testRun.id" class="grid gap-5 rounded-lg bg-default/70 p-5 ring ring-accented">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="grid gap-1">
            <p class="font-medium text-highlighted">{{ testRun.tester }}</p>
            <p class="text-base/7 text-muted sm:text-sm/6">
              Requested by {{ testRun.requested_by }} · <span class="tabular-nums">{{ formatDateTime(testRun.requested_at) }}</span>
            </p>
          </div>
          <UBadge :color="testRunStatusColor(testRun.status)" :label="testRun.status_label" variant="subtle" />
        </div>

        <div class="@container">
          <div class="grid gap-4 @2xl:grid-cols-2">
            <div class="grid content-start gap-1">
              <h3 class="font-medium text-highlighted">Instructions</h3>
              <p class="whitespace-pre-wrap text-base/7 text-pretty text-muted sm:text-sm/6">{{ testRun.instructions }}</p>
            </div>
            <div v-if="testRun.build_reference" class="grid content-start gap-1">
              <h3 class="font-medium text-highlighted">Build reference</h3>
              <a v-if="isWebReference(testRun.build_reference)" :href="testRun.build_reference" class="break-all hover:underline" rel="noreferrer" target="_blank">
                <p class="text-base/7 text-highlighted underline decoration-primary/50 underline-offset-4 sm:text-sm/6">
                  {{ testRun.build_reference }}
                </p>
                <span class="sr-only">(opens in a new tab)</span>
              </a>
              <p v-else class="break-all text-base/7 text-muted sm:text-sm/6">{{ testRun.build_reference }}</p>
            </div>
          </div>
        </div>

        <UAlert v-if="testRun.feedback" color="info" :description="testRun.feedback" title="Feedback" :ui="{ description: 'whitespace-pre-wrap text-base/7 text-pretty sm:text-sm/6', icon: 'size-4' }" variant="subtle" />

        <Form v-if="testRun.can.record_result" #default="{ errors, processing }" class="grid gap-3 border-t border-default pt-5" v-bind="updateTestRun.form(testRun.id)">
          <FormTextarea :error="errors.feedback" label="Testing feedback" name="feedback" />
          <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
          <div class="flex flex-wrap justify-end gap-3">
            <UButton :disabled="processing" color="warning" label="Request changes" name="status" size="sm" type="submit" value="changes_requested" variant="soft" />
            <UButton :loading="processing" color="success" label="Accept and complete" name="status" size="sm" type="submit" value="accepted" variant="soft" />
          </div>
        </Form>
      </li>
    </ol>
    <UAlert
      v-else
      color="warning"
      description="Testing instructions, builds, and outcomes will collect here when verification begins."
      icon="i-ph-test-tube"
      title="No user-testing rounds"
      :ui="{ description: 'text-base/7 text-pretty sm:text-sm/6', icon: 'size-12', title: 'text-sm font-bold' }"
      variant="subtle"
    />
  </DevelopmentTaskSectionCard>
</template>
