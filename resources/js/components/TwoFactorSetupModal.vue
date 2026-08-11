<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import TwoFactorConfirmationStep from "@/components/TwoFactorConfirmationStep.vue";
import TwoFactorSetupStep from "@/components/TwoFactorSetupStep.vue";
import { useTwoFactorAuth } from "@/composables/useTwoFactorAuth";
import { enable } from "@/routes/two-factor";

const props = defineProps<{
  requiresConfirmation: boolean;
  twoFactorEnabled: boolean;
}>();

const open = ref(false);
const showVerificationStep = ref(false);
const { clearSetupData, fetchSetupData, hasSetupData } = useTwoFactorAuth();

const modalConfig = computed(() => {
  if (props.twoFactorEnabled) {
    return {
      title: "Two-Factor Authentication Enabled",
      description: "Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.",
    };
  }

  if (showVerificationStep.value) {
    return {
      title: "Verify Authentication Code",
      description: "Enter the 6-digit code from your authenticator app.",
    };
  }

  return {
    title: "Enable Two-Factor Authentication",
    description: "To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.",
  };
});

const handleNextStep = (): void => {
  if (props.requiresConfirmation) {
    showVerificationStep.value = true;

    return;
  }

  clearSetupData();
  open.value = false;
};

const resetModalState = (): void => {
  if (props.twoFactorEnabled) {
    clearSetupData();
  }

  showVerificationStep.value = false;
};

const handleUpdateOpen = async (isOpen: boolean): Promise<void> => {
  if (isOpen && props.twoFactorEnabled && !hasSetupData.value) {
    await fetchSetupData();
  }
};
</script>

<template>
  <UModal v-model:open="open" :ui="{ content: 'sm:max-w-md divide-none', title: 'font-normal', header: 'block!' }" @after:leave="resetModalState" @update:open="handleUpdateOpen">
    <UButton v-if="hasSetupData" icon="i-ph-shield-check" label="Continue Setup" />

    <Form v-else v-slot="{ processing }" v-bind="enable.form()" @success="fetchSetupData">
      <UButton :disabled="processing" icon="i-ph-shield-check" label="Enable 2FA" type="submit" />
    </Form>

    <template #title>
      <div class="mt-4 flex flex-col items-center justify-center gap-2 text-center sm:text-left">
        <UAvatar icon="i-ph-scan" size="2xl" />
        <h2 class="font-semibold text-highlighted">{{ modalConfig.title }}</h2>
        <p class="mt-1 text-center text-sm text-muted">{{ modalConfig.description }}</p>
      </div>
    </template>

    <template #body>
      <TwoFactorSetupStep v-if="!showVerificationStep" @continue="handleNextStep" />
      <TwoFactorConfirmationStep v-else @back="showVerificationStep = false" @confirmed="open = false" />
    </template>
  </UModal>
</template>
