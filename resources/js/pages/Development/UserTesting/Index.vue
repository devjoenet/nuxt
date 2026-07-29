<script lang="ts" setup>
  import { Form, Head, usePage } from "@inertiajs/vue3";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { show } from "@/routes/development/tasks";
  import { update as updateTestRun } from "@/routes/development/testRuns";
  import type { AppPageProps } from "@/types";
  import type { DevelopmentTestRun } from "@/types/development";

  defineOptions({ layout: Layout });

  defineProps<{ testRuns: DevelopmentTestRun[] }>();

  const page = usePage<AppPageProps>();
  const toast = useToast();

  watch(
    () => page.props.flash.success,
    (value) => {
      if (value) toast.add({ title: value, color: "success" });
    },
    { immediate: true },
  );

  const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
  const isWebReference = (value: string | null): boolean => value?.startsWith("https://") === true || value?.startsWith("http://") === true;
</script>

<template>
  <Head title="User testing" />

  <UDashboardPanel id="development-user-testing">
    <template #header>
      <UDashboardNavbar description="Run task-specific test rounds and preserve every feedback cycle." title="User testing">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-6">
        <UPageCard variant="subtle">
          <p class="text-xs font-bold tracking-[0.22em] text-highlighted uppercase">Release gate</p>
          <h1 class="mt-3 text-3xl font-semibold tracking-tight text-highlighted">Test the requested outcome, not a generic page checklist.</h1>
          <p class="mt-3 max-w-3xl leading-7 text-muted">
            Each round keeps its instructions, build reference, tester, and result. Accepted work is completed and archived; requested changes return to implementation without
            erasing the previous round.
          </p>
        </UPageCard>

        <UEmpty v-if="testRuns.length === 0" description="There are no task rounds waiting for your result." icon="i-ph-check-circle" title="The testing queue is clear" />

        <ol v-else class="grid gap-5" role="list">
          <li v-for="testRun in testRuns" :key="testRun.id">
            <UPageCard variant="subtle">
              <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(22rem,0.8fr)]">
                <div class="grid content-start gap-5">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <p class="text-xs font-bold tracking-[0.16em] text-primary uppercase">{{ testRun.task?.affected_area_label }}</p>
                      <h2 class="mt-2 text-xl font-semibold text-highlighted">{{ testRun.task?.title ?? "Archived task" }}</h2>
                    </div>
                    <UBadge :label="testRun.status_label" color="info" variant="subtle" />
                  </div>

                  <dl class="grid gap-3 border-y border-default py-4 text-sm sm:grid-cols-3">
                    <div>
                      <dt class="text-muted">Tester</dt>
                      <dd class="font-medium text-highlighted">{{ testRun.tester }}</dd>
                    </div>
                    <div>
                      <dt class="text-muted">Requested by</dt>
                      <dd class="font-medium text-highlighted">{{ testRun.requested_by }}</dd>
                    </div>
                    <div>
                      <dt class="text-muted">Requested</dt>
                      <dd class="font-medium text-highlighted">{{ formatDateTime(testRun.requested_at) }}</dd>
                    </div>
                  </dl>

                  <div>
                    <h3 class="font-semibold text-highlighted">Testing instructions</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-muted">{{ testRun.instructions }}</p>
                  </div>

                  <div v-if="testRun.task">
                    <h3 class="font-semibold text-highlighted">Acceptance criteria</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-muted">{{ testRun.task.acceptance_criteria }}</p>
                  </div>

                  <div v-if="testRun.build_reference">
                    <h3 class="font-semibold text-highlighted">Build or deployment reference</h3>
                    <a
                      v-if="isWebReference(testRun.build_reference)"
                      :href="testRun.build_reference"
                      class="mt-2 inline-block break-all text-sm text-primary hover:underline"
                      rel="noreferrer"
                      target="_blank"
                    >
                      {{ testRun.build_reference }}
                      <span class="sr-only">(opens in a new tab)</span>
                    </a>
                    <p v-else class="mt-2 break-all text-sm text-muted">{{ testRun.build_reference }}</p>
                  </div>

                  <UButton v-if="testRun.task" :to="show(testRun.task.id).url" class="justify-self-start" color="neutral" label="Open task workspace" variant="ghost" />
                </div>

                <Form
                  v-if="testRun.can.record_result"
                  #default="{ errors, processing }"
                  class="grid content-start gap-4 rounded-lg bg-elevated p-5"
                  v-bind="updateTestRun.form(testRun.id)"
                >
                  <div>
                    <h3 class="font-semibold text-highlighted">Record this round</h3>
                    <p class="mt-1 text-sm leading-6 text-muted">Feedback is optional when accepting and required when requesting changes.</p>
                  </div>
                  <FormTextarea :error="errors.feedback" label="Feedback" name="feedback" placeholder="What passed, or what needs another pass?" />
                  <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
                  <div class="flex flex-wrap justify-end gap-3">
                    <UButton :disabled="processing" color="warning" label="Request changes" name="status" type="submit" value="changes_requested" variant="soft" />
                    <UButton :loading="processing" label="Accept and complete" name="status" type="submit" value="accepted" />
                  </div>
                </Form>
              </div>
            </UPageCard>
          </li>
        </ol>
      </div>
    </template>
  </UDashboardPanel>
</template>
