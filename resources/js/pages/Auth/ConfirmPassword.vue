<script setup lang="ts">
import { FormInput } from "@/components/Ui/FormInput";
import { store } from "@/routes/password/confirm";
import { Form, Head, setLayoutProps } from "@inertiajs/vue3";

setLayoutProps({
  title: "Confirm your password",
  description: "This is a secure area. Confirm your password before continuing.",
});
</script>

<template>
  <div class="contents">
    <Head title="Confirm password" />

    <Form v-bind="store.form()" reset-on-success v-slot="{ errors, processing }">
      <div class="space-y-6">
        <FormInput :error="errors.password" autocomplete="current-password" autofocus label="Password" name="password" required type="password" />

        <div class="flex items-center">
          <UButton :loading="processing" type="submit" block>Confirm Password</UButton>
        </div>
      </div>
    </Form>
  </div>
</template>
