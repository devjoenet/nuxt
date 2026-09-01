<script setup lang="ts">
import { AdvancedCalendar } from "@/components/AdvancedCalendar";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { AdvancedDateTime } from "@/components/AdvancedDateTime";
import { LazyAdvancedEditor } from "@/components/AdvancedEditor";
import { AdvancedKanban, type AdvancedKanbanColumn, type AdvancedKanbanMove } from "@/components/AdvancedKanban";
import { LazyAdvancedMap } from "@/components/AdvancedMap";
import { FormFileUpload } from "@/components/Ui/FormFileUpload";
import { FormSelect } from "@/components/Ui/FormSelect";
import { advancedBehaviorCapabilities, type AdvancedBehaviorCapabilityId } from "@/lib/advancedBehaviorCapabilities";
import { computed, ref } from "vue";

const capability = ref<AdvancedBehaviorCapabilityId>("chart");
const selected = computed(() => advancedBehaviorCapabilities.find((item) => item.id === capability.value)!);
const capabilityItems = advancedBehaviorCapabilities.map((item) => ({ label: item.id, value: item.id }));
const calendarDate = ref("2026-09-01");
const date = ref("2026-09-01");
const time = ref("09:30");
const editorContent = ref("<p>Typed HTML stays behind the adapter boundary.</p>");
const selectValue = ref<string | null>("adapted");
const page = ref(1);
const columns = ref<AdvancedKanbanColumn[]>([
  { id: "planned", title: "Planned", cards: [{ id: "gallery", title: "Protected gallery", description: "Permission and manifest shell" }] },
  { id: "testing", title: "Testing", cards: [{ id: "adapters", title: "Adapter coverage" }] },
]);

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
  <section aria-labelledby="advanced-lab-title" class="grid gap-5">
    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_16rem] sm:items-end">
      <div>
        <h3 id="advanced-lab-title" class="text-balance text-lg font-semibold text-content-heading">Advanced adapters</h3>
        <p class="text-pretty text-base text-content-body sm:text-sm">Select a capability to mount only its typed specimen.</p>
      </div>
      <FormSelect v-model="capability" :items="capabilityItems" label="Capability" name="advanced-capability" />
    </div>
    <div class="flex flex-wrap gap-2"><UBadge :color="selected.status === 'adapted' ? 'success' : 'neutral'" :label="selected.status" variant="subtle" /><UBadge :label="selected.lazy ? 'lazy runtime' : 'shared foundation'" variant="subtle" /></div>
    <LazyAdvancedChart
      v-if="capability === 'chart'"
      v-bind="{ ariaLabel: 'Gallery delivery trend' }"
      :data="[
        { x: 1, y: 8, label: 'Planned' },
        { x: 2, y: 13, label: 'Built' },
        { x: 3, y: 18, label: 'Verified' },
      ]"
    />
    <AdvancedDateTime v-else-if="capability === 'date-time'" v-model:date="date" v-model:time="time" />
    <AdvancedKanban v-else-if="capability === 'kanban'" v-bind="{ ariaLabel: 'Gallery task board' }" :columns="columns" @move="moveCard" />
    <AdvancedCalendar v-else-if="capability === 'calendar'" v-model="calendarDate" v-bind="{ ariaLabel: 'Reference date' }" />
    <LazyAdvancedEditor v-else-if="capability === 'editor'" v-model="editorContent" v-bind="{ ariaLabel: 'Divergence notes' }" />
    <LazyAdvancedMap v-else-if="capability === 'map'" v-bind="{ ariaLabel: 'Southeast reference locations' }" :markers="[{ id: 'atlanta', label: 'Atlanta', latitude: 33.749, longitude: -84.388 }]" />
    <FormSelect v-else-if="capability === 'select'" v-model="selectValue" :items="['adapted', 'foundation']" label="Adapter status" name="adapter-status" />
    <FormFileUpload v-else-if="capability === 'upload'" label="Upload inert specimen" name="adapter-upload" />
    <UTable v-else-if="capability === 'table'" :data="advancedBehaviorCapabilities.slice(0, 5)" />
    <UPagination v-else-if="capability === 'pagination'" v-model:page="page" :items-per-page="10" :total="69" />
    <UAlert v-else color="neutral" :description="selected.implementation" title="Shared Nuxt UI foundation" variant="subtle" />
  </section>
</template>
