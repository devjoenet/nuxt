<script lang="ts" setup>
  import { Form } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { index, store } from "@/routes/development/requests";
  import type { DevelopmentOption } from "@/types/development";

  defineOptions({ layout: Layout });
  defineProps<{ requestTypes: DevelopmentOption[]; affectedAreas: DevelopmentOption[] }>();
</script>

<template>
  <UDashboardPanel id="development-request-create">
    <template #header>
      <UDashboardNavbar title="Submit a development request">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><UButton :to="index().url" color="neutral" label="My requests" variant="ghost" /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="mx-auto w-full max-w-3xl">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold tracking-tight text-highlighted">Describe the problem before prescribing the patch.</h1>
          <p class="mt-2 text-sm leading-6 text-muted">Clear outcomes and acceptance criteria make review faster and leave less room for interpretive acrobatics later.</p>
        </div>

        <Form #default="{ errors, processing }" class="grid gap-5" v-bind="store.form()">
          <FormInput :error="errors.title" label="Title" name="title" required />
          <div class="grid gap-5 sm:grid-cols-2">
            <FormSelect :error="errors.request_type" :items="requestTypes" label="Request type" name="request_type" required />
            <FormSelect :error="errors.affected_area" :items="affectedAreas" label="Affected area" name="affected_area" required />
          </div>
          <FormTextarea
            :error="errors.description"
            description="What is happening now, who it affects, and why the current behavior is a problem."
            label="Problem or request"
            name="description"
            required
          />
          <FormTextarea
            :error="errors.desired_outcome"
            description="Describe the result that would make this request successful."
            label="Desired outcome"
            name="desired_outcome"
            required
          />
          <FormTextarea
            :error="errors.acceptance_criteria"
            description="List concrete, testable conditions. One criterion per line is fine."
            label="Acceptance criteria"
            name="acceptance_criteria"
            required
          />
          <div class="flex justify-end gap-3 border-t border-default pt-5">
            <UButton :to="index().url" color="neutral" label="Cancel" variant="ghost" />
            <UButton :loading="processing" icon="i-ph-paper-plane-tilt" label="Submit request" type="submit" />
          </div>
        </Form>
      </div>
    </template>
  </UDashboardPanel>
</template>
