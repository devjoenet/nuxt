<script setup lang="ts">
import { Avatar } from "@/components/Ui/Avatar";
import { Badge } from "@/components/Ui/Badge";
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { Stat } from "@/components/Ui/Stat";
import { ref } from "vue";

defineProps<{ specimen: "billing" | "invoice" | "security" | "settings" }>();
const firstName = ref("Alex");
const lastName = ref("Morgan");
const email = ref("alex@example.com");
const question = ref<string | number | null>("first-pet");
const securityAlerts = ref(true);
const productUpdates = ref(false);
const notice = ref(false);
const invoices = [
  { date: "March 1, 2026", id: "#SEC-3106", amount: "$180" },
  { date: "February 1, 2026", id: "#SEC-2841", amount: "$180" },
  { date: "January 1, 2026", id: "#SEC-2512", amount: "$160" },
];
const transactions = [
  { name: "Cloud hosting", date: "Today", amount: "−$82", tone: "error" as const },
  { name: "Client retainer", date: "Yesterday", amount: "+$2,400", tone: "success" as const },
  { name: "Design assets", date: "Aug 30", amount: "−$49", tone: "warning" as const },
];
const acknowledge = (): void => {
  notice.value = true;
};
</script>

<template>
  <div class="grid gap-5">
    <UAlert color="info" description="Every control uses local gallery state. Payment, security, session, and profile actions cannot reach application mutations." title="Inert account reference" variant="subtle" />

    <template v-if="specimen === 'billing'">
      <div class="grid gap-4 md:grid-cols-3">
        <Card class="grid content-between gap-8 bg-linear-to-br from-primary to-info text-white" padding="lg">
          <div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold">Southeast Code</span><Badge label="Primary" tone="info" /></div>
          <p class="font-mono text-xl tracking-[0.14em]">•••• •••• •••• 7852</p>
          <div class="flex justify-between gap-4 text-sm"><span>Alex Morgan</span><span>11/28</span></div>
        </Card>
        <Stat description="monthly deposit" icon="i-ph-wallet" label="Salary" tone="success" value="+$2,000" />
        <Stat description="available balance" icon="i-ph-paypal-logo" label="Payment account" value="$455" />
      </div>
      <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
        <Card as="section" class="grid gap-4" variant="outline">
          <header class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-content-heading">Payment methods</h3>
            <Button label="Add card" size="sm" variant="outline" @click="acknowledge" />
          </header>
          <div class="grid gap-3 sm:grid-cols-2">
            <div v-for="ending in ['7852', '5248']" :key="ending" class="flex items-center justify-between gap-3 rounded-xl border border-content-border p-4">
              <span class="font-mono text-sm">•••• {{ ending }}</span
              ><Badge :label="ending === '7852' ? 'Visa' : 'Mastercard'" variant="soft" />
            </div>
          </div>
          <h3 class="pt-2 text-lg font-semibold text-content-heading">Billing information</h3>
          <div class="grid gap-3 sm:grid-cols-2">
            <div v-for="person in ['Alex Morgan', 'Taylor Reed']" :key="person" class="rounded-xl bg-surface-inset p-4">
              <p class="font-semibold text-content-heading">{{ person }}</p>
              <p class="mt-1 text-sm text-content-body">455 Market Street · San Francisco</p>
            </div>
          </div>
        </Card>
        <Card as="section" class="grid content-start gap-4" variant="outline">
          <h3 class="text-lg font-semibold text-content-heading">Transactions</h3>
          <ul class="grid gap-3" role="list">
            <li v-for="transaction in transactions" :key="transaction.name" class="flex items-center justify-between gap-4 border-b border-content-divider pb-3">
              <div>
                <p class="font-medium text-content-heading">{{ transaction.name }}</p>
                <p class="text-sm text-content-subtle">{{ transaction.date }}</p>
              </div>
              <Badge :label="transaction.amount" :tone="transaction.tone" variant="soft" />
            </li>
          </ul>
        </Card>
      </div>
    </template>

    <Card v-else-if="specimen === 'invoice'" as="section" class="grid gap-6" padding="lg" variant="outline">
      <header class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-primary">INVOICE #SEC-3106</p>
          <h3 class="mt-1 text-2xl font-semibold text-content-heading">Product design sprint</h3>
        </div>
        <Badge label="Paid" tone="success" />
      </header>
      <div class="grid gap-4 rounded-xl bg-surface-inset p-4 sm:grid-cols-2">
        <div>
          <p class="text-sm text-content-subtle">Billed to</p>
          <p class="font-semibold text-content-heading">Southeast Code Studio</p>
          <p class="text-sm text-content-body">455 Market Street, San Francisco</p>
        </div>
        <div class="sm:text-end">
          <p class="text-sm text-content-subtle">Issued</p>
          <p class="font-semibold text-content-heading">September 3, 2026</p>
          <p class="text-sm text-content-body">Due on receipt</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full min-w-lg text-left">
          <thead class="border-b border-content-divider text-sm text-content-subtle">
            <tr>
              <th class="py-3 font-medium">Description</th>
              <th class="py-3 font-medium">Hours</th>
              <th class="py-3 font-medium">Rate</th>
              <th class="py-3 text-end font-medium">Amount</th>
            </tr>
          </thead>
          <tbody class="text-content-body">
            <tr
              v-for="item in [
                { name: 'Interface direction', hours: 12, rate: '$140', amount: '$1,680' },
                { name: 'Prototype review', hours: 6, rate: '$140', amount: '$840' },
              ]"
              :key="item.name"
              class="border-b border-content-divider"
            >
              <td class="py-4 font-medium text-content-heading">{{ item.name }}</td>
              <td>{{ item.hours }}</td>
              <td>{{ item.rate }}</td>
              <td class="text-end">{{ item.amount }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="grid justify-end gap-2 text-end">
        <p class="text-sm text-content-body">Tax <span class="ms-10">$202</span></p>
        <p class="text-xl font-semibold text-content-heading">Total $2,722</p>
      </div>
      <Button class="justify-self-end" label="Preview PDF" variant="outline" @click="acknowledge" />
    </Card>

    <template v-else-if="specimen === 'security'">
      <div class="grid gap-5 lg:grid-cols-2">
        <Card as="section" class="grid gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Security question</h3>
          <FormSelect
            v-model="question"
            :items="[
              { label: 'What was your first pet?', value: 'first-pet' },
              { label: 'Where did you grow up?', value: 'hometown' },
            ]"
            label="Question"
            name="account-security-question" /><FormInput default-value="Milo" label="Your answer" name="account-security-answer" />
          <div class="flex justify-end gap-3"><Button label="Cancel" tone="neutral" variant="outline" /><Button label="Save changes" @click="acknowledge" /></div
        ></Card>
        <Card as="section" class="grid gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Change password</h3>
          <FormInput autocomplete="off" label="Current password" name="account-current-password" type="password" /><FormInput autocomplete="off" error="Use at least 12 characters in production." label="New password" name="account-new-password" type="password" /><FormInput
            autocomplete="off"
            label="Confirm password"
            name="account-confirm-password"
            type="password" /><Button block label="Update password" @click="acknowledge"
        /></Card>
      </div>
      <Card as="section" class="grid gap-4" variant="soft"
        ><h3 class="text-lg font-semibold text-content-heading">Security settings</h3>
        <FormSwitch v-model="securityAlerts" label="Email unusual sign-in alerts" name="account-security-alerts" /><FormSwitch v-model="productUpdates" label="Allow trusted devices" name="account-trusted-devices"
      /></Card>
    </template>

    <template v-else>
      <Card as="section" class="grid gap-5" variant="outline"
        ><div class="flex flex-wrap items-center gap-4">
          <Avatar name="Alex Morgan" size="xl" status="online" />
          <div>
            <h3 class="text-xl font-semibold text-content-heading">Alex Morgan</h3>
            <p class="text-content-body">Product designer · San Francisco</p>
          </div>
          <Button class="sm:ms-auto" label="Change avatar" variant="outline" @click="acknowledge" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormInput v-model="firstName" label="First name" name="account-first-name" /><FormInput v-model="lastName" label="Last name" name="account-last-name" /><FormInput v-model="email" label="Email" name="account-email" type="email" /><FormInput
            default-value="San Francisco, CA"
            label="Location"
            name="account-location"
          />
        </div>
        <Button class="justify-self-end" label="Save profile" @click="acknowledge"
      /></Card>
      <div class="grid gap-5 lg:grid-cols-2">
        <Card as="section" class="grid gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Two-factor authentication</h3>
          <p class="text-content-body">Add another layer of security to the account.</p>
          <div class="flex items-center justify-between gap-4"><Badge label="Not configured" tone="warning" /><Button label="Set up" size="sm" variant="outline" @click="acknowledge" /></div></Card
        ><Card as="section" class="grid gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Active sessions</h3>
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="font-medium text-content-heading">Chrome on macOS</p>
              <p class="text-sm text-content-subtle">Current session · New York</p>
            </div>
            <Badge label="Current" tone="success" />
          </div>
          <Button label="Sign out other sessions" tone="neutral" variant="outline" @click="acknowledge"
        /></Card>
      </div>
      <Card as="section" class="flex flex-wrap items-center justify-between gap-4" tone="error" variant="soft"
        ><div>
          <h3 class="text-lg font-semibold text-content-heading">Delete account</h3>
          <p class="text-content-body">This gallery button records intent locally and cannot delete anything.</p>
        </div>
        <Button label="Delete account" tone="error" @click="acknowledge"
      /></Card>
    </template>

    <UAlert v-if="notice" color="success" description="The control updated only this browser preview. No request was sent." title="Inert interaction confirmed" variant="subtle" />
    <Card v-if="specimen === 'billing'" as="section" class="grid gap-3" variant="outline"
      ><header class="flex items-center justify-between gap-4">
        <h3 class="text-lg font-semibold text-content-heading">Invoices</h3>
        <Button label="View all" size="sm" variant="ghost" @click="acknowledge" />
      </header>
      <ul class="grid gap-2" role="list">
        <li v-for="invoice in invoices" :key="invoice.id" class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-surface-inset p-3">
          <div>
            <p class="font-medium text-content-heading">{{ invoice.date }}</p>
            <p class="text-sm text-content-subtle">{{ invoice.id }}</p>
          </div>
          <span class="font-semibold text-content-heading">{{ invoice.amount }}</span>
        </li>
      </ul></Card
    >
  </div>
</template>
