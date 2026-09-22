<script setup lang="ts">
import { PhDevices, PhLaptop, PhWatch, PhHeadphones, PhArmchair } from "@phosphor-icons/vue";
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormInput } from "@/components/Ui/FormInput";
import { ref } from "vue";
const selected = ref(0);
const gallery = ref(false);
const color = ref("Black");
const quantity = ref(1);
const added = ref(false);
const views = [
  { label: "Apple Bundle", icon: PhDevices },
  { label: "Laptop", icon: PhLaptop },
  { label: "Smart Watch", icon: PhWatch },
  { label: "Headphones", icon: PhHeadphones },
];
</script>
<template>
  <div class="grid gap-5">
    <h3 class="text-xl font-semibold text-content-heading">Product Details</h3>
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="grid gap-4">
        <button class="bg-surface-subtle grid min-h-72 place-content-center gap-4 rounded-xl p-8 text-content-heading focus-visible:outline-2 focus-visible:outline-primary" type="button" aria-label="Enlarge product preview" @click="gallery = true">
          <component :is="views[selected]?.icon" aria-hidden="true" class="mx-auto size-32 text-primary" /><span>{{ views[selected]?.label }}</span>
        </button>
        <div class="grid grid-cols-4 gap-3">
          <button
            v-for="(view, index) in views"
            :key="view.label"
            :aria-label="`View ${view.label}`"
            :aria-pressed="selected === index"
            :class="['rounded-xl border p-4 focus-visible:outline-2 focus-visible:outline-primary', selected === index ? 'border-primary bg-primary/10' : 'border-content-border']"
            type="button"
            @click="selected = index"
          >
            <component :is="view.icon" aria-hidden="true" class="size-8 text-primary" />
          </button>
        </div>
      </div>
      <Card variant="outline"
        ><h4 class="text-2xl font-semibold text-content-heading">Apple Bundle</h4>
        <p class="mt-2 text-sm text-content-subtle">5.0 · 24 demo reviews</p>
        <p class="mt-5 text-sm text-content-subtle">Price</p>
        <p class="text-3xl font-semibold text-content-heading">$1,419</p>
        <Badge class="mt-4" label="In stock" tone="success" variant="outline" />
        <p class="my-5 text-content-body">A complete desktop and mobile setup with thoughtfully matched accessories.</p>
        <form class="grid gap-4" @submit.prevent="added = true">
          <FormSelect v-model="color" label="Color" :items="['Black', 'Silver', 'Gold']" /><FormInput v-model="quantity" label="Quantity" name="demo-product-quantity" type="number" min="1" max="10" required /><UButton type="submit" label="Add to demo cart" />
          <p v-if="added" role="status" class="text-content-body">{{ quantity }} × {{ color }} Apple Bundle added to your demo cart.</p>
        </form></Card
      >
    </div>
    <Card variant="outline"
      ><h3 class="mb-4 text-lg font-semibold text-content-heading">Other Products</h3>
      <div class="grid gap-4 sm:grid-cols-3">
        <div v-for="name in ['Christopher Knight Home', 'Bar Height Swivel Barstool', 'Signature Design by Ashley']" :key="name" class="bg-surface-subtle rounded-xl p-5">
          <PhArmchair aria-hidden="true" class="mb-3 size-12 text-primary" />
          <h4 class="font-semibold text-content-heading">{{ name }}</h4>
          <p class="mt-2 text-sm text-content-subtle">Modern furniture collection</p>
        </div>
      </div></Card
    >
    <UModal v-model:open="gallery" title="Product preview" description="Authored icons represent the licensed product photos."
      ><template #body
        ><div class="grid place-items-center gap-5">
          <component :is="views[selected]?.icon" aria-hidden="true" class="size-48 text-primary" />
          <p class="text-content-heading">{{ views[selected]?.label }}</p>
          <div class="flex gap-3"><UButton label="Previous image" variant="outline" @click="selected = (selected + 3) % 4" /><UButton label="Next image" variant="outline" @click="selected = (selected + 1) % 4" /></div></div></template
    ></UModal>
  </div>
</template>
