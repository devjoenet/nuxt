<script lang="ts" setup>
  import { computed } from "vue";
  import { Form } from "@inertiajs/vue3";
  import FormTextarea from "@/components/FormTextarea.vue";
  import { update } from "@/routes/admin/userTesting";

  interface UserTestingPage {
    id: number;
    title: string;
    path: string;
    tested: boolean;
    tested_at: string | null;
    tested_by: string | null;
    notes: string | null;
  }

  const props = defineProps<{ page: UserTestingPage }>();

  const surfaceClasses = ["bg-elevated/90 ring-default", "bg-muted/85 ring-default", "bg-accented/35 ring-default", "bg-elevated/70 ring-primary/20"] as const;

  const surfaceClass = computed(() => surfaceClasses[(props.page.id - 1) % surfaceClasses.length]);
</script>

<template>
  <UPageCard :class="['h-full', surfaceClass]" variant="subtle">
    <Form #default="{ errors, processing }" class="flex h-full flex-col gap-4" v-bind="update.form(page.id)">
      <div class="flex items-start justify-between gap-4">
        <div>
          <a :href="page.path" class="text-sm font-semibold text-highlighted hover:text-primary hover:underline" rel="noopener noreferrer" target="_blank">
            {{ page.title }}
            <span aria-hidden="true" class="ms-1 text-xs text-muted">↗</span>
          </a>
          <p class="mt-1 font-mono text-xs text-muted">{{ page.path }}</p>
        </div>
        <UBadge :color="page.tested ? 'success' : 'warning'" :label="page.tested ? 'Approved' : 'Needs eyes'" variant="subtle" />
      </div>

      <FormTextarea :default-value="page.notes ?? undefined" :error="errors.notes" :rows="4" label="Notes, feedback, or changes" name="notes" />

      <div class="mt-auto flex flex-wrap items-center justify-between gap-3">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
          <input :checked="page.tested" class="size-4 rounded border-default text-primary focus:ring-primary" name="tested" type="checkbox" value="1" />
          <span>I tested and approve this page</span>
        </label>
        <UButton :loading="processing" label="Save review" size="sm" type="submit" />
      </div>

      <p v-if="page.tested_by && page.tested_at" class="text-xs text-muted">Last reviewed by {{ page.tested_by }} · {{ page.tested_at }}</p>
    </Form>
  </UPageCard>
</template>
