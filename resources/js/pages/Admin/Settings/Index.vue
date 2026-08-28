<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { FormInput } from "@/components/Ui/FormInput";
import { update } from "@/routes/admin/settings";

defineProps<{ setting: { company_name: string; notification_email: string; default_task_due_days: number } }>();
</script>

<template>
  <WorkspacePage id="admin-settings" content-width="narrow" description="Defaults used by the Southeast Code operating workspace." title="Business settings">
    <AppPageCard :style="'outline'" class="w-full dark:bg-elevated dark:shadow-none" description="These changes are permission-protected and audit-logged." title="Workspace defaults">
      <Form #default="{ errors, processing }" class="grid gap-5" v-bind="update.form()">
        <FormInput :default-value="setting.company_name" :error="errors.company_name" label="Company name" name="company_name" />
        <FormInput :default-value="setting.notification_email" :error="errors.notification_email" label="Notification email" name="notification_email" type="email" />
        <FormInput :default-value="setting.default_task_due_days" :error="errors.default_task_due_days" label="Default follow-up due days" max="90" min="1" name="default_task_due_days" type="number" />
        <UButton :loading="processing" class="w-fit" label="Save settings" type="submit" />
      </Form>
    </AppPageCard>
  </WorkspacePage>
</template>
