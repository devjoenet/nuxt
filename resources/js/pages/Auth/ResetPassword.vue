<script setup lang="ts">
import NewPasswordController from "@/actions/App/Http/Controllers/Auth/NewPasswordController";
import { FormInput } from "@/components/Ui/FormInput";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { Form, Head } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
  token: string;
  email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
  <AuthLayout title="Reset password" description="Please enter your new password below">
    <Head title="Reset password" />

    <Form v-bind="NewPasswordController.store.form()" :transform="(data) => ({ ...data, token, email })" :reset-on-success="['password', 'password_confirmation']" v-slot="{ errors, processing }">
      <div class="grid gap-6">
        <FormInput v-model="inputEmail" :error="errors.email" autocomplete="email" label="Email" name="email" readonly type="email" />

        <FormInput :error="errors.password" autocomplete="new-password" label="Password" name="password" required type="password" />

        <FormInput :error="errors.password_confirmation" autocomplete="new-password" label="Confirm Password" name="password_confirmation" required type="password" />

        <UButton :loading="processing" type="submit" block class="mt-4">Confirm Password</UButton>
      </div>
    </Form>
  </AuthLayout>
</template>
