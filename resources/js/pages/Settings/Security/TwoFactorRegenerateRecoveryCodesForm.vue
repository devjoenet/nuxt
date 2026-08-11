<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { regenerateRecoveryCodes } from "@/routes/two-factor";
import { useTwoFactorAuth } from "./useTwoFactorAuth";

const { fetchRecoveryCodes } = useTwoFactorAuth();
</script>

<template>
  <Form v-slot="{ processing }" :options="{ preserveScroll: true }" v-bind="regenerateRecoveryCodes.form()" @success="fetchRecoveryCodes">
    <UButton :disabled="processing" color="secondary" icon="i-ph-arrow-clockwise" label="Regenerate Codes" type="submit" />
  </Form>
</template>
