import type { FormSelectItem } from "@/components/Ui/FormSelect";
import type { ArgonGalleryExample, ArgonGalleryFamily, ArgonGallerySourceStatus } from "@/types/argonGallery";
import { computed, ref } from "vue";
import { galleryFamilies, galleryLabel } from "./galleryCatalog";

type FamilyFilter = ArgonGalleryFamily | "all";
type StatusFilter = ArgonGallerySourceStatus | "all";

export function useGalleryFilters(examples: ArgonGalleryExample[], familyCounts: Record<ArgonGalleryFamily, number>) {
  const query = ref("");
  const family = ref<FamilyFilter>("all");
  const status = ref<StatusFilter>("all");
  const familyItems: FormSelectItem[] = [{ label: "All families", value: "all" }, ...galleryFamilies.map((value) => ({ label: `${galleryLabel(value)} (${familyCounts[value]})`, value }))];
  const statusItems: FormSelectItem[] = [
    { label: "All source states", value: "all" },
    { label: "Available", value: "available" },
    { label: "Empty source", value: "empty" },
  ];
  const filtered = computed(() => {
    const needle = query.value.trim().toLocaleLowerCase();

    return examples.filter((example) => {
      const matchesQuery = !needle || [example.title, example.id, example.source_path, ...example.behaviors].some((value) => value.toLocaleLowerCase().includes(needle));

      return matchesQuery && (family.value === "all" || example.family === family.value) && (status.value === "all" || example.source_status === status.value);
    });
  });
  const groups = computed(() => galleryFamilies.map((value) => ({ family: value, label: galleryLabel(value), examples: filtered.value.filter((example) => example.family === value) })).filter((group) => group.examples.length > 0));

  return { family, familyItems, filtered, groups, query, status, statusItems };
}
