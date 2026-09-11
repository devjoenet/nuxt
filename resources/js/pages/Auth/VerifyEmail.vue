<script setup lang="ts">
import EmailVerificationNotificationController from "@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController";
import { TextLink } from "@/components/TextLink";
import { logout } from "@/routes";
import { Form, Head, setLayoutProps } from "@inertiajs/vue3";

defineProps<{
  status?: string;
}>();

setLayoutProps({
  title: "Verify email",
  description: "Verify your email address using the link we just sent.",
});
</script>

<template>
  <div class="contents">
    <Head title="Email verification" />

    <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-success">A new verification link has been sent to the email address you provided during registration.</div>

    <Form v-bind="EmailVerificationNotificationController.store.form()" class="space-y-6 text-center" v-slot="{ processing }">
      <UButton :loading="processing" type="submit">Resend verification email</UButton>

      <TextLink :href="logout()" as="button" class="mx-auto block text-sm font-medium text-primary"> Log out </TextLink>
    </Form>
  </div>
</template>
