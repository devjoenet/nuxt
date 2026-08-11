export interface RoadmapColumn {
  description: string;
  empty: string;
  icon: string;
  key: "planned" | "in_progress" | "blocked";
  title: string;
}
