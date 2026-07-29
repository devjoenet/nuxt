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

export interface DevelopmentTaskLink {
  id: string;
  type: string;
  type_label: string;
  label: string | null;
  url: string;
  added_by: string;
  created_at: string | null;
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
  roadmap_position: number | null;
  submitter: string;
  reviewer: string | null;
  assignee: string | null;
  assignee_id: string | null;
  submitted_at: string;
  updated_at: string;
  notes?: DevelopmentTaskNote[];
  links?: DevelopmentTaskLink[];
  can: {
    review: boolean;
    manage_roadmap: boolean;
    update_progress: boolean;
    attach_link: boolean;
    add_participant_note: boolean;
    add_internal_note: boolean;
  };
}

export interface DevelopmentRoadmapTasks {
  planned: DevelopmentTask[];
  in_progress: DevelopmentTask[];
  blocked: DevelopmentTask[];
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
