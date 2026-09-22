<script setup lang="ts">
import { FormCheckbox } from "@/components/Ui/FormCheckbox";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import type { FormFieldVariant } from "@/components/Ui/FormField";
import { computed, ref } from "vue";

type FormState = "default" | "invalid" | "disabled" | "readonly" | "loading";
const variant = ref<FormFieldVariant>("outlined");
const state = ref<FormState>("default");
const value = ref("Restored value");
const states = ["default", "invalid", "disabled", "readonly", "loading"].map((item) => ({ label: item, value: item }));
const variants = ["outlined", "filled"].map((item) => ({ label: item, value: item }));
const fieldProps = computed(() => ({ disabled: state.value === "disabled", error: state.value === "invalid" ? "Review this value." : undefined, loading: state.value === "loading", readonly: state.value === "readonly" }));
</script>

<template>
  <section aria-labelledby="form-lab-title" class="grid gap-5">
    <div>
      <h3 id="form-lab-title" class="text-lg font-semibold text-balance text-content-heading">Floating form states</h3>
      <p class="text-base text-pretty text-content-body sm:text-sm">Exercise a shared field state across text, selection, and choice controls.</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-2"><FormSelect v-model="variant" :items="variants" label="Presentation" name="form-variant" /><FormSelect v-model="state" :items="states" label="State" name="form-state" /></div>
    <div class="grid gap-4 sm:grid-cols-2">
      <FormInput v-model="value" v-bind="fieldProps" label="Project name" name="lab-project" :variant="variant" /><FormSelect v-bind="fieldProps" :items="['Discovery', 'Delivery']" label="Phase" name="lab-phase" :variant="variant" /><FormTextarea
        v-bind="fieldProps"
        class="sm:col-span-2"
        label="Notes"
        name="lab-notes"
        :variant="variant"
      /><FormCheckbox label="Include research" name="lab-research" :variant="variant" /><FormSwitch label="Notify reviewers" name="lab-notify" :variant="variant" />
    </div>
  </section>
</template>
