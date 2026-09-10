<script setup lang="ts">
import { Card } from "@/components/Ui/Card";
import { show } from "@/routes/admin/argonGallery/examples";
</script>
<template>
  <div class="grid gap-5">
    <UAlert title="Landing source contains an empty content grid" description="The licensed landing.html includes navigation and empty columns, with no dashboard widgets to port. These links open the implemented dashboard examples." color="neutral" variant="subtle" />
    <div class="grid gap-4 sm:grid-cols-2">
      <Card
        v-for="dashboard in [
          { id: 'default', label: 'Default' },
          { id: 'crm', label: 'CRM' },
          { id: 'automotive', label: 'Automotive' },
          { id: 'smart-home', label: 'Smart Home' },
        ]"
        :key="dashboard.id"
        variant="outline"
        ><UButton :to="show(`dashboards-${dashboard.id}`).url" :label="dashboard.label" trailing-icon="i-ph-arrow-right" variant="ghost"
      /></Card>
    </div>
  </div>
</template>
