<script setup lang="ts">
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormCheckbox } from "@/components/Ui/FormCheckbox";
import { FormInput } from "@/components/Ui/FormInput";
import { FormRadioGroup } from "@/components/Ui/FormRadioGroup";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { ref } from "vue";

defineProps<{ specimen: "components" | "elements" | "validation" }>();
const name = ref("Alex Morgan");
const email = ref("");
const role = ref<string | number | null>("designer");
const notes = ref("This inert form demonstrates restored values without submitting data.");
const updates = ref(true);
const active = ref(true);
const plan = ref("team");
const roles = [
  { label: "Designer", value: "designer" },
  { label: "Developer", value: "developer" },
  { label: "Researcher", value: "researcher" },
];
const plans = [
  { label: "Solo", value: "solo" },
  { label: "Team", value: "team" },
  { label: "Enterprise", value: "enterprise" },
];
</script>

<template>
  <div class="grid gap-5">
    <UAlert color="info" description="The licensed file redirects elsewhere, so this local port represents the named form family through the shared floating-field contract." title="Redirect-only source" variant="subtle" />
    <Card as="section" class="grid gap-5" variant="outline">
      <div class="grid gap-4 md:grid-cols-2">
        <FormInput v-model="name" label="Full name" name="reference-name" required />
        <FormInput v-model="email" autocomplete="email" :error="specimen === 'validation' ? 'Enter a valid email address.' : undefined" label="Email address" name="reference-email" placeholder="name@example.com" type="email" />
        <FormSelect v-model="role" :items="roles" label="Role" name="reference-role" />
        <FormInput default-value="Read-only value" label="Account state" name="reference-state" readonly />
      </div>
      <FormTextarea v-if="specimen !== 'elements'" v-model="notes" label="Notes" name="reference-notes" :rows="4" />
      <div class="grid gap-4 sm:grid-cols-2"><FormCheckbox v-model="updates" label="Product updates" name="reference-updates" /><FormSwitch v-model="active" label="Active workspace" name="reference-active" /></div>
      <FormRadioGroup v-if="specimen !== 'components'" v-model="plan" label="Plan" name="reference-plan" :items="plans" orientation="horizontal" />
      <div class="flex flex-wrap justify-end gap-3"><Button label="Reset specimen" tone="neutral" variant="outline" /><Button label="Validate fields" /></div>
    </Card>
  </div>
</template>
