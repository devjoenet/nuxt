<script setup lang="ts">
import { LazyAdvancedEditor } from "@/components/AdvancedEditor";
import { Alert } from "@/components/Ui/Alert";
import { Card } from "@/components/Ui/Card";
import { FormFileUpload, type FormFileValue } from "@/components/Ui/FormFileUpload";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { computed, ref } from "vue";
const props = defineProps<{ editing?: boolean }>();
const step = ref(0);
const steps = ["Product Information", "Media", "Socials", "Pricing"];
const name = ref(props.editing ? "Minimal Bar Stool" : "");
const weight = ref(2);
const description = ref("<p>Some initial <strong>bold</strong> text</p>");
const category = ref("Furniture");
const size = ref("Medium");
const images = ref<FormFileValue>(null);
const handle = ref("");
const facebook = ref("");
const instagram = ref("");
const price = ref(99);
const currency = ref("USD");
const sku = ref("0134729");
const saved = ref(false);
const tags = ref(["In stock"]);
const imageCount = computed(() => (Array.isArray(images.value) ? images.value.length : images.value ? 1 : 0));
function submit(): void {
  if (!props.editing && step.value < 3) {
    step.value++;
    return;
  }
  saved.value = true;
}
</script>
<template>
  <form class="grid gap-5" @submit.prevent="submit">
    <header class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-2xl font-semibold text-content-heading">{{ editing ? "Make the changes below" : "New product" }}</h3>
        <p class="text-sm text-content-subtle">Preview your product with local demo data.</p>
      </div>
      <UButton v-if="editing" type="submit" label="Save demo changes" />
    </header>
    <ol v-if="!editing" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
      <li v-for="(label, index) in steps" :key="label" :aria-current="step === index ? 'step' : undefined" :class="['border-b-2 pb-3 text-sm', step === index ? 'border-primary font-semibold text-primary' : 'border-content-border text-content-subtle']">{{ index + 1 }}. {{ label }}</li>
    </ol>
    <Card v-if="editing || step === 0" variant="outline"
      ><h4 class="mb-5 text-lg font-semibold text-content-heading">Product Information</h4>
      <div class="grid gap-4 sm:grid-cols-2">
        <FormInput v-model="name" label="Product name" name="demo-product-name" required /><FormInput v-model="weight" label="Weight (kg)" type="number" min="0" step="0.1" />
        <div class="grid min-w-0 gap-3 sm:col-span-2">
          <p class="text-sm text-content-body">Description</p>
          <LazyAdvancedEditor v-model="description" v-bind="{ ariaLabel: 'Product description' }" />
        </div>
        <FormSelect v-model="category" label="Category" :items="['Clothing', 'Real Estate', 'Electronics', 'Furniture', 'Others']" /><FormSelect v-model="size" label="Size" :items="['Small', 'Medium', 'Large', 'Extra Large']" /></div
    ></Card>
    <Card v-if="editing || step === 1" variant="outline"
      ><h4 class="mb-5 text-lg font-semibold text-content-heading">Media</h4>
      <FormFileUpload v-model="images" label="Product images" name="demo-product-images" accept="image/*" multiple><template #label>Select product images or drop them here</template></FormFileUpload>
      <p role="status" class="mt-3 text-sm text-content-subtle">{{ imageCount }} images selected · files remain on your device.</p></Card
    >
    <Card v-if="editing || step === 2" variant="outline"
      ><h4 class="mb-5 text-lg font-semibold text-content-heading">Socials</h4>
      <div class="grid gap-4 sm:grid-cols-3"><FormInput v-model="handle" label="Shopify handle" /><FormInput v-model="facebook" label="Facebook account" /><FormInput v-model="instagram" label="Instagram account" /></div
    ></Card>
    <Card v-if="editing || step === 3" variant="outline"
      ><h4 class="mb-5 text-lg font-semibold text-content-heading">Pricing</h4>
      <div class="grid gap-4 sm:grid-cols-3"><FormInput v-model="price" label="Price" type="number" min="0" step="0.01" required /><FormSelect v-model="currency" label="Currency" :items="['USD', 'EUR', 'GBP', 'CNY', 'INR']" /><FormInput v-model="sku" label="SKU" required /></div>
      <fieldset class="mt-5">
        <legend class="mb-3 text-sm text-content-body">Tags</legend>
        <UCheckboxGroup v-model="tags" :items="['In stock', 'Sale', 'Black Friday']" /></fieldset
    ></Card>
    <div v-if="!editing" class="flex justify-between gap-3"><UButton label="Previous" color="neutral" variant="outline" :disabled="step === 0" @click="step--" /><UButton type="submit" :label="step === 3 ? 'Create demo product' : 'Next'" /></div>
    <Alert v-if="saved" tone="success" variant="outline" title="Demo product saved" :description="`${name} · ${currency} ${price}. Changes last for this visit only.`" />
  </form>
</template>
