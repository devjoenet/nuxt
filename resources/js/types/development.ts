export type DevelopmentOption = {
  label: string;
  value: string;
};

export interface DevelopmentTaskNote {
  id: string;
  body: string;
  audience: "participants" | "development_team";
  audience_label: string;
  author: string;
  created_at: string;
}

export interface DevelopmentTask {
  id: string;
  title: string;
  request_type: string;
  request_type_label: string;
  affected_area: string;
  affected_area_label: string;
  description: string;
  desired_outcome: string;
  acceptance_criteria: string;
  status: string;
  status_label: string;
  priority: string;
  priority_label: string;
  submitter: string;
  reviewer: string | null;
  assignee: string | null;
  submitted_at: string;
  updated_at: string;
  notes?: DevelopmentTaskNote[];
  can: {
    review: boolean;
    add_participant_note: boolean;
    add_internal_note: boolean;
  };
}

export interface PaginatedDevelopmentTasks {
  data: DevelopmentTask[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links: {
    prev: string | null;
    next: string | null;
  };
}
