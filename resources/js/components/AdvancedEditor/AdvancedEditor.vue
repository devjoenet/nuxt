<script lang="ts" setup>
import { cn } from "@/lib/utils";
import { ref } from "vue";

const props = withDefaults(defineProps<{ ariaLabel: string; class?: string; disabled?: boolean; modelValue?: string; placeholder?: string }>(), {
  modelValue: "",
  placeholder: "Start writing…",
});
const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const contentError = ref(false);
function updateValue(value: unknown): void {
  if (typeof value === "string") emit("update:modelValue", value);
}
function onContentError(): void {
  contentError.value = true;
}
</script>

<template>
  <div :class="cn('grid gap-3', props.class)">
    <UAlert v-if="contentError" color="error" description="The original value is preserved so it can be corrected safely." title="Editor content could not be parsed" />
    <UEditor v-slot="{ editor }" :aria-label="ariaLabel" content-type="html" :editable="!disabled" :emit-content-error="true" :model-value="modelValue" :on-content-error="onContentError" :placeholder="placeholder" @update:model-value="updateValue">
      <UEditorToolbar :editor="editor" />
    </UEditor>
  </div>
</template>
