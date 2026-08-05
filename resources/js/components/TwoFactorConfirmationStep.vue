<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { confirm } from "@/routes/two-factor";

const emit = defineEmits<{
  back: [];
  confirmed: [];
}>();

const code = ref<number[]>([]);
const codeValue = computed(() => code.value.join(""));
</script>

<template>
  <Form v-slot="{ errors, processing }" reset-on-error v-bind="confirm.form()" @finish="code = []" @success="emit('confirmed')">
    <input :value="codeValue" name="code" type="hidden" />
    <div class="relative w-full space-y-8">
      <UFormField :error="errors.code" :ui="{ root: 'text-center' }" name="code">
        <UPinInput v-model="code" :length="6" autofocus otp placeholder="○" size="xl" type="number" />
      </UFormField>

      <div class="flex w-full items-center space-x-5">
        <UButton :disabled="processing" block class="w-auto flex-1" color="neutral" label="Back" type="button" variant="subtle" @click="emit('back')" />
        <UButton :disabled="processing || codeValue.length < 6" :loading="processing" block class="w-auto flex-1" color="primary" label="Confirm" type="submit" variant="solid" />
      </div>
    </div>
  </Form>
</template>
