<script lang="ts" setup>
import { cn } from "@/lib/utils";
import type { AdvancedKanbanColumn, AdvancedKanbanMove } from "./types";
import { useAccessibleKanban } from "./useAccessibleKanban";

const props = defineProps<{ ariaLabel: string; class?: string; columns: AdvancedKanbanColumn[] }>();
const emit = defineEmits<{ move: [move: AdvancedKanbanMove] }>();
const { announcement, dragging, onDragStart, onDrop, onKeydown, reducedMotion } = useAccessibleKanban(
  () => props.columns,
  (move) => emit("move", move),
);
</script>

<template>
  <section :aria-label="ariaLabel" :class="cn('grid gap-4 overflow-x-auto md:grid-flow-col md:auto-cols-[minmax(16rem,1fr)]', props.class)">
    <p id="kanban-keyboard-help" class="sr-only">Focus a card and use Left or Right Arrow to move it between columns.</p>
    <section v-for="(column, columnIndex) in columns" :key="column.id" class="grid content-start gap-3 rounded-xl border border-content-border bg-surface-subtle p-3" @dragover.prevent @drop="onDrop(column.id)">
      <h3 class="font-semibold text-content-heading">
        {{ column.title }} <span class="text-sm font-normal text-content-subtle">{{ column.cards.length }}</span>
      </h3>
      <p v-if="column.cards.length === 0" class="rounded-lg border border-dashed border-content-border p-4 text-sm text-content-subtle">No cards in this column.</p>
      <button
        v-for="card in column.cards"
        :key="card.id"
        :aria-describedby="'kanban-keyboard-help'"
        :class="['rounded-lg border border-content-border bg-surface-elevated p-3 text-left shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary', reducedMotion === 'reduce' ? 'transition-none' : 'transition hover:-translate-y-0.5']"
        :data-kanban-card="card.id"
        draggable="true"
        type="button"
        @dragend="dragging = null"
        @dragstart="onDragStart(card.id, column.id)"
        @keydown="onKeydown(card, columnIndex, $event)"
      >
        <span class="block font-medium text-content-heading">{{ card.title }}</span
        ><span v-if="card.description" class="mt-1 block text-sm text-content-subtle">{{ card.description }}</span>
      </button>
    </section>
    <p aria-live="polite" class="sr-only">{{ announcement }}</p>
  </section>
</template>
