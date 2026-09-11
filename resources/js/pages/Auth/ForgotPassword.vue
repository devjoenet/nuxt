<script setup lang="ts">
import PasswordResetLinkController from "@/actions/App/Http/Controllers/Auth/PasswordResetLinkController";
import { FormInput } from "@/components/Ui/FormInput";
import { TextLink } from "@/components/TextLink";
import { login } from "@/routes";
import { Form, Head, setLayoutProps } from "@inertiajs/vue3";

defineProps<{
  status?: string;
}>();

setLayoutProps({
  title: "Forgot password",
  description: "Enter your email to receive a password reset link.",
});
</script>

<template>
  <div class="contents">
    <Head title="Forgot password" />

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-success">
      {{ status }}
    </div>

    <div class="space-y-6">
      <Form v-bind="PasswordResetLinkController.store.form()" v-slot="{ errors, processing }">
        <FormInput :error="errors.email" autocomplete="off" autofocus label="Email address" name="email" required type="email" />

        <div class="my-6 flex items-center justify-start">
          <UButton :loading="processing" type="submit" block>Confirm Password</UButton>
        </div>
      </Form>

      <div class="space-x-1 text-center text-sm text-muted">
        <span class="text-muted">Or, return to</span>
        <TextLink :href="login()" class="text-sm font-medium text-primary">log in</TextLink>
      </div>
    </div>
  </div>
</template>
