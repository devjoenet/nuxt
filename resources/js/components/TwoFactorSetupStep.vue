<script lang="ts" setup>
import FormInput from "@/components/FormInput.vue";
import { useTwoFactorAuth } from "@/composables/useTwoFactorAuth";

const emit = defineEmits<{
  continue: [];
}>();

const { copy, copied } = useClipboard();
const { errors, manualSetupKey, qrCodeSvg, setupProcessing } = useTwoFactorAuth();
</script>

<template>
  <div class="relative flex w-auto flex-col items-center justify-center space-y-5">
    <AlertError v-if="errors.length" :errors="errors" />
    <template v-else>
      <div class="relative mx-auto flex max-w-md items-center overflow-hidden">
        <div class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-muted">
          <div v-if="setupProcessing || !qrCodeSvg" class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-default">
            <UIcon class="size-6 animate-spin" name="i-ph-spinner-gap" />
          </div>
          <div v-else class="relative z-10 overflow-hidden border border-muted p-5">
            <div class="flex aspect-square size-full items-center justify-center" v-html="qrCodeSvg" />
          </div>
        </div>
      </div>

      <UButton :disabled="setupProcessing" block label="Continue" @click="emit('continue')" />

      <div class="relative flex w-full items-center justify-center">
        <div class="absolute inset-0 top-1/2 h-px w-full bg-muted" />
        <span class="relative bg-elevated px-2 py-1">or, enter the code manually</span>
      </div>

      <div class="flex w-full items-center justify-center space-x-2">
        <div class="flex w-full items-stretch">
          <div v-if="setupProcessing || !manualSetupKey" class="flex h-full w-full items-center justify-center bg-default p-3">
            <UIcon class="size-4 animate-spin" name="i-ph-spinner-gap" />
          </div>
          <FormInput v-else v-model="manualSetupKey" :ui="{ trailing: 'pr-0.5' }" class="w-full" label="Manual setup key" readonly size="lg">
            <template #trailing>
              <UTooltip :content="{ side: 'right' }" text="Copy to clipboard">
                <UButton :aria-label="copied ? 'Copied to clipboard' : 'Copy to clipboard'" :color="copied ? 'success' : 'neutral'" :icon="copied ? 'i-ph-checks' : 'i-ph-copy'" size="sm" variant="link" @click="copy(manualSetupKey)" />
              </UTooltip>
            </template>
          </FormInput>
        </div>
      </div>
    </template>
  </div>
</template>
