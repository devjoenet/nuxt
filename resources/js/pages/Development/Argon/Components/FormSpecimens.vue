<script lang="ts" setup>
import { ref } from "vue";
import { FormAsyncSelect } from "@/components/Ui/FormAsyncSelect";
import { FormCheckbox } from "@/components/Ui/FormCheckbox";
import { FormCurrencyInput } from "@/components/Ui/FormCurrencyInput";
import { FormFileUpload } from "@/components/Ui/FormFileUpload";
import { FormInput } from "@/components/Ui/FormInput";
import { FormMultiSelect } from "@/components/Ui/FormMultiSelect";
import { FormPhoneInput } from "@/components/Ui/FormPhoneInput";
import { FormRadioGroup } from "@/components/Ui/FormRadioGroup";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { FormTextarea } from "@/components/Ui/FormTextarea";

const selected = ref<string | null>(null);
const options = [
  { label: "First option", value: "first" },
  { label: "Second option", value: "second" },
];
</script>

<template>
  <section aria-labelledby="form-specimens" class="grid gap-5 sm:grid-cols-2">
    <h2 id="form-specimens" class="text-2xl font-semibold text-content-heading sm:col-span-2">Floating form specimens</h2>
    <FormInput label="Empty text" name="empty" /><FormInput default-value="Restored value" label="Filled or restored" name="restored" /> <FormInput error="Review this value." label="Invalid" name="invalid" /><FormInput disabled label="Disabled" name="disabled" />
    <FormInput default-value="Readonly value" label="Readonly" name="readonly" readonly /><FormInput label="Loading search" loading name="search" type="search" /> <FormInput inputmode="numeric" label="Number" name="number" type="number" /><FormInput
      autocomplete="current-password"
      label="Password"
      name="password"
      type="password"
    />
    <FormInput label="Date" name="date" type="date" /><FormInput label="Time" name="time" type="time" /> <FormPhoneInput label="Phone" name="phone" /><FormCurrencyInput label="Budget" name="budget" />
    <FormTextarea class="sm:col-span-2" label="Project details" name="details" />
    <FormSelect :items="options" label="Select" name="select" /><FormMultiSelect :items="options" label="Multiple select" name="multiple" />
    <FormAsyncSelect v-model="selected" :items="options" label="Async select" name="async" placeholder="Search options" />
    <FormFileUpload accept="image/*" label="File upload" name="file" />
    <FormCheckbox label="I agree" name="agree" /><FormSwitch label="Notifications" name="notifications" />
    <FormRadioGroup class="sm:col-span-2" :items="options" label="Choose one" name="radio" />
  </section>
</template>
