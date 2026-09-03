<script setup lang="ts">
import { EmptyState } from "@/components/Ui/EmptyState";

defineProps<{ title: string }>();
</script>

<template>
  <EmptyState :description="`The licensed ${title} HTML file is empty. This dedicated Vue destination preserves route and manifest completeness without inventing source content.`" icon="i-ph-file-dashed" :title="`${title} source unavailable`" />
</template>
