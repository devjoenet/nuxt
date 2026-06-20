<script setup lang="ts">
  const navItems = ["Overview", "Shipments", "Routes", "Inventory", "Orders", "Approvals", "Field data", "Reports", "Integrations", "Users & roles", "Settings"];

  const stats = [
    { label: "Shipments in transit", value: "128", delta: "+21% vs last 7 days" },
    { label: "On-time delivery", value: "94.2%", delta: "+4.1% vs last 7 days" },
    { label: "Inventory value", value: "$2.48M", delta: "+3.4% vs last 7 days" },
    { label: "Exceptions", value: "14", delta: "+6 vs yesterday" },
  ];

  const routes = [
    ["SAV to ATL", "In transit", "ETA 10:45 AM"],
    ["SAV to MIA", "In transit", "ETA 12:15 PM"],
    ["CHS to SAV", "Delayed", "ETA 02:10 PM"],
    ["SAV to BHM", "On time", "ETA 04:10 PM"],
  ];

  const approvals = [
    ["PO-14853", "Vendor invoice", "$18,450", "High"],
    ["PO-14867", "Purchase order", "$7,250", "Med"],
    ["PO-14881", "Rate adjustment", "$2,950", "Med"],
  ];
</script>

<template>
  <aside
    class="relative rounded-xl border border-secondary-500/25 bg-muted-50/82 p-3 shadow-2xl shadow-muted-950/16 ring-1 ring-muted-200/80 backdrop-blur-md dark:bg-muted-950/72 dark:shadow-muted-950/60 dark:ring-neutral-100/10"
    aria-label="Operations command dashboard preview"
  >
    <div class="grid gap-3 lg:grid-cols-[9rem_1fr]">
      <div class="rounded-lg border border-muted-200 bg-muted-100/80 p-3 dark:border-neutral-100/10 dark:bg-muted-900/80">
        <div class="flex items-center gap-3 border-b border-muted-200 pb-3 dark:border-neutral-100/10">
          <div class="flex size-9 items-center justify-center rounded-md border border-secondary-500/35 text-sm font-bold text-muted-950 dark:text-neutral-50">SC</div>
          <p class="text-sm font-semibold text-muted-900 dark:text-neutral-100">Ops</p>
        </div>

        <nav class="mt-3 space-y-1" aria-label="Dashboard preview navigation">
          <span
            v-for="item in navItems"
            :key="item"
            :class="item === 'Overview' ? 'bg-secondary-500/15 text-secondary-800 ring-1 ring-secondary-500/30 dark:text-secondary-100' : ''"
            class="flex items-center rounded-md px-2 py-1.5 text-xs text-muted-600 dark:text-neutral-400"
          >
            {{ item }}
          </span>
        </nav>
      </div>

      <div class="space-y-3">
        <div
          class="flex flex-col gap-3 rounded-lg border border-muted-200 bg-muted-100/80 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-100/10 dark:bg-muted-900/80"
        >
          <p class="text-sm font-semibold text-muted-900 dark:text-neutral-100">Operations command</p>
          <div class="flex items-center gap-3 text-muted-500 dark:text-neutral-500">
            <div class="flex items-center gap-2 rounded-md border border-muted-200 px-3 py-1.5 text-xs dark:border-neutral-100/10">
              <UIcon aria-hidden="true" class="size-3.5" name="i-ph-magnifying-glass" />
              <span>Search...</span>
            </div>
            <UIcon aria-hidden="true" class="size-4" name="i-ph-bell" />
            <span class="flex size-7 items-center justify-center rounded-full bg-muted-200 text-[0.65rem] text-muted-700 dark:bg-neutral-100/10 dark:text-neutral-300">AD</span>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <article v-for="stat in stats" :key="stat.label" class="rounded-lg border border-muted-200 bg-muted-100/80 p-4 dark:border-neutral-100/10 dark:bg-muted-900/80">
            <p class="text-[0.7rem] text-muted-600 dark:text-neutral-400">{{ stat.label }}</p>
            <div class="mt-2 flex items-end justify-between gap-3">
              <p class="text-2xl font-semibold text-muted-950 dark:text-neutral-50">{{ stat.value }}</p>
              <UIcon aria-hidden="true" class="size-5 text-secondary-400" name="i-ph-download-simple" />
            </div>
            <p class="mt-2 text-[0.7rem] text-secondary-400">{{ stat.delta }}</p>
          </article>
        </div>

        <div class="grid gap-3 xl:grid-cols-2">
          <article class="rounded-lg border border-muted-200 bg-muted-100/80 p-4 dark:border-neutral-100/10 dark:bg-muted-900/80">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-muted-900 dark:text-neutral-100">Active routes</h2>
              <span class="text-xs text-primary-400">View all</span>
            </div>
            <dl class="mt-4 space-y-3">
              <div v-for="route in routes" :key="route[0]" class="grid grid-cols-[1fr_auto_auto] gap-3 text-xs">
                <dt class="font-semibold text-muted-800 dark:text-neutral-200">{{ route[0] }}</dt>
                <dd :class="route[1] === 'Delayed' ? 'text-warning-400' : 'text-primary-400'">{{ route[1] }}</dd>
                <dd class="text-muted-500 dark:text-neutral-500">{{ route[2] }}</dd>
              </div>
            </dl>
          </article>

          <article class="rounded-lg border border-muted-200 bg-muted-100/80 p-4 dark:border-neutral-100/10 dark:bg-muted-900/80">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-muted-900 dark:text-neutral-100">Approvals queue</h2>
              <span class="text-xs text-accent-400">7 pending</span>
            </div>
            <dl class="mt-4 space-y-3">
              <div v-for="approval in approvals" :key="approval[0]" class="grid grid-cols-[auto_1fr_auto_auto] gap-3 text-xs">
                <dt class="font-semibold text-muted-800 dark:text-neutral-200">{{ approval[0] }}</dt>
                <dd class="text-muted-600 dark:text-neutral-400">{{ approval[1] }}</dd>
                <dd class="text-muted-700 dark:text-neutral-300">{{ approval[2] }}</dd>
                <dd :class="approval[3] === 'High' ? 'text-warning-400' : 'text-accent-400'">{{ approval[3] }}</dd>
              </div>
            </dl>
          </article>
        </div>

        <div class="rounded-lg border border-muted-200 bg-muted-100/80 p-4 dark:border-neutral-100/10 dark:bg-muted-900/80">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-muted-900 dark:text-neutral-100">System alerts</h2>
            <span class="inline-flex items-center text-xs text-secondary-400">
              View all alerts
              <UIcon aria-hidden="true" class="ml-1 size-3.5" name="i-ph-arrow-circle-right" />
            </span>
          </div>
          <div class="mt-3 flex flex-wrap gap-4 text-xs">
            <p class="flex items-center gap-2 text-warning-400"><UIcon aria-hidden="true" class="size-4" name="i-ph-warning-circle" /> Low stock: BOLT-12</p>
            <p class="text-muted-600 dark:text-neutral-400">3 shipments delayed</p>
            <p class="text-accent-400">Invoice sync failed</p>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>
