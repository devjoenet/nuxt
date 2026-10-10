<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import { formatDateTime } from "@/lib/dateFormatters";
import { Stat } from "@/components/Ui/Stat";

const props = defineProps<{
  stats: { label: string; value: number | null; url: string; attention: boolean }[];
  recentChanges: { id: number; actor: string; action: string; record: string; url: string | null; created_at: string | null }[];
  canViewChanges: boolean;
}>();
const attention = computed(() => props.stats.filter((stat) => stat.attention && (stat.value === null || stat.value > 0)));
</script>

<template>
  <WorkspacePage id="admin-dashboard" description="Workspace health, access, and recent changes." document-title="Admin dashboard" title="Admin">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <Link v-for="stat in stats" :key="stat.label" :href="stat.url" class="rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
        <Stat :label="stat.label" tone="secondary" :value="stat.value ?? 'Unavailable'" />
      </Link>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
      <AppPageCard :style="'outline'" class="dark:bg-elevated dark:shadow-none" description="Open issues in the areas you can access." title="Needs attention">
        <ul v-if="attention.length" class="divide-y divide-default">
          <li v-for="item in attention" :key="item.label" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
            <div>
              <p class="text-sm font-medium text-highlighted">{{ item.label }}</p>
              <p class="text-sm text-muted">{{ item.value === null ? "Count unavailable. Check this area." : item.value }}</p>
            </div>
            <UButton :to="item.url" color="neutral" variant="outline" :aria-label="`Review ${item.label.toLowerCase()}`" label="Review" />
          </li>
        </ul>
        <p v-else class="text-sm text-muted">Nothing needs attention.</p>
      </AppPageCard>

      <AppPageCard :style="'outline'" class="dark:bg-elevated dark:shadow-none" description="The latest changes in the areas you can access." title="Recent changes">
        <ul v-if="recentChanges.length" class="divide-y divide-default">
          <li v-for="change in recentChanges" :key="change.id" class="py-3 first:pt-0 last:pb-0">
            <p class="text-sm text-highlighted">{{ change.actor }} · {{ change.action }}</p>
            <Link v-if="change.url" :href="change.url" class="text-sm text-primary underline underline-offset-4">{{ change.record }}</Link>
            <p v-else class="text-sm text-muted">{{ change.record }}</p>
            <p class="mt-1 text-xs text-muted">
              <time v-if="change.created_at" :datetime="change.created_at">{{ formatDateTime(change.created_at) }}</time
              ><span v-else>Time unavailable</span>
            </p>
          </li>
        </ul>
        <p v-else class="text-sm text-muted">{{ canViewChanges ? "No recent changes." : "Audit permission is required to view recent changes." }}</p>
      </AppPageCard>
    </div>
  </WorkspacePage>
</template>
