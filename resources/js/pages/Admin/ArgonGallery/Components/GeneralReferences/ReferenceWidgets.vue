<script setup lang="ts">
import { AdvancedCalendar } from "@/components/AdvancedCalendar";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { Progress } from "@/components/Ui/Progress";
import { ref } from "vue";

const date = ref("2026-09-02");
const income = [
  { x: 1, y: 32, label: "April" },
  { x: 2, y: 54, label: "May" },
  { x: 3, y: 48, label: "June" },
  { x: 4, y: 79, label: "July" },
  { x: 5, y: 67, label: "August" },
];
const stats = [
  { label: "Battery health", value: "99%" },
  { label: "Music volume", value: "15/100" },
  { label: "Income", value: "$130,832" },
  { label: "Tasks", value: "480" },
];
</script>

<template>
  <div class="grid gap-5">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card v-for="stat in stats" :key="stat.label" variant="outline"
        ><p class="truncate text-base text-content-subtle sm:text-sm">{{ stat.label }}</p>
        <p class="text-2xl font-semibold text-content-heading tabular-nums">{{ stat.value }}</p></Card
      >
    </div>
    <div class="grid gap-5 lg:grid-cols-[2fr_1fr]">
      <Card variant="outline"
        ><template #header
          ><div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-balance text-content-heading">Income</h3>
            <Badge label="+90%" tone="success" variant="soft" /></div></template
        ><LazyAdvancedChart v-bind="{ ariaLabel: 'Income trend' }" :data="income" :height="240" /></Card
      ><Card class="grid content-between gap-5" variant="outline"
        ><div>
          <p class="text-base text-content-subtle sm:text-sm">Completion</p>
          <p class="text-4xl font-semibold tracking-tight text-content-heading tabular-nums">60%</p>
        </div>
        <Progress label="Task progress" :value="60" show-value
      /></Card>
    </div>
    <div class="grid gap-5 lg:grid-cols-2">
      <Card variant="outline"
        ><template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Calendar</h3></template><AdvancedCalendar v-model="date" v-bind="{ ariaLabel: 'Widget calendar' }" /></Card
      ><Card variant="outline"
        ><template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Upcoming events</h3></template>
        <ul class="grid gap-4" role="list">
          <li class="border-b border-content-divider pb-4">
            <strong class="text-content-heading">Cyber Week</strong>
            <p class="text-base text-content-body sm:text-sm">27 September, at 12:30 PM.</p>
          </li>
          <li>
            <strong class="text-content-heading">Meeting with Marry</strong>
            <p class="text-base text-content-body sm:text-sm">30 September, at 10:00 AM.</p>
          </li>
        </ul></Card
      >
    </div>
  </div>
</template>
