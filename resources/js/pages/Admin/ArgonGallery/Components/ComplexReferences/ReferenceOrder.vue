<script setup lang="ts">
import { PhWatch } from "@phosphor-icons/vue";
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { ref } from "vue";
const invoice = ref(false);
</script>
<template>
  <div class="grid gap-5">
    <header class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-xl font-semibold text-content-heading">Order Details</h3>
        <p class="text-sm text-content-subtle">Order No. 1832412 · 23 February 2021</p>
      </div>
      <UButton label="Preview invoice" variant="outline" @click="invoice = true" />
    </header>
    <Card variant="outline"
      ><div class="flex flex-wrap items-center gap-5">
        <PhWatch aria-hidden="true" class="size-20 text-primary" />
        <div>
          <h4 class="text-xl font-semibold text-content-heading">Smart Watch</h4>
          <p class="mt-2 text-content-body">Color: Black · Quantity: 1</p>
        </div>
        <Badge label="Delivered" tone="success" variant="outline" class="ml-auto" /></div
    ></Card>
    <div class="grid gap-5 lg:grid-cols-2">
      <Card variant="outline"
        ><h3 class="mb-5 text-lg font-semibold text-content-heading">Track order</h3>
        <ol class="grid gap-5 border-l-2 border-primary pl-5">
          <li v-for="(status, index) in ['Order received', 'Generate order id No. 1832412', 'Order transmitted to courier', 'Order delivered']" :key="status">
            <strong class="text-content-heading">{{ status }}</strong>
            <p class="text-sm text-content-subtle">{{ 23 + index }} February 2021 · 09:30</p>
          </li>
        </ol></Card
      >
      <div class="grid gap-5">
        <Card variant="outline"
          ><h3 class="font-semibold text-content-heading">Payment details</h3>
          <p class="mt-4 text-xl tracking-widest text-content-heading">•••• •••• •••• 7852</p>
          <p class="mt-2 text-sm text-content-subtle">Demo card · payment received</p></Card
        ><Card variant="outline"
          ><h3 class="font-semibold text-content-heading">Billing Information</h3>
          <p class="mt-4 text-content-body">Oliver Liam</p>
          <p class="text-sm text-content-subtle">Viking Burrito · oliver@example.test</p></Card
        ><Card variant="outline"
          ><h3 class="mb-4 font-semibold text-content-heading">Order Summary</h3>
          <dl class="grid gap-3 text-content-body">
            <div class="flex justify-between">
              <dt>Product price</dt>
              <dd>$299.00</dd>
            </div>
            <div class="flex justify-between">
              <dt>Delivery</dt>
              <dd>$0.00</dd>
            </div>
            <div class="flex justify-between">
              <dt>Taxes</dt>
              <dd>$29.90</dd>
            </div>
            <div class="flex justify-between border-t border-content-border pt-3 font-semibold">
              <dt>Total</dt>
              <dd>$328.90</dd>
            </div>
          </dl></Card
        >
      </div>
    </div>
    <UModal v-model:open="invoice" title="Demo invoice No. 1832412" description="Order summary preview; no invoice is generated or sent."
      ><template #body
        ><p class="text-content-body">Oliver Liam · Smart Watch × 1</p>
        <p class="mt-4 text-xl font-semibold text-content-heading">Total $328.90</p></template
      ></UModal
    >
  </div>
</template>
