export type ArgonGalleryFamily = "account" | "applications" | "authentication" | "dashboards" | "ecommerce" | "general" | "projects" | "team";
export type ArgonGallerySourceStatus = "available" | "empty";
export type ArgonGalleryLabId = "foundations" | "primitives" | "forms" | "layouts" | "advanced";
export type ArgonGalleryViewport = "mobile" | "tablet" | "desktop" | "full";

export interface ArgonGallerySummary {
  application_use: string;
  available: number;
  complete: boolean;
  empty: number;
  families: number;
  product: string;
  routable: number;
  snapshot: string;
  source_publication: string;
  total: number;
  version: string;
}

export interface ArgonGalleryExample {
  adaptation_state: "adapted";
  behaviors: string[];
  consuming_pages: string[];
  dependencies: string[];
  divergence_note: string;
  family: ArgonGalleryFamily;
  id: string;
  layout: string;
  planned_vue_destination: string;
  source_note: string;
  source_path: string;
  source_status: ArgonGallerySourceStatus;
  title: string;
}
