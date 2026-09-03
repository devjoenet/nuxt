<script setup lang="ts">
import { Avatar } from "@/components/Ui/Avatar";
import { Badge } from "@/components/Ui/Badge";
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { Progress } from "@/components/Ui/Progress";
import { Stat } from "@/components/Ui/Stat";
import { ref } from "vue";

defineProps<{ specimen: "general" | "new-project" | "timeline" }>();
const projectName = ref("Argon reference rollout");
const owner = ref<string | number | null>("alex");
const description = ref("Prepare an inert, reusable project composition for gallery review.");
const notice = ref(false);
const tasks = [
  { title: "Confirm reference states", progress: 100, owner: "Alex Morgan" },
  { title: "Review responsive layout", progress: 72, owner: "Taylor Reed" },
  { title: "Run accessibility pass", progress: 45, owner: "Sam Lee" },
];
const timeline = [
  { title: "$2,400 design changes", time: "09:30", tone: "success" as const },
  { title: "New order 1832412", time: "11:15", tone: "primary" as const },
  { title: "Server payments approved", time: "13:40", tone: "info" as const },
  { title: "Development package unlocked", time: "16:05", tone: "warning" as const },
];
const acknowledge = (): void => {
  notice.value = true;
};
</script>

<template>
  <div class="grid gap-5">
    <UAlert color="info" description="These project fixtures demonstrate composition and interaction without creating, changing, or assigning a real project." title="Inert project reference" variant="subtle" />

    <template v-if="specimen === 'general'">
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Stat label="Earnings" value="$15,800" delta="+12%" trend="up" icon="i-ph-currency-dollar" /><Stat label="Projects" value="24" description="6 active" icon="i-ph-folder-open" /><Stat label="Hours" value="348" description="this month" icon="i-ph-clock" /><Stat
          label="Completion"
          value="78%"
          delta="+5%"
          trend="up"
          icon="i-ph-chart-line-up"
        />
      </div>
      <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
        <Card as="section" class="grid gap-4" variant="outline"
          ><header>
            <p class="text-sm text-content-subtle">Current sprint</p>
            <h3 class="text-xl font-semibold text-content-heading">Argon reference rollout</h3>
          </header>
          <ul class="grid gap-4" role="list">
            <li v-for="task in tasks" :key="task.title" class="grid gap-2">
              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <Avatar :name="task.owner" size="sm" /><span class="font-medium text-content-heading">{{ task.title }}</span>
                </div>
                <span class="text-sm text-content-subtle">{{ task.progress }}%</span>
              </div>
              <Progress :value="task.progress" />
            </li>
          </ul>
          <Button class="justify-self-start" label="Open task board" variant="outline" @click="acknowledge"
        /></Card>
        <Card as="section" class="grid content-start gap-4" variant="soft"
          ><h3 class="text-lg font-semibold text-content-heading">Team availability</h3>
          <div
            v-for="member in [
              { name: 'Alex Morgan', role: 'Design', status: 'online' as const },
              { name: 'Taylor Reed', role: 'Engineering', status: 'busy' as const },
              { name: 'Sam Lee', role: 'Research', status: 'offline' as const },
            ]"
            :key="member.name"
            class="flex items-center gap-3"
          >
            <Avatar :name="member.name" :status="member.status" />
            <div>
              <p class="font-medium text-content-heading">{{ member.name }}</p>
              <p class="text-sm text-content-subtle">{{ member.role }}</p>
            </div>
          </div></Card
        >
      </div>
    </template>

    <Card v-else-if="specimen === 'new-project'" as="section" class="grid gap-6" padding="lg" variant="outline">
      <header>
        <p class="text-sm font-semibold text-primary">New project</p>
        <h3 class="mt-1 text-2xl font-semibold text-content-heading">Define the project brief</h3>
        <p class="mt-1 text-content-body">The source wizard is condensed into one keyboard-friendly inert form.</p>
      </header>
      <form class="grid gap-5" @submit.prevent="acknowledge">
        <div class="grid gap-4 sm:grid-cols-2">
          <FormInput v-model="projectName" label="Project name" name="project-name" required /><FormSelect
            v-model="owner"
            :items="[
              { label: 'Alex Morgan', value: 'alex' },
              { label: 'Taylor Reed', value: 'taylor' },
            ]"
            label="Owner"
            name="project-owner"
            required
          /><FormInput default-value="2026-09-15" label="Starts" name="project-start" type="date" /><FormInput default-value="2026-10-30" label="Target date" name="project-target" type="date" />
        </div>
        <FormTextarea v-model="description" label="Project description" name="project-description" :rows="4" />
        <div class="grid gap-3 sm:grid-cols-3">
          <label v-for="option in ['Design system', 'Application UI', 'Research']" :key="option" class="flex cursor-pointer items-center gap-3 rounded-xl border border-content-border p-4 text-content-heading has-checked:border-primary has-checked:bg-primary/5"
            ><input class="size-4 accent-primary" name="project-category" type="radio" :value="option" />{{ option }}</label
          >
        </div>
        <div class="flex flex-wrap justify-end gap-3"><Button label="Save draft" tone="neutral" variant="outline" @click="acknowledge" /><Button label="Create preview" type="submit" /></div>
      </form>
    </Card>

    <div v-else class="grid gap-5 lg:grid-cols-2">
      <Card
        v-for="variant in ['Light timeline', 'Dark timeline']"
        :key="variant"
        as="section"
        class="grid content-start gap-5"
        :class="variant === 'Dark timeline' ? 'bg-linear-to-br from-primary to-info text-white' : ''"
        padding="lg"
        :variant="variant === 'Dark timeline' ? 'naked' : 'outline'"
      >
        <header>
          <Badge :label="variant" :tone="variant === 'Dark timeline' ? 'info' : 'primary'" />
          <h3 class="mt-2 text-xl font-semibold" :class="variant === 'Dark timeline' ? 'text-white' : 'text-content-heading'">Project activity</h3>
        </header>
        <ol class="grid gap-0">
          <li v-for="event in timeline" :key="event.title" class="relative grid grid-cols-[auto_1fr] gap-4 pb-5 last:pb-0">
            <span :class="variant === 'Dark timeline' ? 'border-white/30 bg-white' : 'border-content-border bg-primary'" class="relative z-10 mt-1 size-3 rounded-full border-2"></span>
            <div>
              <p class="font-medium" :class="variant === 'Dark timeline' ? 'text-white' : 'text-content-heading'">{{ event.title }}</p>
              <p class="text-sm" :class="variant === 'Dark timeline' ? 'text-white/75' : 'text-content-subtle'">{{ event.time }} · recorded by the gallery fixture</p>
            </div>
            <span aria-hidden="true" :class="variant === 'Dark timeline' ? 'bg-white/20' : 'bg-content-divider'" class="absolute top-3 bottom-0 left-[5px] w-px last:hidden"></span>
          </li>
        </ol>
      </Card>
    </div>

    <UAlert v-if="notice" color="success" description="The project action remained inside the preview and did not create or change a record." title="Inert interaction confirmed" variant="subtle" />
  </div>
</template>
