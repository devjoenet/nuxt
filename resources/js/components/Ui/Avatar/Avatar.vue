<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { AvatarProps } from "./types";
import { avatarVariants } from "./variants";

const props = withDefaults(defineProps<AvatarProps>(), { size: "md", shape: "circle" });
const initials = computed(
  () =>
    props.name
      ?.split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map((part) => part[0]?.toUpperCase())
      .join("") ?? "",
);
const avatarClass = computed(() => cn(avatarVariants({ size: props.size, shape: props.shape }), props.class));
const statusClass = computed(() => ({ online: "bg-success", offline: "bg-grey-400", busy: "bg-error" })[props.status ?? "offline"]);
</script>

<template>
  <span :class="avatarClass">
    <img v-if="src" :alt="alt ?? name ?? ''" class="size-full rounded-[inherit] object-cover" height="64" :src="src" width="64" />
    <span v-else :aria-hidden="!(alt ?? name) || undefined" :aria-label="alt ?? name" :role="alt || name ? 'img' : undefined">{{ initials }}</span>
    <span v-if="status" :aria-label="status" :class="statusClass" class="absolute right-0 bottom-0 size-2.5 rounded-full ring-2 ring-surface-elevated" role="status" />
  </span>
</template>
