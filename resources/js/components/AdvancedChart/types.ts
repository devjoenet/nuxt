export interface AdvancedChartPoint {
  label?: string;
  x: number;
  y: number;
}

export interface AdvancedChartProps {
  ariaLabel: string;
  class?: string;
  color?: string;
  data: AdvancedChartPoint[];
  height?: number;
}
