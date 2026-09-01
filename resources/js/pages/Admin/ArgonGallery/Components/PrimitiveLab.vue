<script setup lang="ts">
import { Badge } from "@/components/Ui/Badge";
import { Button, type ButtonSize, type ButtonVariant } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { uiTones, type UiTone } from "@/lib/uiTone";
import { ref } from "vue";

const tone = ref<UiTone>("primary");
const variant = ref<ButtonVariant>("solid");
const size = ref<ButtonSize>("md");
const disabled = ref(false);
const loading = ref(false);
const toneItems = uiTones.map((value) => ({ label: value, value }));
const variantItems = ["solid", "soft", "outline", "ghost", "link"].map((value) => ({ label: value, value }));
const sizeItems = ["xs", "sm", "md", "lg"].map((value) => ({ label: value, value }));
</script>

<template>
  <section aria-labelledby="primitive-lab-title" class="grid gap-5">
    <div>
      <h3 id="primitive-lab-title" class="text-balance text-lg font-semibold text-content-heading">Primitive states</h3>
      <p class="text-pretty text-base text-content-body sm:text-sm">Change the typed primitive contract and inspect the composed result.</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-3">
      <FormSelect v-model="tone" :items="toneItems" label="Tone" name="primitive-tone" /><FormSelect v-model="variant" :items="variantItems" label="Variant" name="primitive-variant" /><FormSelect v-model="size" :items="sizeItems" label="Size" name="primitive-size" />
    </div>
    <div class="grid gap-3 sm:grid-cols-2"><FormSwitch v-model="disabled" label="Disabled" name="primitive-disabled" /><FormSwitch v-model="loading" label="Loading" name="primitive-loading" /></div>
    <Card class="grid gap-4" tone="neutral" variant="outline"
      ><div class="flex flex-wrap items-center gap-3"><Button :disabled="disabled" label="Typed action" :loading="loading" :size="size" :tone="tone" :variant="variant" /><Badge dot label="Current tone" :tone="tone" variant="soft" /></div>
      <p class="text-base text-content-body sm:text-sm">Buttons, badges, and surfaces share the same semantic tone without leaking vendor props.</p></Card
    >
  </section>
</template>
