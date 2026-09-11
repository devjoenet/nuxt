import type { AdminUser } from "@/types";
import type { PaginationMeta } from "@/types/pagination";

export interface AdminUsersPage {
  data: AdminUser[];
  meta: PaginationMeta;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

export type AdminRoleOption = {
  label: string;
  value: string;
  [key: string]: unknown;
};
