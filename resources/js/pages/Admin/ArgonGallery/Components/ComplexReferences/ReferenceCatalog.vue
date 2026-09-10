<script setup lang="ts">
import { show } from "@/routes/admin/argonGallery/examples";
import ReferenceTable from "./ReferenceTable.vue";
import { employees, orders, products } from "./tableFixtures";
defineProps<{ specimen: "employees" | "orders" | "products" }>();
</script>
<template>
  <div class="grid gap-5">
    <div v-if="specimen !== 'employees'" class="flex flex-wrap gap-3">
      <UButton v-if="specimen === 'products'" :to="show('ecommerce-products-new-product').url" label="New demo product" /><UButton
        :to="show(specimen === 'products' ? 'ecommerce-products-edit-product' : 'ecommerce-orders-details').url"
        :label="specimen === 'products' ? 'Edit demo product' : 'View demo order'"
        variant="outline"
      />
    </div>
    <ReferenceTable :title="specimen === 'employees' ? 'Datatable Search' : specimen === 'orders' ? 'Order List' : 'All Products'" :rows="specimen === 'employees' ? employees : specimen === 'orders' ? orders : products" />
  </div>
</template>
