<script setup lang="ts">
import { AdvancedKanban, type AdvancedKanbanColumn, type AdvancedKanbanMove } from "@/components/AdvancedKanban";
import { FormInput } from "@/components/Ui/FormInput";
import { ref } from "vue";
const title = ref("");
const columns = ref<AdvancedKanbanColumn[]>([
  {
    id: "backlog",
    title: "Backlog",
    cards: [
      { id: "design", title: "Design new dashboard", description: "Review the initial wireframes" },
      { id: "pricing", title: "Update pricing page", description: "Prepare revised plans" },
    ],
  },
  { id: "progress", title: "In progress", cards: [{ id: "api", title: "API integration", description: "Connect the demo interface" }] },
  { id: "review", title: "Review", cards: [{ id: "mobile", title: "Mobile layout", description: "Check responsive states" }] },
  { id: "done", title: "Done", cards: [{ id: "research", title: "User research", description: "Summarize interviews" }] },
]);
let nextId = 1;
function addTask(): void {
  if (!title.value.trim()) return;
  columns.value[0]?.cards.push({ id: `demo-${nextId++}`, title: title.value.trim() });
  title.value = "";
}
function moveCard(move: AdvancedKanbanMove): void {
  const source = columns.value.find((column) => column.id === move.fromColumnId);
  const target = columns.value.find((column) => column.id === move.toColumnId);
  const index = source?.cards.findIndex((card) => card.id === move.cardId) ?? -1;
  if (!source || !target || index < 0) return;
  const [card] = source.cards.splice(index, 1);
  if (card) target.cards.splice(move.toIndex, 0, card);
}
</script>
<template>
  <div class="grid min-w-0 gap-5">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h3 class="text-xl font-semibold text-content-heading">Argon development board</h3>
        <p class="text-sm text-content-subtle">Drag cards, or focus a card and use Left / Right Arrow.</p>
      </div>
      <form class="flex flex-wrap items-end gap-3" @submit.prevent="addTask"><FormInput v-model="title" label="Task title" name="demo-task-title" required /><UButton type="submit" label="Add demo task" /></form>
    </div>
    <AdvancedKanban v-bind="{ ariaLabel: 'Reference project board' }" :columns="columns" @move="moveCard" />
  </div>
</template>
