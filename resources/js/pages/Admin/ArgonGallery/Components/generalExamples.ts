import { defineAsyncComponent, type Component } from "vue";

const generalExamples: Record<string, Component> = {
  "general-charts": defineAsyncComponent(() => import("../Examples/General/Charts.vue")),
  "general-components-buttons": defineAsyncComponent(() => import("../Examples/General/Components/Buttons.vue")),
  "general-components-cards": defineAsyncComponent(() => import("../Examples/General/Components/Cards.vue")),
  "general-components-grid": defineAsyncComponent(() => import("../Examples/General/Components/Grid.vue")),
  "general-components-icons": defineAsyncComponent(() => import("../Examples/General/Components/Icons.vue")),
  "general-components-notifications": defineAsyncComponent(() => import("../Examples/General/Components/Notifications.vue")),
  "general-components-typography": defineAsyncComponent(() => import("../Examples/General/Components/Typography.vue")),
  "general-forms-components": defineAsyncComponent(() => import("../Examples/General/Forms/Components.vue")),
  "general-forms-elements": defineAsyncComponent(() => import("../Examples/General/Forms/Elements.vue")),
  "general-forms-validation": defineAsyncComponent(() => import("../Examples/General/Forms/Validation.vue")),
  "general-maps-google": defineAsyncComponent(() => import("../Examples/General/Maps/Google.vue")),
  "general-maps-vector": defineAsyncComponent(() => import("../Examples/General/Maps/Vector.vue")),
  "general-notifications": defineAsyncComponent(() => import("../Examples/General/Notifications.vue")),
  "general-pricing-page": defineAsyncComponent(() => import("../Examples/General/PricingPage.vue")),
  "general-rtl-page": defineAsyncComponent(() => import("../Examples/General/RtlPage.vue")),
  "general-sweet-alerts": defineAsyncComponent(() => import("../Examples/General/SweetAlerts.vue")),
  "general-tables-datatables": defineAsyncComponent(() => import("../Examples/General/Tables/DataTables.vue")),
  "general-tables-sortable": defineAsyncComponent(() => import("../Examples/General/Tables/Sortable.vue")),
  "general-tables-tables": defineAsyncComponent(() => import("../Examples/General/Tables/Tables.vue")),
  "general-vr-vr-default": defineAsyncComponent(() => import("../Examples/General/VirtualReality/VrDefault.vue")),
  "general-vr-vr-info": defineAsyncComponent(() => import("../Examples/General/VirtualReality/VrInfo.vue")),
  "general-widgets": defineAsyncComponent(() => import("../Examples/General/Widgets.vue")),
};

export function resolveGeneralExample(id: string): Component | null {
  return generalExamples[id] ?? null;
}
