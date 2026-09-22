<script setup lang="ts">
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { Progress } from "@/components/Ui/Progress";

defineProps<{ specimen: "default" | "info" }>();
const people = ["John Michael", "Alex Smith", "Samantha Ivy"];
const tasks = ["Call with Dave", "Brunch meeting", "Argon gallery launch"];
const schedule = [
  { time: "08:00", title: "Sync with Mark" },
  { time: "09:30", title: "Gym" },
  { time: "11:00", title: "Design review" },
];
</script>

<template>
  <section class="relative isolate overflow-hidden rounded-2xl bg-linear-to-br from-primary-950 via-primary-800 to-secondary-700 p-5 text-white shadow-lg sm:p-8 dark:shadow-none">
    <div class="pointer-events-none absolute inset-0 opacity-35 [background:radial-gradient(circle_at_20%_10%,white_0,transparent_28%),radial-gradient(circle_at_80%_80%,var(--color-info)_0,transparent_34%)]" />
    <div class="relative grid gap-6">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-base text-white/75 sm:text-sm">Virtual reality reference</p>
          <h3 class="text-3xl font-semibold tracking-tight text-balance">{{ specimen === "default" ? "General statistics" : "Daily briefing" }}</h3>
        </div>
        <Badge :label="specimen === 'default' ? '1,600,000 users' : '12°C · Cloudy'" tone="info" />
      </div>
      <div v-if="specimen === 'default'" class="grid gap-4 lg:grid-cols-3">
        <Card class="grid gap-3" variant="glass"
          ><h4 class="text-lg font-semibold text-content-heading">Team members</h4>
          <ul class="grid gap-3" role="list">
            <li v-for="person in people" :key="person" class="flex items-center justify-between gap-3 text-base text-content-body sm:text-sm">
              <span>{{ person }}</span
              ><Badge label="Online" tone="success" variant="soft" />
            </li></ul></Card
        ><Card class="grid gap-3" variant="glass"
          ><h4 class="text-lg font-semibold text-content-heading">To-do list</h4>
          <ul class="grid gap-3" role="list">
            <li v-for="task in tasks" :key="task" class="text-base text-content-body sm:text-sm">{{ task }}</li>
          </ul></Card
        ><Card class="grid gap-4" variant="glass"
          ><h4 class="text-lg font-semibold text-content-heading">Progress track</h4>
          <Progress label="Argon Design System" :value="76" show-value /><Progress label="Vue UI kit" tone="success" :value="58" show-value
        /></Card>
      </div>
      <div v-else class="grid gap-4 md:grid-cols-[2fr_1fr]">
        <Card class="grid gap-3" variant="glass"
          ><h4 class="text-lg font-semibold text-content-heading">Today’s schedule</h4>
          <ol class="grid gap-3" role="list">
            <li v-for="item in schedule" :key="item.time" class="grid grid-cols-[auto_1fr] gap-4 border-b border-content-divider pb-3 last:border-0 last:pb-0">
              <span class="font-medium text-primary tabular-nums">{{ item.time }}</span
              ><span class="text-content-body">{{ item.title }}</span>
            </li>
          </ol></Card
        ><Card class="grid content-between gap-5" variant="glass"
          ><div>
            <p class="text-base text-content-subtle sm:text-sm">To do</p>
            <p class="text-5xl font-semibold tracking-tight text-content-heading tabular-nums">7</p>
          </div>
          <p class="text-base text-pretty text-content-body sm:text-sm">Some Kind of Blues is queued for the afternoon.</p></Card
        >
      </div>
    </div>
  </section>
</template>
