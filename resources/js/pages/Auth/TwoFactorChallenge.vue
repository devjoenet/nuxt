<script setup lang="ts">
import { FormInput } from "@/components/Ui/FormInput";
import { store } from "@/routes/two-factor/login";
import { Form, Head, setLayoutProps } from "@inertiajs/vue3";
import { computed, ref, watchEffect } from "vue";

const authConfigContent = computed<{
  title: string;
  description: string;
  toggleText: string;
}>(() => {
  if (showRecoveryInput.value) {
    return {
      title: "Recovery Code",
      description: "Please confirm access to your account by entering one of your emergency recovery codes.",
      toggleText: "login using an authentication code",
    };
  }

  return {
    title: "Authentication Code",
    description: "Enter the authentication code provided by your authenticator application.",
    toggleText: "login using a recovery code",
  };
});

const showRecoveryInput = ref<boolean>(false);

function toggleRecoveryMode(clearErrors: () => void) {
  showRecoveryInput.value = !showRecoveryInput.value;
  clearErrors();
  code.value = [];
}

const code = ref<number[]>([]);
const codeValue = computed<string>(() => code.value.join(""));

watchEffect(() => {
  setLayoutProps({
    title: authConfigContent.value.title,
    description: authConfigContent.value.description,
  });
});
</script>

<template>
  <div class="contents">
    <Head title="Two-Factor Authentication" />

    <div class="space-y-6">
      <template v-if="!showRecoveryInput">
        <Form v-bind="store.form()" class="space-y-4" reset-on-error @error="code = []" #default="{ errors, processing, clearErrors }">
          <input type="hidden" name="code" :value="codeValue" />

          <UFormField name="code" :error="errors.code" :ui="{ root: 'text-center' }">
            <UPinInput v-model="code" type="number" placeholder="○" size="xl" length="6" otp autofocus />
          </UFormField>

          <UButton type="submit" :disabled="processing" block>Continue</UButton>

          <div class="text-center text-sm text-muted">
            <span>or you can </span>
            <button type="button" class="text-primary underline decoration-[var(--ui-border-accented)] underline-offset-4 transition-colors duration-300 ease-out hover:text-default hover:decoration-current! focus-visible:outline-primary" @click="() => toggleRecoveryMode(clearErrors)">
              {{ authConfigContent.toggleText }}
            </button>
          </div>
        </Form>
      </template>

      <template v-else>
        <Form v-bind="store.form()" class="space-y-4" reset-on-error #default="{ errors, processing, clearErrors }">
          <FormInput :autofocus="showRecoveryInput" :error="errors.recovery_code" label="Recovery code" name="recovery_code" required />

          <UButton type="submit" :disabled="processing" block>Continue</UButton>

          <div class="text-center text-sm text-muted">
            <span>or you can </span>
            <button type="button" class="text-primary underline decoration-[var(--ui-border-accented)] underline-offset-4 transition-colors duration-300 ease-out hover:text-default hover:decoration-current! focus-visible:outline-primary" @click="() => toggleRecoveryMode(clearErrors)">
              {{ authConfigContent.toggleText }}
            </button>
          </div>
        </Form>
      </template>
    </div>
  </div>
</template>
