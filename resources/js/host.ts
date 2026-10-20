export const HOST_API_VERSION = 1 as const;
export { WorkspacePage } from "@/components/WorkspacePage";
export { useInertiaFilters } from "@/composables/useInertiaFilters";
export type { InertiaFilterValue } from "@/composables/useInertiaFilters";
export { resolveAppLayout } from "@/layouts/resolveAppLayout";
export type { Paginated, PaginationMeta } from "@/types/pagination";

export interface WorkspaceUserIdentity {
  id: string;
  name: string;
  email: string;
  is_active: boolean;
  can: Record<string, boolean>;
}
