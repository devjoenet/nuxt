<script setup lang="ts">
import PasswordController from "@/actions/App/Http/Controllers/Settings/PasswordController";
import { FormInput } from "@/components/Ui/FormInput";
import { provideTwoFactorAuth } from "@/composables/useTwoFactorAuth";
import { Form, setLayoutProps } from "@inertiajs/vue3";

interface Props {
  requiresConfirmation: boolean;
  twoFactorEnabled: boolean;
}

withDefaults(defineProps<Props>(), {
  requiresConfirmation: false,
  twoFactorEnabled: false,
});

provideTwoFactorAuth();
setLayoutProps({ documentTitle: "Security settings" });

const toast = useToast();

function handleSuccess() {
  toast.add({
    title: "Success",
    description: "Your password has been updated.",
    icon: "i-ph-check",
    color: "success",
  });
}
</script>

<template>
  <div class="contents">
    <AppPageCard description="Confirm your current password before setting a new one." title="Password">
      <Form
        v-bind="PasswordController.update.form()"
        :options="{
          preserveScroll: true,
        }"
        reset-on-success
        :reset-on-error="['password', 'password_confirmation', 'current_password']"
        class="flex max-w-xs flex-col gap-4"
        v-slot="{ errors, processing }"
        @success="handleSuccess"
      >
        <FormInput :error="errors.current_password" label="Current password" name="current_password" type="password" />

        <FormInput :error="errors.password" label="New password" name="password" type="password" />

        <FormInput :error="errors.password_confirmation" label="Confirm password" name="password_confirmation" type="password" />

        <UButton label="Update" class="w-fit" type="submit" :disabled="processing" />
      </Form>
    </AppPageCard>

    <TwoFactor :requiresConfirmation="requiresConfirmation" :twoFactorEnabled="twoFactorEnabled" />

    <AppPageCard description="No longer want to use our service? You can delete your account here. This action is not reversible. All information related to this account will be deleted permanently." title="Account" variant="error">
      <template #footer>
        <DeleteAccount />
      </template>
    </AppPageCard>
  </div>
</template>
