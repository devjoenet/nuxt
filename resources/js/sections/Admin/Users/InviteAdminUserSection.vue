<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import { cn } from "@/lib/utils";
import { store } from "@/routes/admin/users";
import type { AdminRoleOption } from "@/sections/Admin/Users/types";

const props = defineProps<{
  roles: AdminRoleOption[];
  class?: string;
}>();
</script>

<template>
  <AppPageCard :class="cn('dark:bg-elevated dark:shadow-none', props.class)" :style="'outline'" description="They will receive a secure password setup link." title="Invite a user">
    <Form #default="{ errors, processing }" class="grid gap-4" reset-on-success v-bind="store.form()">
      <FormInput :error="errors.name" label="Name" name="name" />
      <FormInput :error="errors.email" label="Email" name="email" type="email" />
      <FormSelect :default-value="roles[0]?.value" :error="errors.role" :items="roles" label="Role" name="role" />
      <UButton :loading="processing" class="w-fit" label="Send invitation" type="submit" />
    </Form>
  </AppPageCard>
</template>
