<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import AlertError from "./AlertError.vue";
import TwoFactorRegenerateRecoveryCodesForm from "./TwoFactorRegenerateRecoveryCodesForm.vue";
import { useTwoFactorAuth } from "./useTwoFactorAuth";

const { errors, fetchRecoveryCodes, recoveryCodesList, recoveryCodesProcessing } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref(false);
const recoveryCodeSectionRef = ref<HTMLDivElement | null>(null);

async function toggleRecoveryCodesVisibility(): Promise<void> {
  if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
    await fetchRecoveryCodes();
  }

  isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

  if (isRecoveryCodesVisible.value) {
    await nextTick();
    recoveryCodeSectionRef.value?.scrollIntoView({ behavior: "smooth" });
  }
}

onMounted(async () => {
  if (!recoveryCodesList.value.length) {
    await fetchRecoveryCodes();
  }
});
</script>

<template>
  <UPageCard description="Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager." icon="i-ph-lock-key" title="2FA Recovery Codes">
    <div class="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between">
      <UButton :disabled="recoveryCodesProcessing" :icon="isRecoveryCodesVisible ? 'i-ph-eye-slash' : 'i-ph-eye'" :label="`${isRecoveryCodesVisible ? 'Hide' : 'View'} Recovery Codes`" @click="toggleRecoveryCodesVisibility" />

      <TwoFactorRegenerateRecoveryCodesForm v-if="isRecoveryCodesVisible && recoveryCodesList.length" />
    </div>
    <div :class="['relative overflow-hidden transition-all duration-300', isRecoveryCodesVisible ? 'h-auto opacity-100' : 'h-0 opacity-0']">
      <div v-if="errors.length" class="mt-6">
        <AlertError :errors="errors" />
      </div>
      <div v-else class="mt-3 space-y-3">
        <div ref="recoveryCodeSectionRef" class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm">
          <div v-if="recoveryCodesProcessing && !recoveryCodesList.length" class="space-y-2">
            <div v-for="number in 8" :key="number" class="h-4 animate-pulse rounded bg-muted/20" />
          </div>
          <div v-for="(code, index) in recoveryCodesList" v-else :key="index">
            {{ code }}
          </div>
        </div>
        <p class="text-xs text-muted select-none">
          Each recovery code can be used once to access your account and will be removed after use. If you need more, select
          <span class="font-bold">Regenerate Codes</span> above.
        </p>
      </div>
    </div>
  </UPageCard>
</template>
