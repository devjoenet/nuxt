<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { cn } from "@/lib/utils";
import { promote, reject } from "@/routes/development/tasks";
import { store as requestInformation } from "@/routes/development/tasks/informationRequest";
import { store as startReview } from "@/routes/development/tasks/review";

const props = defineProps<{
  taskId: string;
  canStartReview: boolean;
  canRequestInformation: boolean;
  canDecide: boolean;
  class?: string;
}>();
</script>

<template>
  <div :class="cn('divide-y divide-default border-t border-default', props.class)">
    <section v-if="canStartReview" aria-labelledby="start-review-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="start-review-heading" class="font-medium text-highlighted">Review this request</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Starting review assigns the request to you and unlocks the review decision.</p>
      </div>
      <Form v-slot="{ processing }" v-bind="startReview.form(taskId)">
        <UButton :loading="processing" block label="Start review" size="sm" type="submit" />
      </Form>
    </section>

    <section v-if="canDecide" aria-labelledby="review-decision-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="review-decision-heading" class="font-medium text-highlighted">Review decision</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Promote approved work to the roadmap or record why it should stop here.</p>
      </div>
      <Form v-slot="{ processing }" v-bind="promote.form(taskId)">
        <UButton :loading="processing" block icon="i-ph-map-pin" label="Add to roadmap" size="sm" type="submit" />
      </Form>
      <USeparator />
      <Form v-slot="{ errors, processing }" class="grid gap-3" v-bind="reject.form(taskId)">
        <FormTextarea :error="errors.body" label="Reason for rejection" name="body" required />
        <UButton :loading="processing" class="justify-self-end" color="error" label="Reject request" size="sm" type="submit" variant="soft" />
      </Form>
    </section>

    <section v-if="canRequestInformation" aria-labelledby="request-information-heading" class="grid gap-4 p-5 sm:p-6">
      <div class="grid gap-1">
        <h3 id="request-information-heading" class="font-medium text-highlighted">Request more information</h3>
        <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Ask for the missing context before the request enters review.</p>
      </div>
      <Form v-slot="{ errors, processing }" class="grid gap-3" reset-on-success v-bind="requestInformation.form(taskId)">
        <FormTextarea :error="errors.body" label="What needs clarification?" name="body" required />
        <UButton :loading="processing" class="justify-self-end" color="neutral" label="Send request" size="sm" type="submit" variant="soft" />
      </Form>
    </section>
  </div>
</template>
