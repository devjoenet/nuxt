<script lang="ts" setup>
  import { Form, usePage } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { index as requestsIndex } from "@/routes/development/requests";
  import { index as roadmapIndex } from "@/routes/development/roadmap";
  import { promote, reject } from "@/routes/development/tasks";
  import { store as requestInformation } from "@/routes/development/tasks/informationRequest";
  import { store as storeLink } from "@/routes/development/tasks/links";
  import { store as storeNote } from "@/routes/development/tasks/notes";
  import { update as updateProgress } from "@/routes/development/tasks/progress";
  import { store as startReview } from "@/routes/development/tasks/review";
  import { update as updateRoadmap } from "@/routes/development/tasks/roadmap";
  import { store as requestTesting } from "@/routes/development/tasks/testRuns";
  import { update as updateTestRun } from "@/routes/development/testRuns";
  import type { AppPageProps } from "@/types";
  import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

  defineOptions({ layout: Layout });
  const props = defineProps<{
    task: DevelopmentTask;
    assignees: DevelopmentOption[];
    priorities: DevelopmentOption[];
    linkTypes: DevelopmentOption[];
    testers: DevelopmentOption[];
  }>();
  const page = usePage<AppPageProps>();
  const toast = useToast();

  watch(
    () => page.props.flash.success,
    (value) => {
      if (value) toast.add({ title: value, color: "success" });
    },
    { immediate: true },
  );

  const canStartReview = computed(() => props.task.can.review && ["submitted", "needs_information"].includes(props.task.status));
  const canRequestInformation = computed(() => props.task.can.review && props.task.status === "submitted");
  const canDecide = computed(() => props.task.can.review && props.task.status === "under_review");
  const isRoadmapTask = computed(() => ["planned", "in_progress", "blocked", "changes_requested"].includes(props.task.status));
  const showActionSidebar = computed(() => props.task.can.review || props.task.can.manage_roadmap || props.task.can.update_progress || props.task.can.request_testing);
  const assigneeOptions = computed(() => [{ label: "Unassigned", value: "" }, ...props.assignees]);
  const progressAction = computed<{ color: "neutral" | "primary" | "warning"; label: string; status: string } | null>(() => {
    if (props.task.status === "planned") return { label: "Start work", status: "in_progress", color: "primary" };
    if (props.task.status === "in_progress") return { label: "Mark blocked", status: "blocked", color: "warning" };
    if (props.task.status === "blocked") return { label: "Resume work", status: "in_progress", color: "primary" };
    if (props.task.status === "changes_requested") return { label: "Resume implementation", status: "in_progress", color: "primary" };

    return null;
  });
  const noteAudiences = [
    { label: "Participants", value: "participants" },
    { label: "Development team only", value: "development_team" },
  ];
  const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
  const isWebReference = (value: string | null): boolean => value?.startsWith("https://") === true || value?.startsWith("http://") === true;
</script>

<template>
  <UDashboardPanel id="development-task-detail">
    <template #header>
      <UDashboardNavbar :title="task.title">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton :label="isRoadmapTask ? 'Roadmap' : 'My requests'" :to="isRoadmapTask ? roadmapIndex().url : requestsIndex().url" color="neutral" variant="ghost" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)]">
        <div class="grid content-start gap-8">
          <section class="grid gap-5">
            <div class="flex flex-wrap items-center gap-2">
              <UBadge
                :color="task.status === 'needs_information' ? 'warning' : task.status === 'under_review' ? 'info' : task.status === 'planned' ? 'success' : 'neutral'"
                :label="task.status_label"
              />
              <UBadge :label="task.request_type_label" color="neutral" variant="subtle" />
              <UBadge :label="task.affected_area_label" color="neutral" variant="subtle" />
            </div>
            <dl class="grid gap-4 border-y border-default py-5 sm:grid-cols-2 xl:grid-cols-4">
              <div>
                <dt class="text-sm text-muted">Submitted by</dt>
                <dd class="font-medium text-highlighted">{{ task.submitter }}</dd>
              </div>
              <div>
                <dt class="text-sm text-muted">Reviewer</dt>
                <dd class="font-medium text-highlighted">{{ task.reviewer ?? "Unassigned" }}</dd>
              </div>
              <div>
                <dt class="text-sm text-muted">Submitted</dt>
                <dd class="font-medium text-highlighted">{{ formatDateTime(task.submitted_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm text-muted">Roadmap</dt>
                <dd class="font-medium text-highlighted">
                  {{ task.assignee ?? "Unassigned" }} · {{ task.priority_label }}
                  <span v-if="task.roadmap_position"> · #{{ task.roadmap_position }}</span>
                </dd>
              </div>
            </dl>
            <div>
              <h2 class="font-semibold text-highlighted">Problem or request</h2>
              <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-muted">{{ task.description }}</p>
            </div>
            <div>
              <h2 class="font-semibold text-highlighted">Desired outcome</h2>
              <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-muted">{{ task.desired_outcome }}</p>
            </div>
            <div>
              <h2 class="font-semibold text-highlighted">Acceptance criteria</h2>
              <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-muted">{{ task.acceptance_criteria }}</p>
            </div>
          </section>

          <section class="grid gap-4">
            <div>
              <h2 class="text-xl font-semibold text-highlighted">Clarification and planning notes</h2>
              <p class="mt-1 text-sm text-muted">Participant notes are shared with the requester. Internal notes stay with the development team.</p>
            </div>

            <Form
              v-if="task.can.add_participant_note || task.can.add_internal_note"
              #default="{ errors, processing }"
              :options="{ preserveScroll: true }"
              class="grid gap-3 rounded-lg bg-elevated p-4"
              reset-on-success
              v-bind="storeNote.form(task.id)"
            >
              <FormTextarea :error="errors.body" label="Add a note" name="body" required />
              <FormSelect
                v-if="task.can.add_internal_note"
                :error="errors.audience"
                :items="noteAudiences"
                default-value="participants"
                label="Audience"
                name="audience"
                required
              />
              <input v-else name="audience" type="hidden" value="participants" />
              <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add note" type="submit" variant="soft" />
            </Form>

            <ol v-if="task.notes?.length" class="grid gap-4" role="list">
              <li v-for="note in task.notes" :key="note.id" class="border-t border-default pt-4">
                <div class="flex flex-wrap items-center gap-2 text-sm text-muted">
                  <span class="font-medium text-highlighted">{{ note.author }}</span>
                  <span aria-hidden="true">·</span>
                  <time>{{ formatDateTime(note.created_at) }}</time>
                  <UBadge v-if="note.audience === 'development_team'" :label="note.audience_label" color="warning" size="sm" variant="subtle" />
                </div>
                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-highlighted">{{ note.body }}</p>
              </li>
            </ol>
            <p v-else class="text-sm text-muted">No notes yet.</p>
          </section>

          <section class="grid gap-4">
            <div>
              <h2 class="text-xl font-semibold text-highlighted">Task links</h2>
              <p class="mt-1 text-sm text-muted">Branches, pull requests, commits, deployments, and supporting references stay attached to this task.</p>
            </div>

            <Form
              v-if="task.can.attach_link"
              #default="{ errors, processing }"
              :options="{ preserveScroll: true }"
              class="grid gap-3 rounded-lg bg-elevated p-4 sm:grid-cols-2"
              reset-on-success
              v-bind="storeLink.form(task.id)"
            >
              <FormSelect :error="errors.type" :items="linkTypes" label="Link type" name="type" required />
              <FormInput :error="errors.label" label="Label" name="label" placeholder="Optional display label" />
              <FormInput :error="errors.url" class="sm:col-span-2" label="URL" name="url" placeholder="https://…" required type="url" />
              <UButton :loading="processing" class="justify-self-end sm:col-span-2" color="neutral" label="Attach link" type="submit" variant="soft" />
            </Form>

            <ul v-if="task.links?.length" class="grid gap-3" role="list">
              <li v-for="link in task.links" :key="link.id" class="flex items-start justify-between gap-4 border-t border-default pt-4">
                <div class="min-w-0">
                  <a :href="link.url" class="font-medium text-primary hover:underline" rel="noreferrer" target="_blank">
                    {{ link.label || link.type_label }}
                    <span class="sr-only">(opens in a new tab)</span>
                  </a>
                  <p class="mt-1 truncate text-sm text-muted">{{ link.url }}</p>
                </div>
                <UBadge :label="link.type_label" color="neutral" size="sm" variant="subtle" />
              </li>
            </ul>
            <p v-else class="text-sm text-muted">No links have been attached.</p>
          </section>

          <section class="grid gap-4">
            <div>
              <h2 class="text-xl font-semibold text-highlighted">User-testing history</h2>
              <p class="mt-1 text-sm text-muted">Every round remains attached to the task, including feedback that caused another implementation pass.</p>
            </div>

            <ol v-if="task.test_runs?.length" class="grid gap-4" role="list">
              <li v-for="testRun in task.test_runs" :key="testRun.id" class="rounded-lg border border-default p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p class="font-medium text-highlighted">{{ testRun.tester }}</p>
                    <p class="mt-1 text-sm text-muted">Requested by {{ testRun.requested_by }} · {{ formatDateTime(testRun.requested_at) }}</p>
                  </div>
                  <UBadge
                    :color="testRun.status === 'accepted' ? 'success' : testRun.status === 'changes_requested' ? 'warning' : 'info'"
                    :label="testRun.status_label"
                    variant="subtle"
                  />
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                  <div>
                    <h3 class="text-sm font-semibold text-highlighted">Instructions</h3>
                    <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-muted">{{ testRun.instructions }}</p>
                  </div>
                  <div v-if="testRun.build_reference">
                    <h3 class="text-sm font-semibold text-highlighted">Build reference</h3>
                    <a
                      v-if="isWebReference(testRun.build_reference)"
                      :href="testRun.build_reference"
                      class="mt-1 inline-block break-all text-sm text-primary hover:underline"
                      rel="noreferrer"
                      target="_blank"
                    >
                      {{ testRun.build_reference }}
                      <span class="sr-only">(opens in a new tab)</span>
                    </a>
                    <p v-else class="mt-1 break-all text-sm text-muted">{{ testRun.build_reference }}</p>
                  </div>
                </div>

                <div v-if="testRun.feedback" class="mt-4 border-t border-default pt-4">
                  <h3 class="text-sm font-semibold text-highlighted">Feedback</h3>
                  <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-muted">{{ testRun.feedback }}</p>
                </div>

                <Form
                  v-if="testRun.can.record_result"
                  #default="{ errors, processing }"
                  class="mt-4 grid gap-3 border-t border-default pt-4"
                  v-bind="updateTestRun.form(testRun.id)"
                >
                  <FormTextarea :error="errors.feedback" label="Testing feedback" name="feedback" />
                  <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
                  <div class="flex flex-wrap justify-end gap-3">
                    <UButton :disabled="processing" color="warning" label="Request changes" name="status" type="submit" value="changes_requested" variant="soft" />
                    <UButton :loading="processing" label="Accept and complete" name="status" type="submit" value="accepted" />
                  </div>
                </Form>
              </li>
            </ol>
            <p v-else class="text-sm text-muted">No user-testing rounds have been requested.</p>
          </section>
        </div>

        <aside v-if="showActionSidebar" class="grid content-start gap-5">
          <UPageCard v-if="canStartReview" title="Review this request" variant="subtle">
            <p class="text-sm leading-6 text-muted">Starting review assigns the request to you. You can then add internal notes, promote it, or reject it.</p>
            <Form #default="{ processing }" class="mt-4" v-bind="startReview.form(task.id)">
              <UButton :loading="processing" block label="Start review" type="submit" />
            </Form>
          </UPageCard>

          <UPageCard v-if="canRequestInformation" title="Request more information" variant="subtle">
            <Form #default="{ errors, processing }" class="mt-4 grid gap-3" reset-on-success v-bind="requestInformation.form(task.id)">
              <FormTextarea :error="errors.body" label="What needs clarification?" name="body" required />
              <UButton :loading="processing" class="justify-self-end" color="neutral" label="Send request" type="submit" variant="soft" />
            </Form>
          </UPageCard>

          <UPageCard v-if="canDecide" title="Review decision" variant="subtle">
            <Form #default="{ processing }" v-bind="promote.form(task.id)">
              <UButton :loading="processing" block icon="i-ph-map-pin" label="Add to roadmap" type="submit" />
            </Form>
            <div class="my-5 border-t border-default" />
            <Form #default="{ errors, processing }" class="grid gap-3" v-bind="reject.form(task.id)">
              <FormTextarea :error="errors.body" label="Reason for rejection" name="body" required />
              <UButton :loading="processing" class="justify-self-end" color="error" label="Reject request" type="submit" variant="soft" />
            </Form>
          </UPageCard>

          <UPageCard v-if="task.can.manage_roadmap && isRoadmapTask" title="Roadmap details" variant="subtle">
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="updateRoadmap.form(task.id)">
              <FormSelect :default-value="task.priority" :error="errors.priority" :items="priorities" label="Priority" name="priority" required />
              <FormSelect :default-value="task.assignee_id ?? ''" :error="errors.assignee_id" :items="assigneeOptions" label="Assignee" name="assignee_id" />
              <FormInput
                :default-value="task.roadmap_position ?? 1"
                :error="errors.roadmap_position"
                label="Roadmap position"
                max="9999"
                min="1"
                name="roadmap_position"
                required
                type="number"
              />
              <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
              <UButton :loading="processing" class="justify-self-end" label="Save roadmap details" type="submit" />
            </Form>
          </UPageCard>

          <UPageCard v-if="task.can.request_testing" title="Request user testing" variant="subtle">
            <p class="text-sm leading-6 text-muted">Assign a tester and describe the exact build and behavior they should verify.</p>
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="mt-4 grid gap-3" v-bind="requestTesting.form(task.id)">
              <FormSelect :default-value="task.assignee_id ?? ''" :error="errors.tester_id" :items="testers" label="Tester" name="tester_id" required />
              <FormTextarea :error="errors.instructions" label="Testing instructions" name="instructions" required />
              <FormInput :error="errors.build_reference" label="Build or deployment reference" name="build_reference" placeholder="Deployment URL, commit, or build identifier" />
              <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
              <UButton :loading="processing" class="justify-self-end" icon="i-ph-test-tube" label="Send to testing" type="submit" />
            </Form>
          </UPageCard>

          <UPageCard v-if="task.can.update_progress && progressAction" title="Implementation progress" variant="subtle">
            <p class="text-sm leading-6 text-muted">Status changes use the same lifecycle action for the web workspace and future agent tools.</p>
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="mt-4 grid gap-3" v-bind="updateProgress.form(task.id)">
              <input :value="progressAction.status" name="status" type="hidden" />
              <UAlert v-if="errors.assignee_id || errors.status" :description="errors.assignee_id || errors.status" color="error" variant="subtle" />
              <UButton :color="progressAction.color" :label="progressAction.label" :loading="processing" block type="submit" />
            </Form>
          </UPageCard>
        </aside>
      </div>
    </template>
  </UDashboardPanel>
</template>
