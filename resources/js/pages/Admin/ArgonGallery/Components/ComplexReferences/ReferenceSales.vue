<script setup lang="ts">
import { PhRocketLaunch } from "@phosphor-icons/vue";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { computed, ref } from "vue";
import ReferenceMetrics from "./ReferenceMetrics.vue";
import ReferenceTable from "./ReferenceTable.vue";
import { countries, products } from "./tableFixtures";
const props = defineProps<{ specimen: "default" | "overview" | "analytics" }>();
const period = ref("month");
const completed = ref<string[]>([]);
const slide = ref(0);
const slides = ["Get started with Argon", "Faster way to create web pages", "Share with us your design tips!"];
const stats = computed(() =>
  props.specimen === "analytics"
    ? [
        { label: "Users", value: "930", change: "+55%" },
        { label: "New users", value: "744", change: "+3%" },
        { label: "Sessions", value: "1,414", change: "−2%" },
        { label: "Pages / session", value: "1.76", change: "+5%" },
      ]
    : props.specimen === "overview"
      ? [
          { label: "Sales", value: "$230,220", change: "+55% since last month" },
          { label: "Customers", value: "3,200", change: "+12% since last month" },
          { label: "Average revenue", value: "$1,200", change: "+5% since last month" },
        ]
      : [
          { label: "Today's money", value: "$53,000", change: "+55% since yesterday" },
          { label: "Today's users", value: "2,300", change: "+3% since last week" },
          { label: "New clients", value: "+3,462", change: "−2% since last quarter" },
          { label: "Sales", value: "$103,430", change: "+5% since last month" },
        ],
);
const trend = computed(() => (period.value === "month" ? [50, 40, 300, 220, 500, 250, 400, 230, 500] : [90, 120, 80, 190, 240, 200, 310]).map((y, i) => ({ x: i + 1, y })));
const pages = [
  { Page: "/bits", Views: 345, "Average time": "00:17:07", "Bounce rate": "40.91%" },
  { Page: "/pages/argon-dashboard", Views: 520, "Average time": "00:23:13", "Bounce rate": "30.14%" },
  { Page: "/pages/soft-ui-dashboard", Views: 122, "Average time": "00:03:10", "Bounce rate": "54.10%" },
  { Page: "/bootstrap-themes", Views: 1900, "Average time": "00:30:42", "Bounce rate": "20.93%" },
  { Page: "/react-themes", Views: 1442, "Average time": "00:31:50", "Bounce rate": "34.98%" },
];
</script>
<template>
  <div class="grid gap-5">
    <ReferenceMetrics :items="stats" />
    <div class="grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
      <Card class="min-w-0" variant="outline"
        ><template #header
          ><div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-content-heading">{{ specimen === "analytics" ? "Traffic channels" : specimen === "overview" ? "Revenue" : "Sales overview" }}</h3>
            <div class="flex gap-2">
              <UButton label="Month" :variant="period === 'month' ? 'solid' : 'outline'" :aria-pressed="period === 'month'" @click="period = 'month'" /><UButton
                label="Week"
                :variant="period === 'week' ? 'solid' : 'outline'"
                :aria-pressed="period === 'week'"
                @click="period = 'week'"
              />
            </div></div></template
        ><LazyAdvancedChart v-bind="{ ariaLabel: `${specimen} trend by ${period}` }" :data="trend" :height="280"
      /></Card>
      <Card v-if="specimen === 'default'" variant="soft" tone="primary" class="flex flex-col justify-between gap-8"
        ><PhRocketLaunch aria-hidden="true" class="size-16 text-primary" />
        <div>
          <h3 aria-live="polite" class="text-2xl font-semibold text-content-heading">{{ slides[slide] }}</h3>
          <p class="mt-3 text-content-body">Build your next experience with the Argon design system.</p>
        </div>
        <div class="flex gap-3"><UButton label="Previous story" icon="i-ph-arrow-left" variant="outline" @click="slide = (slide + 2) % 3" /><UButton label="Next story" icon="i-ph-arrow-right" variant="outline" @click="slide = (slide + 1) % 3" /></div
      ></Card>
      <Card v-else variant="outline"
        ><template #header
          ><h3 class="text-lg font-semibold text-content-heading">{{ specimen === "analytics" ? "Referrals" : "Channels" }}</h3></template
        >
        <ul class="grid gap-5">
          <li v-for="(label, i) in specimen === 'analytics' ? ['Adobe', 'Atlassian', 'Slack', 'Spotify', 'Jira'] : ['Facebook', 'Direct', 'Organic', 'Referral']" :key="label">
            <div class="mb-2 flex justify-between text-sm text-content-body">
              <span>{{ label }}</span
              ><span>{{ [25, 30, 12, 7, 10][i] }}%</span>
            </div>
            <UProgress :model-value="[25, 30, 12, 7, 10][i]" :aria-label="label" />
          </li></ul
      ></Card>
    </div>
    <div v-if="specimen === 'default'" class="grid gap-5 lg:grid-cols-3">
      <Card variant="outline"
        ><h3 class="mb-4 font-semibold text-content-heading">Team members</h3>
        <ul class="grid gap-4">
          <li v-for="name in ['John Michael', 'Alex Smith', 'Samantha Ivy']" :key="name" class="flex items-center gap-3">
            <UAvatar :alt="name" /><span class="text-content-body">{{ name }}</span
            ><Badge label="Online" tone="success" variant="outline" class="ml-auto" />
          </li></ul></Card
      ><Card variant="outline"
        ><h3 class="mb-4 font-semibold text-content-heading">To do list</h3>
        <UCheckboxGroup v-model="completed" :items="['Call with Dave', 'Brunch Meeting', 'Argon Dashboard Launch', 'Winter Hackaton']" /></Card
      ><Card variant="outline"
        ><h3 class="mb-4 font-semibold text-content-heading">Progress track</h3>
        <div v-for="(name, i) in ['React Material Dashboard', 'Argon Design System', 'VueJs Now UI Kit PRO']" :key="name" class="mb-4">
          <p class="mb-2 text-sm text-content-body">{{ name }}</p>
          <UProgress :model-value="[60, 100, 80][i]" :aria-label="name" /></div
      ></Card>
    </div>
    <div v-if="specimen === 'analytics'" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
      <Card variant="outline"
        ><h3 class="mb-4 font-semibold text-content-heading">Social</h3>
        <div v-for="(name, i) in ['Facebook', 'Twitter', 'Reddit', 'Youtube', 'Slack']" :key="name" class="mb-4">
          <p class="mb-2 text-sm text-content-body">{{ name }}</p>
          <UProgress :model-value="[80, 40, 30, 25, 15][i]" :aria-label="name" /></div></Card
      ><ReferenceTable title="Pages" :rows="pages" />
    </div>
    <template v-else><ReferenceTable title="Sales by Country" :rows="countries" /><ReferenceTable v-if="specimen === 'overview'" title="Top Selling Products" :rows="products" /></template>
  </div>
</template>
