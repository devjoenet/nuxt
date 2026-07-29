<script lang="ts" setup>
  import { Form, usePage } from "@inertiajs/vue3";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { index as requestsIndex } from "@/routes/development/requests";
  import { promote, reject } from "@/routes/development/tasks";
  import { store as requestInformation } from "@/routes/development/tasks/informationRequest";
  import { store as storeNote } from "@/routes/development/tasks/notes";
  import { store as startReview } from "@/routes/development/tasks/review";
  import type { AppPageProps } from "@/types";
  import type { DevelopmentTask } from "@/types/development";

  defineOptions({ layout: Layout });
  const props = defineProps<{ task: DevelopmentTask }>();
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
  const noteAudiences = [
    { label: "Participants", value: "participants" },
    { label: "Development team only", value: "development_team" },
  ];
  const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
</script>

<template>
  <UDashboardPanel id="development-task-detail">
    <template #header>
      <UDashboardNavbar :title="task.title">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><UButton :to="requestsIndex().url" color="neutral" label="My requests" variant="ghost" /></template>
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
            <dl class="grid gap-4 border-y border-default py-5 sm:grid-cols-3">
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
        </div>

        <aside v-if="task.can.review" class="grid content-start gap-5">
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
        </aside>
      </div>
    </template>
  </UDashboardPanel>
</template>
