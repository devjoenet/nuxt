<script lang="ts" setup>
  import { Form, Head } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import Layout from "@/layouts/Default.vue";
  import { update } from "@/routes/admin/settings";

  defineOptions({ layout: Layout });
  defineProps<{ setting: { company_name: string; notification_email: string; default_task_due_days: number } }>();
</script>

<template>
  <Head title="Business settings" />

  <UDashboardPanel id="admin-settings">
    <template #header>
      <UDashboardNavbar description="Defaults used by the Southeast Code operating workspace." title="Business settings">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPageCard class="mx-auto w-full max-w-2xl" description="These changes are permission-protected and audit-logged." title="Workspace defaults" variant="subtle">
        <Form #default="{ errors, processing }" class="grid gap-5" v-bind="update.form()">
          <FormInput :default-value="setting.company_name" :error="errors.company_name" label="Company name" name="company_name" />
          <FormInput :default-value="setting.notification_email" :error="errors.notification_email" label="Notification email" name="notification_email" type="email" />
          <FormInput
            :default-value="setting.default_task_due_days"
            :error="errors.default_task_due_days"
            label="Default follow-up due days"
            max="90"
            min="1"
            name="default_task_due_days"
            type="number"
          />
          <UButton :loading="processing" class="w-fit" label="Save settings" type="submit" />
        </Form>
      </UPageCard>
    </template>
  </UDashboardPanel>
</template>
