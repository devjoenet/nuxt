<script setup lang="ts">
  import { Form } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import { cn } from "@/lib/utils";
  import { promote, reject } from "@/routes/development/tasks";
  import { store as requestInformation } from "@/routes/development/tasks/informationRequest";
  import { update as updateProgress } from "@/routes/development/tasks/progress";
  import { store as startReview } from "@/routes/development/tasks/review";
  import { store as requestTesting } from "@/routes/development/tasks/testRuns";
  import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

  const props = defineProps<{
    task: DevelopmentTask;
    testers: DevelopmentOption[];
    class?: string;
  }>();

  const canStartReview = computed(() => props.task.can.review && ["submitted", "needs_information"].includes(props.task.status));
  const canRequestInformation = computed(() => props.task.can.review && props.task.status === "submitted");
  const canDecide = computed(() => props.task.can.review && props.task.status === "under_review");
  const progressAction = computed<{ color: "neutral" | "primary" | "warning"; label: string; status: string } | null>(() => {
    if (props.task.status === "planned") return { label: "Start work", status: "in_progress", color: "primary" };
    if (props.task.status === "in_progress") return { label: "Mark blocked", status: "blocked", color: "warning" };
    if (props.task.status === "blocked") return { label: "Resume work", status: "in_progress", color: "primary" };
    if (props.task.status === "changes_requested") return { label: "Resume implementation", status: "in_progress", color: "primary" };

    return null;
  });
  const showActions = computed(
    () =>
      canStartReview.value || canRequestInformation.value || canDecide.value || (props.task.can.update_progress && Boolean(progressAction.value)) || props.task.can.request_testing,
  );
</script>

<template>
  <div v-if="showActions" :class="cn('divide-y divide-default border-t border-default', props.class)">
    <section v-if="canStartReview" aria-labelledby="start-review-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="start-review-heading" class="font-medium text-highlighted">Review this request</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Starting review assigns the request to you and unlocks the review decision.</p>
      </div>
      <Form #default="{ processing }" v-bind="startReview.form(task.id)">
        <UButton :loading="processing" block label="Start review" size="sm" type="submit" />
      </Form>
    </section>

    <section v-if="canDecide" aria-labelledby="review-decision-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="review-decision-heading" class="font-medium text-highlighted">Review decision</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Promote approved work to the roadmap or record why it should stop here.</p>
      </div>
      <Form #default="{ processing }" v-bind="promote.form(task.id)">
        <UButton :loading="processing" block icon="i-ph-map-pin" label="Add to roadmap" size="sm" type="submit" />
      </Form>
      <USeparator />
      <Form #default="{ errors, processing }" class="grid gap-3" v-bind="reject.form(task.id)">
        <FormTextarea :error="errors.body" label="Reason for rejection" name="body" required />
        <UButton :loading="processing" class="justify-self-end" color="error" label="Reject request" size="sm" type="submit" variant="soft" />
      </Form>
    </section>

    <section v-if="task.can.update_progress && progressAction" aria-labelledby="implementation-progress-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="implementation-progress-heading" class="font-medium text-highlighted">Implementation progress</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Move the task to its next meaningful implementation state.</p>
      </div>
      <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="updateProgress.form(task.id)">
        <input :value="progressAction.status" name="status" type="hidden" />
        <UAlert v-if="errors.assignee_id || errors.status" :description="errors.assignee_id || errors.status" color="error" variant="subtle" />
        <UButton :color="progressAction.color" :label="progressAction.label" :loading="processing" block size="sm" type="submit" />
      </Form>
    </section>

    <section v-if="canRequestInformation" aria-labelledby="request-information-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="request-information-heading" class="font-medium text-highlighted">Request more information</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Ask for the missing context before the request enters review.</p>
      </div>
      <Form #default="{ errors, processing }" class="grid gap-3" reset-on-success v-bind="requestInformation.form(task.id)">
        <FormTextarea :error="errors.body" label="What needs clarification?" name="body" required />
        <UButton :loading="processing" class="justify-self-end" color="neutral" label="Send request" size="sm" type="submit" variant="soft" />
      </Form>
    </section>

    <section v-if="task.can.request_testing" aria-labelledby="request-testing-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="request-testing-heading" class="font-medium text-highlighted">Request user testing</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Assign a tester and describe the exact build and behavior they should verify.</p>
      </div>
      <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="requestTesting.form(task.id)">
        <FormSelect :default-value="task.assignee_id ?? ''" :error="errors.tester_id" :items="testers" label="Tester" name="tester_id" required />
        <FormTextarea :error="errors.instructions" label="Testing instructions" name="instructions" required />
        <FormInput :error="errors.build_reference" label="Build or deployment reference" name="build_reference" placeholder="Deployment URL, commit, or build identifier" />
        <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
        <UButton :loading="processing" class="justify-self-end" color="neutral" icon="i-ph-test-tube" label="Send to testing" size="sm" type="submit" variant="soft" />
      </Form>
    </section>
  </div>
</template>
