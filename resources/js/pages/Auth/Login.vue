<script setup lang="ts">
import AuthenticatedSessionController from "@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController";
import { FormInput } from "@/components/Ui/FormInput";
import { TextLink } from "@/components/TextLink";
import { request } from "@/routes/password";
import { Form, Head, setLayoutProps } from "@inertiajs/vue3";

defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

setLayoutProps({
  title: "Log in to your account",
  description: "Enter your email and password below to log in.",
});
</script>

<template>
  <div class="contents">
    <Head title="Log in" />

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-success">
      {{ status }}
    </div>

    <Form v-bind="AuthenticatedSessionController.store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <FormInput :error="errors.email" autocomplete="email" autofocus label="Email address" name="email" required type="email" />
        <FormInput :error="errors.password" autocomplete="current-password" label="Password" name="password" required type="password">
          <template #hint>
            <TextLink v-if="canResetPassword" :href="request()" class="text-sm text-primary" :tabindex="5"> Forgot password? </TextLink>
          </template>
        </FormInput>

        <UFormField name="remember" :error="errors.password">
          <UCheckbox label="Remember me" />
        </UFormField>

        <UButton :loading="processing" type="submit" block class="mt-4">Log in</UButton>
      </div>
    </Form>
  </div>
</template>
