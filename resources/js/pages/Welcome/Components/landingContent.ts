export interface LandingCard {
  title: string;
  description: string;
  detail: string;
  icon: string;
  tone: "primary" | "accent" | "secondary" | "warning";
}

export interface ServiceItem extends LandingCard {
  proof: string;
}

export interface ProcessStep {
  title: string;
  description: string;
  icon: string;
  tone: "primary" | "secondary" | "accent" | "warning";
}

export interface FooterColumn {
  title: string;
  links: Array<{
    label: string;
    href: string;
  }>;
}

export const realityCards: LandingCard[] = [
  {
    title: "Spreadsheet chains",
    description: "Too many versions.",
    detail: "Too many dead ends.",
    icon: "i-ph-stack",
    tone: "secondary",
  },
  {
    title: "Disconnected tools",
    description: "Data trapped in silos.",
    detail: "No single source of truth.",
    icon: "i-ph-flow-arrow",
    tone: "primary",
  },
  {
    title: "Legacy bottlenecks",
    description: "Manual workarounds.",
    detail: "Slow, risky, expensive.",
    icon: "i-ph-warning-circle",
    tone: "accent",
  },
];

export const transformationCards: LandingCard[] = [
  {
    title: "Centralized workflows",
    description: "One system. Clear steps.",
    detail: "Built around your process.",
    icon: "i-ph-flow-arrow",
    tone: "primary",
  },
  {
    title: "Reliable data",
    description: "Clean, real-time, and ready for decisions.",
    detail: "No guessing from stale exports.",
    icon: "i-ph-database",
    tone: "secondary",
  },
  {
    title: "Role-based access",
    description: "Right people. Right information.",
    detail: "Every time.",
    icon: "i-ph-shield-check",
    tone: "accent",
  },
];

export const services: ServiceItem[] = [
  {
    title: "Dashboards & portals",
    description: "Real-time visibility for teams, customers, and partners.",
    detail: "Centralized operational hubs.",
    proof: "Live work queues, status views, and approval portals.",
    icon: "i-ph-layout",
    tone: "primary",
  },
  {
    title: "Internal tools",
    description: "Custom apps that replace spreadsheets and repetitive daily operational work.",
    detail: "Built around the actual workflow.",
    proof: "Daily load plans, dock checks, and driver payouts.",
    icon: "i-ph-key",
    tone: "secondary",
  },
  {
    title: "System integration",
    description: "Connect the tools you depend on. Automate data flow. Eliminate manual re-entry.",
    detail: "APIs, files, and background jobs.",
    proof: "Shared records across the systems already in place.",
    icon: "i-ph-plugs-connected",
    tone: "primary",
  },
  {
    title: "Reporting & data visibility",
    description: "Operational reporting that answers the right questions fast.",
    detail: "Metrics people can act on.",
    proof: "Trends, exceptions, and performance snapshots.",
    icon: "i-ph-chart-bar",
    tone: "secondary",
  },
  {
    title: "Legacy modernization",
    description: "Modernize outdated systems without disrupting the business.",
    detail: "More stable. More secure.",
    proof: "Replace brittle workflows one useful slice at a time.",
    icon: "i-ph-lightning",
    tone: "warning",
  },
  {
    title: "Progressive web apps",
    description: "Reliable on any device. Works offline, installs in seconds. Always up to date.",
    detail: "Field-ready web software.",
    proof: "Mobile check-ins, photo capture, and offline queues.",
    icon: "i-ph-device-mobile",
    tone: "primary",
  },
];

export const processSteps: ProcessStep[] = [
  {
    title: "Observe workflows",
    description: "We learn how your teams actually work from end to end.",
    icon: "i-ph-binoculars",
    tone: "primary",
  },
  {
    title: "Model the data",
    description: "We structure the data and rules that power the process.",
    icon: "i-ph-database",
    tone: "secondary",
  },
  {
    title: "Build the tool",
    description: "We build secure, tested software that fits.",
    icon: "i-ph-hammer",
    tone: "accent",
  },
  {
    title: "Improve with evidence",
    description: "We refine based on real usage and measurable outcomes.",
    icon: "i-ph-chart-line-up",
    tone: "warning",
  },
];

export const footerColumns: FooterColumn[] = [
  {
    title: "Solutions",
    links: [
      { label: "Dashboards & portals", href: "#services" },
      { label: "Internal tools", href: "#services" },
      { label: "System integration", href: "#services" },
      { label: "Reporting", href: "#services" },
      { label: "Legacy modernization", href: "#services" },
      { label: "Progressive web apps", href: "#services" },
    ],
  },
  {
    title: "Company",
    links: [
      { label: "Process", href: "#process" },
      { label: "Workflows", href: "#operations" },
      { label: "Careers", href: "#contact" },
      { label: "Contact", href: "#contact" },
    ],
  },
  {
    title: "Technology",
    links: [
      { label: "Laravel", href: "#services" },
      { label: "Vue", href: "#services" },
      { label: "TypeScript", href: "#services" },
      { label: "Inertia", href: "#services" },
      { label: "MySQL", href: "#services" },
      { label: "Redis", href: "#services" },
      { label: "Tailwind CSS", href: "#services" },
    ],
  },
];
