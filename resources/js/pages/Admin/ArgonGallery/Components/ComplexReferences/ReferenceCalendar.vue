<script setup lang="ts">
import { AdvancedCalendar } from "@/components/AdvancedCalendar";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { computed, ref } from "vue";
const date = ref<string | null>("2021-03-25");
const title = ref("");
const events = ref([
  { date: "2021-03-24", time: "22:00", title: "Meeting with Marry" },
  { date: "2021-03-25", time: "09:30", title: "Book Deposit Hall" },
  { date: "2021-03-25", time: "14:00", title: "Shipment Deal UK" },
  { date: "2021-03-26", time: "09:00", title: "Verify Dashboard Color Palette" },
  { date: "2021-03-27", time: "12:30", title: "Cyber Week" },
]);
const selectedEvents = computed(() => events.value.filter((event) => event.date === date.value));
function addEvent(): void {
  if (!date.value || !title.value.trim()) return;
  events.value.push({ date: date.value, time: "12:00", title: title.value.trim() });
  title.value = "";
}
</script>
<template>
  <div class="grid gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    <Card variant="outline"
      ><template #header><h3 class="text-lg font-semibold text-content-heading">Team calendar</h3></template>
      <div class="grid gap-6 xl:grid-cols-2">
        <AdvancedCalendar v-model="date" v-bind="{ ariaLabel: 'Team calendar date' }" />
        <section class="grid content-start gap-4">
          <h4 class="font-semibold text-content-heading">Agenda · {{ date ?? "Select a date" }}</h4>
          <ul aria-live="polite" class="grid gap-3">
            <li v-for="event in selectedEvents" :key="event.title" class="rounded-lg bg-primary/10 p-3 text-content-heading">
              <time>{{ event.time }}</time> · {{ event.title }}
            </li>
          </ul>
          <p v-if="!selectedEvents.length" class="text-content-subtle">No events on this date.</p>
          <form class="grid gap-3" @submit.prevent="addEvent"><FormInput v-model="title" label="Event title" name="demo-event-title" required /><UButton type="submit" label="Add demo event" :disabled="!date" class="w-fit" /></form>
        </section>
      </div>
    </Card>
    <div class="grid gap-5">
      <Card variant="outline"
        ><template #header><h3 class="text-lg font-semibold text-content-heading">Next events</h3></template>
        <ul class="grid gap-3">
          <li v-for="event in events" :key="`${event.date}-${event.title}`">
            <button class="text-left text-sm text-content-body hover:text-primary focus-visible:outline-2 focus-visible:outline-primary" type="button" @click="date = event.date">
              <strong class="block">{{ event.title }}</strong
              >{{ event.date }} · {{ event.time }}
            </button>
          </li>
        </ul></Card
      ><Card variant="outline"
        ><h3 class="font-semibold text-content-heading">Productivity</h3>
        <p class="text-sm text-content-subtle">4% more in 2021</p>
        <LazyAdvancedChart
          v-bind="{ ariaLabel: 'Weekly productivity' }"
          :height="160"
          :data="[
            { x: 1, y: 30 },
            { x: 2, y: 42 },
            { x: 3, y: 38 },
            { x: 4, y: 64 },
          ]"
      /></Card>
    </div>
  </div>
</template>
