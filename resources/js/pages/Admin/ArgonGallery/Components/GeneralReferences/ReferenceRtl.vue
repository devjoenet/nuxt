<script setup lang="ts">
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Card } from "@/components/Ui/Card";

const sales = [
  { x: 1, y: 48, label: "يناير" },
  { x: 2, y: 72, label: "فبراير" },
  { x: 3, y: 63, label: "مارس" },
  { x: 4, y: 104, label: "أبريل" },
];
const stats = [
  { label: "صحة البطارية", value: "99%" },
  { label: "حجم الموسيقى", value: "15/100" },
  { label: "الدخل", value: "$130,832" },
  { label: "المهام", value: "480" },
];
const orders = ["$2400، تغييرات في التصميم", "طلب جديد رقم 1832412", "مدفوعات الخادم لشهر أبريل", "تمت إضافة بطاقة جديدة"];
</script>

<template>
  <div class="grid gap-5" dir="rtl" lang="ar">
    <UAlert color="info" description="يستخدم هذا المرجع خصائص منطقية واتجاهًا أصليًا بدل نسخ تخطيط Bootstrap من المصدر." title="تكييف واجهة من اليمين إلى اليسار" variant="subtle" />
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card v-for="stat in stats" :key="stat.label" variant="outline"
        ><p class="truncate text-base text-content-subtle sm:text-sm">{{ stat.label }}</p>
        <p class="text-2xl font-semibold tabular-nums text-content-heading">{{ stat.value }}</p></Card
      >
    </div>
    <div class="grid gap-5 lg:grid-cols-[2fr_1fr]">
      <Card variant="outline"
        ><template #header><h3 class="text-balance text-lg font-semibold text-content-heading">نظرة عامة على المبيعات</h3></template><LazyAdvancedChart v-bind="{ ariaLabel: 'نظرة عامة على المبيعات' }" :data="sales" :height="240" /></Card
      ><Card variant="outline"
        ><template #header><h3 class="text-balance text-lg font-semibold text-content-heading">نظرة عامة على الطلبات</h3></template>
        <ol class="grid gap-3" role="list">
          <li v-for="order in orders" :key="order" class="border-b border-content-divider pb-3 text-base text-content-body last:border-0 last:pb-0 sm:text-sm">{{ order }}</li>
        </ol></Card
      >
    </div>
  </div>
</template>
