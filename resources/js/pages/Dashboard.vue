<script setup lang="ts">
  import Layout from "@/layouts/Default.vue";
  import type { Period, Range } from "@/types";
  import type { DropdownMenuItem } from "@nuxt/ui";
  import { sub } from "date-fns";
  import { ref, shallowRef } from "vue";

  defineOptions({ layout: Layout });

  const { isNotificationsSlideoverOpen } = useDashboard();

  const items = [
    [
      {
        label: "New mail",
        icon: "i-ph-paper-plane-tilt",
        to: "/inbox",
      },
      {
        label: "New lead",
        icon: "i-ph-user-plus",
        to: "/crm/leads",
      },
    ],
  ] satisfies DropdownMenuItem[][];

  const range = shallowRef<Range>({
    start: sub(new Date(), { days: 14 }),
    end: new Date(),
  });
  const period = ref<Period>("daily");
</script>

<template>
  <UDashboardPanel id="home">
    <template #header>
      <UDashboardNavbar title="Home" :ui="{ right: 'gap-3' }">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <UTooltip text="Notifications" :shortcuts="['N']">
            <UButton color="neutral" variant="ghost" square @click="isNotificationsSlideoverOpen = true">
              <UChip color="error" inset>
                <UIcon class="size-5 shrink-0" name="i-ph-bell" />
              </UChip>
            </UButton>
          </UTooltip>

          <UDropdownMenu :items="items">
            <UButton class="rounded-full" icon="i-ph-plus" size="md" />
          </UDropdownMenu>
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <template #left>
          <!-- NOTE: The `-ms-1` class is used to align with the `DashboardSidebarCollapse` button here. -->
          <HomeDateRangePicker v-model="range" class="-ms-1" />

          <HomePeriodSelect v-model="period" :range="range" />
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <HomeStats :period="period" :range="range" />
      <HomeChart :period="period" :range="range" />
      <HomeSales :period="period" :range="range" />
    </template>
  </UDashboardPanel>
</template>
