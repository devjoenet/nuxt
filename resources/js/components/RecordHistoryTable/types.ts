import type { PaginationMeta } from "@/types/pagination";

export type HistoryEntry = {
  id: number;
  action: string;
  subject: string;
  actor: string;
  changes: { attributes?: Record<string, unknown>; old?: Record<string, unknown> };
  created_at: string | null;
};
export type HistoryPage = PaginationMeta & { data: HistoryEntry[] };
