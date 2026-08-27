<script lang="ts" setup>
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { index, store } from "@/routes/development/requests";
import type { DevelopmentOption } from "@/types/development";

defineProps<{ requestTypes: DevelopmentOption[]; affectedAreas: DevelopmentOption[] }>();
</script>

<template>
  <WorkspaceFormPage
    id="development-request-create"
    :action="store()"
    :cancel-to="index().url"
    actions-bordered
    section-description="Clear outcomes and acceptance criteria make review faster and leave less room for interpretive acrobatics later."
    section-title="Describe the problem before prescribing the patch."
    submit-icon="i-ph-paper-plane-tilt"
    submit-label="Submit request"
    title="Submit a development request"
  >
    <template #actions>
      <UButton :to="index().url" color="neutral" label="My requests" variant="ghost" />
    </template>

    <template #default="{ errors }">
      <FormInput :error="errors.title" label="Title" name="title" required />
      <div class="grid gap-5 sm:grid-cols-2">
        <FormSelect :error="errors.request_type" :items="requestTypes" label="Request type" name="request_type" required />
        <FormSelect :error="errors.affected_area" :items="affectedAreas" label="Affected area" name="affected_area" required />
      </div>
      <FormTextarea :error="errors.description" description="What is happening now, who it affects, and why the current behavior is a problem." label="Problem or request" name="description" required />
      <FormTextarea :error="errors.desired_outcome" description="Describe the result that would make this request successful." label="Desired outcome" name="desired_outcome" required />
      <FormTextarea :error="errors.acceptance_criteria" description="List concrete, testable conditions. One criterion per line is fine." label="Acceptance criteria" name="acceptance_criteria" required />
    </template>
  </WorkspaceFormPage>
</template>
