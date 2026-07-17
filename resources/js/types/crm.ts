export type Option = { label: string; value: string };
export type Owner = { id: number; name: string };

export interface Lead {
  id: string;
  status: string;
  source: string;
  service_interest: string | null;
  service_interests: string[];
  budget_range: string | null;
  timeline: string | null;
  project_details: string;
  archived_at: string | null;
  converted_at: string | null;
  created_at: string;
  contact: { id: string; name: string; email: string; phone: string | null };
  organization: { id: string; name: string; website: string | null; client_since_at: string | null } | null;
  owner: Owner | null;
  notes?: Array<{ id: string; body: string; author: string; created_at: string }>;
  activities?: Array<{ id: string; type: string; description: string; created_at: string }>;
  tasks?: Array<{ id: string; title: string; status: string; priority: string; due_at: string }>;
}

export interface Organization {
  id: string;
  name: string;
  website: string | null;
  archived_at: string | null;
  client_since_at: string | null;
  created_at: string;
  contacts_count?: number;
  leads_count?: number;
  owner: Owner | null;
  contacts?: Array<{ id: string; name: string; email: string; phone: string | null; owner: Owner | null }>;
}

export interface Contact {
  id: string;
  name: string;
  email: string;
  phone: string | null;
  organization_id: number | null;
  organization: { id: string; name: string } | null;
  owner: Owner | null;
  archived_at: string | null;
  created_at: string;
  leads_count?: number;
}

export interface Paginated<T> {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
  links: { prev: string | null; next: string | null };
}
