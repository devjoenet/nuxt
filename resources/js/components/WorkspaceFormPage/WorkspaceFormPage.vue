<script lang="ts" setup>
import type { FormComponentProps, FormComponentSlotProps } from "@inertiajs/core";
import { Form } from "@inertiajs/vue3";
import FormActions from "./FormActions.vue";
import FormSectionHeading from "./FormSectionHeading.vue";
import { cn } from "@/lib/utils";

const props = withDefaults(
  defineProps<{
    id: string;
    title: string;
    sectionTitle: string;
    cancelTo: string;
    submitLabel: string;
    action: NonNullable<FormComponentProps["action"]>;
    sectionDescription?: string;
    documentTitle?: string;
    method?: FormComponentProps["method"];
    options?: FormComponentProps["options"];
    resetOnSuccess?: FormComponentProps["resetOnSuccess"];
    submitIcon?: string;
    actionsBordered?: boolean;
    class?: string;
    formClass?: string;
  }>(),
  {
    actionsBordered: false,
  },
);

defineSlots<{
  actions?: () => unknown;
  default: (form: FormComponentSlotProps) => unknown;
}>();
</script>

<template>
  <WorkspacePage :id="id" :class="props.class" :document-title="documentTitle" :title="title" content-width="medium">
    <template v-if="$slots.actions" #actions>
      <slot name="actions" />
    </template>

    <Form v-slot="form" :action="action" :aria-labelledby="`${id}-heading`" :class="cn('grid w-full gap-6', formClass)" :method="method" :options="options" :reset-on-success="resetOnSuccess" disable-while-processing>
      <FormSectionHeading :description="sectionDescription" :heading-id="`${id}-heading`" :title="sectionTitle" />

      <UAlert v-if="form.hasErrors" color="error" data-testid="form-error-summary" description="Please review the highlighted fields and try again." title="The form could not be submitted" variant="subtle" />

      <slot v-bind="form" />

      <FormActions :bordered="actionsBordered" :cancel-to="cancelTo" :loading="form.processing" :submit-icon="submitIcon" :submit-label="submitLabel" />
    </Form>
  </WorkspacePage>
</template>
