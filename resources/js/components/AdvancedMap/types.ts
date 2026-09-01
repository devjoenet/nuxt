export interface AdvancedMapMarker {
  id: string;
  label: string;
  latitude: number;
  longitude: number;
}

export interface AdvancedMapProps {
  ariaLabel: string;
  center?: [longitude: number, latitude: number];
  class?: string;
  markers?: AdvancedMapMarker[];
  styleUrl?: string;
  zoom?: number;
}
