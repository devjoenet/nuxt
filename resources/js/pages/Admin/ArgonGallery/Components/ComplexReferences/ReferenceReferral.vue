<script setup lang="ts">
import { Card } from "@/components/Ui/Card";
import { ref } from "vue";
import ReferenceMetrics from "./ReferenceMetrics.vue";
import ReferenceTable from "./ReferenceTable.vue";
const revealed = ref(false);
const users = [
  { Name: "Alice Vinget", Orders: 12, Sales: "$12,400", Bonus: "$1,240" },
  { Name: "John Alura", Orders: 9, Sales: "$9,500", Bonus: "$950" },
  { Name: "Andrew Sian", Orders: 8, Sales: "$8,200", Bonus: "$820" },
  { Name: "Luca Willaim", Orders: 6, Sales: "$6,400", Bonus: "$640" },
  { Name: "Richel Manuel", Orders: 4, Sales: "$4,200", Bonus: "$420" },
];
</script>
<template>
  <div class="grid gap-5">
    <h3 class="text-xl font-semibold text-content-heading">Referral Program</h3>
    <ReferenceMetrics
      :items="[
        { label: 'Earnings', value: '$14,400' },
        { label: 'Customers', value: '38' },
        { label: 'Average value', value: '$340' },
        { label: 'Refund rate', value: '1.5%' },
      ]"
    />
    <div class="grid gap-5 lg:grid-cols-2">
      <Card variant="soft" tone="primary"
        ><h3 class="text-lg font-semibold text-content-heading">Referral Code</h3>
        <p class="my-4 text-content-body">Share your unique code with friends and earn rewards.</p>
        <UButton :label="revealed ? 'Hide demo code' : 'Reveal demo code'" @click="revealed = !revealed" />
        <p v-if="revealed" role="status" class="mt-5 break-all font-mono text-lg text-content-heading">ARGON-DEMO-2021</p></Card
      ><Card variant="outline"
        ><h3 class="mb-4 text-lg font-semibold text-content-heading">How to use</h3>
        <ol class="grid list-inside list-decimal gap-4 text-content-body">
          <li>Give your friends $100 to get started.</li>
          <li>Earn 10% on eligible purchases.</li>
          <li>Receive up to $500 in rewards.</li>
        </ol></Card
      >
    </div>
    <ReferenceTable title="Top Referred Users" :rows="users" /><Card variant="outline"
      ><h3 class="mb-4 font-semibold text-content-heading">Other programs</h3>
      <div class="grid gap-4 sm:grid-cols-2">
        <p class="rounded-xl bg-surface-subtle p-5 text-content-body">Partner program · earn rewards with your team.</p>
        <p class="rounded-xl bg-surface-subtle p-5 text-content-body">Community program · help others get started.</p>
      </div></Card
    >
  </div>
</template>
