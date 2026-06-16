<script setup lang="ts">
  import { Bell, CircleAlert, Download, Search } from "lucide-vue-next";

  const navItems = ["Overview", "Shipments", "Routes", "Inventory", "Orders", "Approvals", "Field data", "Reports", "Integrations", "Users & roles", "Settings"];

  const stats = [
    { label: "Shipments in transit", value: "128", delta: "+21% vs last 7 days" },
    { label: "On-time delivery", value: "94.2%", delta: "+4.1% vs last 7 days" },
    { label: "Inventory value", value: "$2.48M", delta: "+3.4% vs last 7 days" },
    { label: "Exceptions", value: "14", delta: "+6 vs yesterday" },
  ];

  const routes = [
    ["SAV -> ATL", "In transit", "ETA 10:45 AM"],
    ["SAV -> MIA", "In transit", "ETA 12:15 PM"],
    ["CHS -> SAV", "Delayed", "ETA 02:10 PM"],
    ["SAV -> BHM", "On time", "ETA 04:10 PM"],
  ];

  const approvals = [
    ["PO-14853", "Vendor invoice", "$18,450", "High"],
    ["PO-14867", "Purchase order", "$7,250", "Med"],
    ["PO-14881", "Rate adjustment", "$2,950", "Med"],
  ];
</script>

<template>
  <aside
    class="relative rounded-xl border border-brand-primary-300/20 bg-muted-950/72 p-3 shadow-2xl shadow-muted-950/60 ring-1 ring-neutral-100/10 backdrop-blur-md"
    aria-label="Operations command dashboard preview"
  >
    <div class="grid gap-3 lg:grid-cols-[9rem_1fr]">
      <div class="rounded-lg border border-neutral-100/10 bg-muted-900/80 p-3">
        <div class="flex items-center gap-3 border-b border-neutral-100/10 pb-3">
          <div class="flex size-9 items-center justify-center rounded-md border border-brand-primary-300/30 text-sm font-bold text-neutral-50">SC</div>
          <p class="text-sm font-semibold text-neutral-100">Ops</p>
        </div>

        <nav class="mt-3 space-y-1" aria-label="Dashboard preview navigation">
          <span
            v-for="item in navItems"
            :key="item"
            class="flex items-center rounded-md px-2 py-1.5 text-xs text-neutral-400"
            :class="item === 'Overview' ? 'bg-brand-primary-500/10 text-brand-primary-200 ring-1 ring-brand-primary-400/25' : ''"
          >
            {{ item }}
          </span>
        </nav>
      </div>

      <div class="space-y-3">
        <div class="flex flex-col gap-3 rounded-lg border border-neutral-100/10 bg-muted-900/80 p-3 sm:flex-row sm:items-center sm:justify-between">
          <p class="text-sm font-semibold text-neutral-100">Operations command</p>
          <div class="flex items-center gap-3 text-neutral-500">
            <div class="flex items-center gap-2 rounded-md border border-neutral-100/10 px-3 py-1.5 text-xs">
              <Search class="size-3.5" aria-hidden="true" />
              <span>Search...</span>
            </div>
            <Bell class="size-4" aria-hidden="true" />
            <span class="flex size-7 items-center justify-center rounded-full bg-neutral-100/10 text-[0.65rem] text-neutral-300">AD</span>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <article v-for="stat in stats" :key="stat.label" class="rounded-lg border border-neutral-100/10 bg-muted-900/80 p-4">
            <p class="text-[0.7rem] text-neutral-400">{{ stat.label }}</p>
            <div class="mt-2 flex items-end justify-between gap-3">
              <p class="text-2xl font-semibold text-neutral-50">{{ stat.value }}</p>
              <Download class="size-5 text-brand-primary-300" aria-hidden="true" />
            </div>
            <p class="mt-2 text-[0.7rem] text-brand-primary-300">{{ stat.delta }}</p>
          </article>
        </div>

        <div class="grid gap-3 xl:grid-cols-2">
          <article class="rounded-lg border border-neutral-100/10 bg-muted-900/80 p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-neutral-100">Active routes</h2>
              <span class="text-xs text-brand-primary-300">View all</span>
            </div>
            <dl class="mt-4 space-y-3">
              <div v-for="route in routes" :key="route[0]" class="grid grid-cols-[1fr_auto_auto] gap-3 text-xs">
                <dt class="font-semibold text-neutral-200">{{ route[0] }}</dt>
                <dd :class="route[1] === 'Delayed' ? 'text-brand-warning-300' : 'text-brand-primary-300'">{{ route[1] }}</dd>
                <dd class="text-neutral-500">{{ route[2] }}</dd>
              </div>
            </dl>
          </article>

          <article class="rounded-lg border border-neutral-100/10 bg-muted-900/80 p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-neutral-100">Approvals queue</h2>
              <span class="text-xs text-brand-primary-300">7 pending</span>
            </div>
            <dl class="mt-4 space-y-3">
              <div v-for="approval in approvals" :key="approval[0]" class="grid grid-cols-[auto_1fr_auto_auto] gap-3 text-xs">
                <dt class="font-semibold text-neutral-200">{{ approval[0] }}</dt>
                <dd class="text-neutral-400">{{ approval[1] }}</dd>
                <dd class="text-neutral-300">{{ approval[2] }}</dd>
                <dd :class="approval[3] === 'High' ? 'text-brand-warning-300' : 'text-brand-accent-300'">{{ approval[3] }}</dd>
              </div>
            </dl>
          </article>
        </div>

        <div class="rounded-lg border border-neutral-100/10 bg-muted-900/80 p-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-neutral-100">System alerts</h2>
            <span class="text-xs text-brand-primary-300">View all alerts -></span>
          </div>
          <div class="mt-3 flex flex-wrap gap-4 text-xs">
            <p class="flex items-center gap-2 text-brand-warning-300"><CircleAlert class="size-4" aria-hidden="true" /> Low stock: BOLT-12</p>
            <p class="text-neutral-400">3 shipments delayed</p>
            <p class="text-brand-accent-300">Invoice sync failed</p>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>
