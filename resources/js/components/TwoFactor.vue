<script setup lang="ts">
import { disable } from "@/routes/two-factor";
import { Form } from "@inertiajs/vue3";

interface Props {
  requiresConfirmation: boolean;
  twoFactorEnabled: boolean;
}

withDefaults(defineProps<Props>(), {
  requiresConfirmation: false,
  twoFactorEnabled: false,
});
</script>

<template>
  <UPageCard title="Two-Factor Authentication" variant="subtle" description="Manage your two-factor authentication settings">
    <div v-if="!twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
      <UBadge color="error">Disabled</UBadge>
      <p class="text-muted">When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.</p>
      <div>
        <TwoFactorSetupModal :requiresConfirmation="requiresConfirmation" :twoFactorEnabled="twoFactorEnabled" />
      </div>
    </div>

    <div v-else class="flex flex-col items-start justify-start space-y-4">
      <UBadge>Enabled</UBadge>

      <p class="text-muted">With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.</p>

      <TwoFactorRecoveryCodes />

      <div class="relative inline">
        <Form v-bind="disable.form()" #default="{ processing }">
          <UButton :disabled="processing" color="error" icon="i-ph-shield-slash" type="submit"> Disable 2FA </UButton>
        </Form>
      </div>
    </div>
  </UPageCard>
</template>
