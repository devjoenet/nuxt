import { defineAsyncComponent, type Component } from "vue";

const complexExamples: Record<string, Component> = {
  "applications-analytics": defineAsyncComponent(() => import("../Examples/Applications/Analytics.vue")),
  "applications-calendar": defineAsyncComponent(() => import("../Examples/Applications/Calendar.vue")),
  "applications-datatables": defineAsyncComponent(() => import("../Examples/Applications/DataTables.vue")),
  "applications-kanban": defineAsyncComponent(() => import("../Examples/Applications/Kanban.vue")),
  "applications-wizard": defineAsyncComponent(() => import("../Examples/Applications/Wizard.vue")),
  "dashboards-automotive": defineAsyncComponent(() => import("../Examples/Dashboards/Automotive.vue")),
  "dashboards-crm": defineAsyncComponent(() => import("../Examples/Dashboards/Crm.vue")),
  "dashboards-default": defineAsyncComponent(() => import("../Examples/Dashboards/Default.vue")),
  "dashboards-landing": defineAsyncComponent(() => import("../Examples/Dashboards/Landing.vue")),
  "dashboards-smart-home": defineAsyncComponent(() => import("../Examples/Dashboards/SmartHome.vue")),
  "ecommerce-orders-details": defineAsyncComponent(() => import("../Examples/Ecommerce/Orders/Details.vue")),
  "ecommerce-orders-list": defineAsyncComponent(() => import("../Examples/Ecommerce/Orders/List.vue")),
  "ecommerce-overview": defineAsyncComponent(() => import("../Examples/Ecommerce/Overview.vue")),
  "ecommerce-products-edit-product": defineAsyncComponent(() => import("../Examples/Ecommerce/Products/EditProduct.vue")),
  "ecommerce-products-new-product": defineAsyncComponent(() => import("../Examples/Ecommerce/Products/NewProduct.vue")),
  "ecommerce-products-product-page": defineAsyncComponent(() => import("../Examples/Ecommerce/Products/ProductPage.vue")),
  "ecommerce-products-products-list": defineAsyncComponent(() => import("../Examples/Ecommerce/Products/ProductsList.vue")),
  "ecommerce-referral": defineAsyncComponent(() => import("../Examples/Ecommerce/Referral.vue")),
};

export function resolveComplexExample(id: string): Component | null {
  return complexExamples[id] ?? null;
}
