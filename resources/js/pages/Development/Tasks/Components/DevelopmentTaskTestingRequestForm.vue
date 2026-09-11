<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { cn } from "@/lib/utils";
import { store as requestTesting } from "@/routes/development/tasks/testRuns";
import type { DevelopmentOption } from "@/types/development";

const props = defineProps<{
  taskId: string;
  defaultTesterId: string | null;
  testers: DevelopmentOption[];
  class?: string;
}>();
</script>

<template>
  <section :class="cn('grid gap-4 border-t border-default p-5 sm:p-6', props.class)" aria-labelledby="request-testing-heading">
    <div class="grid gap-1">
      <h3 id="request-testing-heading" class="font-medium text-highlighted">Request user testing</h3>
      <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Assign a tester and describe the exact build and behavior they should verify.</p>
    </div>
    <Form v-slot="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="requestTesting.form(taskId)">
      <input :value="taskId" name="development_task_id" type="hidden" />
      <FormSelect :default-value="defaultTesterId ?? ''" :error="errors.tester_id" :items="testers" label="Tester" name="tester_id" required />
      <FormTextarea :error="errors.instructions" label="Testing instructions" name="instructions" required />
      <FormInput :error="errors.build_reference" label="Build or deployment reference" name="build_reference" placeholder="Deployment URL, commit, or build identifier" />
      <UAlert v-if="errors.development_task_id || errors.status" :description="errors.development_task_id || errors.status" color="error" variant="subtle" />
      <UButton :loading="processing" class="justify-self-end" color="neutral" icon="i-ph-test-tube" label="Send to testing" size="sm" type="submit" variant="soft" />
    </Form>
  </section>
</template>
