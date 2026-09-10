<script setup lang="ts">
import { AdvancedCalendar } from "@/components/AdvancedCalendar";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Card } from "@/components/Ui/Card";
import { ref } from "vue";
import ReferenceMetrics from "./ReferenceMetrics.vue";
const date = ref("2021-03-27");
const message = ref(false);
const tab = ref("Calendar");
</script>
<template>
  <div class="grid gap-5">
    <ReferenceMetrics
      :items="[
        { label: 'Visitors', value: '5,927', change: '+55%' },
        { label: 'Income', value: '$130,832', change: '+90%' },
      ]"
    />
    <div class="grid gap-5 lg:grid-cols-2">
      <Card variant="outline"
        ><div class="mb-4 flex gap-3"><UButton v-for="label in ['Calendar', 'Visitors']" :key="label" :label="label" :variant="tab === label ? 'solid' : 'outline'" :aria-pressed="tab === label" @click="tab = label" /></div>
        <AdvancedCalendar v-if="tab === 'Calendar'" v-model="date" v-bind="{ ariaLabel: 'CRM calendar' }" /><LazyAdvancedChart
          v-else
          v-bind="{ ariaLabel: 'CRM visitors' }"
          :data="[
            { x: 1, y: 230 },
            { x: 2, y: 450 },
            { x: 3, y: 340 },
            { x: 4, y: 580 },
          ]"
      /></Card>
      <div class="grid gap-5">
        <Card variant="soft" tone="primary"
          ><h3 class="text-2xl font-semibold text-content-heading">Hey John!</h3>
          <p class="mt-3 text-content-body">Wealth creation is an evolutionarily recent positive-sum game. It is all about who takes the opportunity first.</p></Card
        ><Card variant="outline"
          ><h3 class="font-semibold text-content-heading">Categories</h3>
          <ul class="mt-4 grid gap-3 text-content-body">
            <li>Devices · 250 in stock, 346+ sold</li>
            <li>Tickets · 123 closed, 15 open</li>
            <li>Error logs · 1 active, 40 closed</li>
          </ul></Card
        ><Card variant="outline"
          ><p class="mb-3 text-content-body">Today is Martina's birthday. Wish her the best of luck!</p>
          <UButton label="Preview birthday message" @click="message = true" />
          <p v-if="message" role="status" class="mt-3 text-content-body">Happy birthday, Martina! Your demo message has not been sent.</p></Card
        >
      </div>
    </div>
    <div class="grid gap-5 lg:grid-cols-2">
      <Card v-for="(heading, index) in ['Transactions', 'Revenue']" :key="heading" variant="outline"
        ><h3 class="mb-4 text-lg font-semibold text-content-heading">{{ heading }}</h3>
        <ul class="grid gap-5">
          <li v-for="(name, i) in index === 0 ? ['Netflix', 'Apple', 'Partner No. 22213'] : ['via PayPal', 'Partner No. 90211', 'Services']" :key="name" class="flex justify-between gap-3 text-content-body">
            <span>{{ name }}</span
            ><strong>{{ (index === 0 ? ["− $2,500", "+ $2,000", "+ $1,400"] : ["+ $4,999", "+ $700", "− $1,800"])[i] }}</strong>
          </li>
        </ul></Card
      >
    </div>
  </div>
</template>
