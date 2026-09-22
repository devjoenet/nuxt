<script setup lang="ts">
import { Alert } from "@/components/Ui/Alert";
import { Badge } from "@/components/Ui/Badge";
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { Icon } from "@/components/Ui/Icon";
import { Typography } from "@/components/Ui/Typography";

defineProps<{ specimen: "buttons" | "cards" | "grid" | "icons" | "notifications" | "typography" }>();
const icons = ["i-ph-house", "i-ph-chart-line", "i-ph-users", "i-ph-calendar", "i-ph-bell", "i-ph-gear", "i-ph-map-pin", "i-ph-envelope"];
const tones = ["primary", "secondary", "success", "info", "warning", "error"] as const;
</script>

<template>
  <div class="grid gap-5">
    <UAlert color="info" description="The licensed file is a redirect stub. This specimen records the intended component family using the shared typed Argon primitives." title="Adapted from source intent" variant="subtle" />
    <section v-if="specimen === 'buttons'" aria-label="Button specimens" class="grid gap-5">
      <div class="flex flex-wrap gap-3">
        <Button label="Primary action" /><Button label="Secondary" tone="neutral" variant="outline" /><Button label="Soft action" variant="soft" /><Button disabled label="Disabled" tone="neutral" variant="outline" /><Button label="Loading" loading variant="soft" />
      </div>
      <div class="flex flex-wrap gap-3"><Button v-for="tone in tones" :key="tone" :label="tone" :tone="tone" variant="outline" /></div>
    </section>
    <section v-else-if="specimen === 'cards'" aria-label="Card specimens" class="grid gap-5 md:grid-cols-3">
      <Card v-for="variant in ['outline', 'soft', 'glass'] as const" :key="variant" class="grid content-between gap-5" :variant="variant"
        ><div class="grid gap-2">
          <Badge :label="variant" variant="soft" />
          <h3 class="text-lg font-semibold text-balance text-content-heading">{{ variant }} surface</h3>
          <p class="text-base text-pretty text-content-body sm:text-sm">Shared padding, semantic tones, and a stable content hierarchy.</p>
        </div>
        <Button label="Inspect state" tone="neutral" variant="ghost"
      /></Card>
    </section>
    <section v-else-if="specimen === 'grid'" aria-label="Responsive grid specimen" class="grid gap-4">
      <div class="grid grid-cols-12 gap-2">
        <div v-for="column in 12" :key="column" class="grid min-h-20 place-items-center rounded-lg border border-content-border bg-surface-interactive text-base font-medium text-content-heading tabular-nums sm:text-sm">{{ column }}</div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card v-for="(width, index) in ['One half', 'One half', 'One third', 'One third', 'One third']" :key="`${width}-${index}`" variant="outline"
          ><p class="text-base text-content-body sm:text-sm">{{ width }}</p></Card
        >
      </div>
    </section>
    <section v-else-if="specimen === 'icons'" aria-label="Icon specimens" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <Card v-for="icon in icons" :key="icon" class="grid place-items-center gap-3" variant="outline"
        ><Icon decorative :name="icon" size="lg" />
        <p class="text-center text-base text-content-body sm:text-sm">{{ icon.replace("i-ph-", "") }}</p></Card
      >
    </section>
    <section v-else-if="specimen === 'notifications'" aria-label="Notification component specimens" class="grid gap-3">
      <Alert v-for="tone in tones" :key="tone" :description="`${tone} feedback remains visible and screen-reader announced.`" :title="`${tone} notification`" :tone="tone" variant="soft" />
    </section>
    <section v-else aria-label="Typography specimens" class="grid gap-5">
      <Typography v-for="variant in ['display', 'h1', 'h2', 'h3', 'h4'] as const" :key="variant" as="h3" :variant="variant">Argon {{ variant }} specimen</Typography>
      <Typography class="max-w-[56ch]" variant="lead">Strong hierarchy, readable measure, and semantic content tokens carry the visual direction without copying licensed styles.</Typography>
      <Typography class="max-w-[56ch]">Body copy stays comfortable across mobile and desktop while preserving the app’s shared typography contract.</Typography>
    </section>
  </div>
</template>
