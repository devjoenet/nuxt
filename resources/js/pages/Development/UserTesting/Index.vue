<script lang="ts" setup>
import { usePage } from "@inertiajs/vue3";
import IndexEmptyState from "@/components/IndexEmptyState.vue";
import UserTestingRoundSection from "@/sections/Development/UserTesting/UserTestingRoundSection.vue";
import type { AppPageProps } from "@/types";
import type { DevelopmentTestRun } from "@/types/development";

defineProps<{ testRuns: DevelopmentTestRun[] }>();

const page = usePage<AppPageProps>();
const toast = useToast();

watch(
  () => page.props.flash.success,
  (value) => {
    if (value) toast.add({ title: value, color: "success" });
  },
  { immediate: true },
);
</script>

<template>
  <WorkspacePage id="development-user-testing" description="Run task-specific test rounds and preserve every feedback cycle." title="User testing">
    <div class="grid gap-6">
      <UPageCard variant="subtle">
        <p class="text-xs font-bold tracking-[0.22em] text-highlighted uppercase">Release gate</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-highlighted">Test the requested outcome, not a generic page checklist.</h1>
        <p class="mt-3 max-w-3xl leading-7 text-muted">Each round keeps its instructions, build reference, tester, and result. Accepted work is completed and archived; requested changes return to implementation without erasing the previous round.</p>
      </UPageCard>

      <IndexEmptyState v-if="testRuns.length === 0" description="There are no task rounds waiting for your result." icon="i-ph-check-circle" title="The testing queue is clear" />

      <ol v-else class="grid gap-5" role="list">
        <li v-for="testRun in testRuns" :key="testRun.id">
          <UserTestingRoundSection :test-run="testRun" />
        </li>
      </ol>
    </div>
  </WorkspacePage>
</template>
