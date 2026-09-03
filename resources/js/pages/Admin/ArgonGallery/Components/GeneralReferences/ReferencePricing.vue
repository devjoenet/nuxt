<script setup lang="ts">
import { Badge } from "@/components/Ui/Badge";
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { ref } from "vue";

const annual = ref(false);
const tiers = [
  { name: "Starter", monthly: 59, description: "For small teams finding their rhythm.", features: ["2 team members", "20 GB storage", "Integration help"] },
  { name: "Premium", monthly: 89, description: "For growing teams shipping together.", features: ["10 team members", "40 GB storage", "Priority integration help"], recommended: true },
  { name: "Enterprise", monthly: 159, description: "For organizations needing more control.", features: ["Unlimited members", "100 GB storage", "Complete documentation"] },
];
</script>

<template>
  <section aria-labelledby="pricing-reference-title" class="grid gap-8">
    <div class="grid justify-items-center gap-4 text-center">
      <div>
        <p class="text-base font-medium text-primary sm:text-sm">Flexible plans</p>
        <h3 id="pricing-reference-title" class="max-w-[35ch] text-balance text-3xl font-semibold tracking-tight text-content-heading">See our pricing</h3>
        <p class="max-w-[48ch] text-pretty text-base text-content-body sm:text-sm">Choose monthly billing or save with an annual commitment.</p>
      </div>
      <FormSwitch v-model="annual" label="Annual billing" name="reference-billing-cycle" />
    </div>
    <div class="grid gap-5 lg:grid-cols-3">
      <Card v-for="tier in tiers" :key="tier.name" class="flex flex-col justify-between gap-6" variant="outline"
        ><div class="grid gap-4">
          <div class="flex items-center justify-between gap-3">
            <h4 class="text-balance text-xl font-semibold text-content-heading">{{ tier.name }}</h4>
            <Badge v-if="tier.recommended" label="Recommended" />
          </div>
          <div>
            <strong class="text-4xl font-semibold tracking-tight tabular-nums text-content-heading">${{ annual ? Math.round(tier.monthly * 0.8) : tier.monthly }}</strong
            ><span class="text-content-subtle"> / month</span>
          </div>
          <p class="text-pretty text-base text-content-body sm:text-sm">{{ tier.description }}</p>
          <ul class="grid gap-3" role="list">
            <li v-for="feature in tier.features" :key="feature" class="flex items-start gap-2 text-base text-content-body sm:text-sm"><UIcon class="size-4 shrink-0 text-success" name="i-ph-check" />{{ feature }}</li>
          </ul>
        </div>
        <Button :class="tier.recommended ? 'dark:[--ui-tone:var(--ui-color-primary-700)]' : undefined" :label="tier.recommended ? 'Try Premium' : 'Choose plan'" :tone="tier.recommended ? 'primary' : 'neutral'" :variant="tier.recommended ? 'solid' : 'outline'"
      /></Card>
    </div>
  </section>
</template>
