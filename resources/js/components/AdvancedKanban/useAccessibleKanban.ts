import { usePreferredReducedMotion } from "@vueuse/core";
import { nextTick, onBeforeUnmount, ref } from "vue";
import type { AdvancedKanbanCard, AdvancedKanbanColumn, AdvancedKanbanMove } from "./types";

export function useAccessibleKanban(columns: () => AdvancedKanbanColumn[], onMove: (move: AdvancedKanbanMove) => void) {
  const announcement = ref("");
  const dragging = ref<{ cardId: string; columnId: string } | null>(null);
  const reducedMotion = usePreferredReducedMotion();

  async function move(card: AdvancedKanbanCard, fromColumnIndex: number, toColumnIndex: number): Promise<void> {
    const currentColumns = columns();
    const destination = currentColumns[toColumnIndex];
    const source = currentColumns[fromColumnIndex];
    if (!destination || !source || destination.id === source.id) return;
    onMove({ cardId: card.id, fromColumnId: source.id, toColumnId: destination.id, toIndex: destination.cards.length });
    announcement.value = `${card.title} moved to ${destination.title}.`;
    await nextTick();
    requestAnimationFrame(() => document.querySelector<HTMLElement>(`[data-kanban-card="${CSS.escape(card.id)}"]`)?.focus());
  }

  function onKeydown(card: AdvancedKanbanCard, columnIndex: number, event: KeyboardEvent): void {
    if (!["ArrowLeft", "ArrowRight"].includes(event.key)) return;
    event.preventDefault();
    void move(card, columnIndex, columnIndex + (event.key === "ArrowLeft" ? -1 : 1));
  }

  function onDragStart(cardId: string, columnId: string, event: DragEvent): void {
    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = "move";
      event.dataTransfer.setData("text/plain", cardId);
    }
    dragging.value = { cardId, columnId };
  }

  function onDrop(columnId: string): void {
    const active = dragging.value;
    const currentColumns = columns();
    if (!active || active.columnId === columnId) return;
    const source = currentColumns.find((column) => column.id === active.columnId);
    const destination = currentColumns.find((column) => column.id === columnId);
    const card = source?.cards.find((item) => item.id === active.cardId);
    if (source && destination && card) {
      onMove({ cardId: card.id, fromColumnId: source.id, toColumnId: destination.id, toIndex: destination.cards.length });
      announcement.value = `${card.title} moved to ${destination.title}.`;
    }
    dragging.value = null;
  }

  onBeforeUnmount(() => {
    dragging.value = null;
  });

  return { announcement, dragging, onDragStart, onDrop, onKeydown, reducedMotion };
}
