<script lang="ts" setup>
  import { computed, useId, useSlots } from "vue";

  defineOptions({ inheritAttrs: false });

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      label?: string;
      description?: string;
      error?: string | boolean;
      valid?: boolean;
      successText?: string;
      variant?: "filled" | "outlined";
      multiline?: boolean;
      staticLabel?: boolean;
      required?: boolean;
    }>(),
    {
      variant: "outlined",
    },
  );

  const slots = useSlots();
  const generatedId = useId();
  const fieldId = computed(() => props.id ?? props.name ?? `form-field-${generatedId}`);
  const messageId = computed(() => `${fieldId.value}-message`);
  const errorMessage = computed(() => (typeof props.error === "string" ? props.error : props.error ? "Please review this field." : undefined));
  const message = computed(() => errorMessage.value ?? (props.valid ? props.successText : props.description));
  const messageTone = computed(() => (errorMessage.value ? "error" : props.valid ? "success" : "description"));
</script>

<template>
  <div class="form-field grid gap-1.5" v-bind="$attrs">
    <div v-if="slots.hint" class="flex items-center justify-end gap-3 text-base text-muted sm:text-sm">
      <slot name="hint" />
    </div>

    <div
      :data-invalid="Boolean(errorMessage)"
      :data-multiline="multiline"
      :data-static-label="staticLabel"
      :data-valid="Boolean(valid && !errorMessage)"
      :data-variant="variant"
      class="form-field__shell group"
    >
      <slot :id="fieldId" :ariaDescribedby="message ? messageId : undefined" :ariaInvalid="errorMessage ? 'true' : undefined" />

      <label v-if="label" :for="fieldId" class="form-field__label"> {{ label }}<span v-if="required" aria-hidden="true" class="ms-0.5 text-error">*</span> </label>
    </div>

    <p v-if="message" :id="messageId" :data-tone="messageTone" :role="messageTone === 'error' ? 'alert' : 'status'" class="form-field__message">
      {{ message }}
    </p>
  </div>
</template>
