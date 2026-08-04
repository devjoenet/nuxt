import type { AdminUser } from "@/types";

export interface AdminUsersPage {
  data: AdminUser[];
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
