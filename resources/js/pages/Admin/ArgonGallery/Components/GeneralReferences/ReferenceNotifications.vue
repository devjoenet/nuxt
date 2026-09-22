<script setup lang="ts">
import { Alert } from "@/components/Ui/Alert";
import { Button } from "@/components/Ui/Button";

const toast = useToast();
const tones = ["success", "info", "warning", "error"] as const;

function notify(tone: (typeof tones)[number]): void {
  toast.add({ title: `${tone.charAt(0).toUpperCase()}${tone.slice(1)} notification`, description: "This inert gallery action uses the shared application toast provider.", color: tone });
}
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-2">
    <section aria-labelledby="notification-alerts" class="grid content-start gap-3">
      <h3 id="notification-alerts" class="text-lg font-semibold text-balance text-content-heading">Persistent alerts</h3>
      <Alert v-for="tone in tones" :key="tone" :description="`${tone} context remains visible until the surrounding state changes.`" :title="`${tone} alert`" :tone="tone" variant="soft" />
    </section>
    <section aria-labelledby="notification-toasts" class="grid content-start gap-3">
      <h3 id="notification-toasts" class="text-lg font-semibold text-balance text-content-heading">Ephemeral notifications</h3>
      <p class="text-base text-pretty text-content-body sm:text-sm">Trigger the four feedback states represented by the source page.</p>
      <div class="grid gap-3 sm:grid-cols-2"><Button v-for="(tone, index) in tones" :key="tone" :label="`${tone} notification`" :tone="tone" :variant="index === 0 ? 'solid' : 'outline'" @click="notify(tone)" /></div>
    </section>
  </div>
</template>
