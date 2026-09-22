<script setup lang="ts">
import { Avatar } from "@/components/Ui/Avatar";
import { Badge } from "@/components/Ui/Badge";
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSwitch } from "@/components/Ui/FormSwitch";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { Progress } from "@/components/Ui/Progress";
import { Stat } from "@/components/Ui/Stat";
import { computed, ref } from "vue";

defineProps<{ specimen: "new-user" | "overview" | "projects" | "reports" | "teams" }>();
const wizardStep = ref(0);
const wizardSteps = ["Identity", "Address", "Socials", "Profile"];
const wizardTitle = computed(() => wizardSteps[wizardStep.value] ?? "Identity");
const name = ref("Jordan Lee");
const email = ref("jordan@example.com");
const bio = ref("Building thoughtful tools with the Southeast Code team.");
const accountEmails = ref(true);
const projectUpdates = ref(true);
const newsletter = ref(false);
const notice = ref(false);
const projects = [
  { title: "Modern", category: "Dashboard", progress: 78, members: ["Alex Morgan", "Jordan Lee"] },
  { title: "Scandinavian", category: "Brand system", progress: 54, members: ["Taylor Reed", "Sam Lee"] },
  { title: "Minimalist", category: "Mobile app", progress: 92, members: ["Alex Morgan", "Sam Lee"] },
];
const members = [
  { name: "Alex Morgan", role: "Product design", status: "online" as const },
  { name: "Jordan Lee", role: "Engineering", status: "busy" as const },
  { name: "Taylor Reed", role: "Operations", status: "offline" as const },
];
const nextStep = (): void => {
  wizardStep.value = Math.min(wizardStep.value + 1, wizardSteps.length - 1);
};
const previousStep = (): void => {
  wizardStep.value = Math.max(wizardStep.value - 1, 0);
};
const acknowledge = (): void => {
  notice.value = true;
};
</script>

<template>
  <div class="grid min-w-0 gap-5">
    <UAlert color="info" description="Team members, messages, reports, and invitations are local fixtures. No production team or notification is touched." title="Inert team reference" variant="subtle" />

    <Card v-if="specimen === 'new-user'" as="section" class="grid max-w-full min-w-0 gap-6" padding="lg" variant="outline">
      <header class="grid gap-4">
        <div>
          <p class="text-sm font-semibold text-primary">New team member</p>
          <h3 class="mt-1 text-2xl font-semibold text-content-heading">{{ wizardTitle }}</h3>
        </div>
        <ol class="grid grid-cols-4 gap-2" aria-label="Invitation steps">
          <li v-for="(step, index) in wizardSteps" :key="step" class="grid gap-2">
            <span :class="index <= wizardStep ? 'bg-primary' : 'bg-content-divider'" class="h-1.5 rounded-full"></span><span class="hidden text-xs text-content-subtle sm:block">{{ step }}</span>
          </li>
        </ol>
      </header>
      <form class="grid min-w-0 gap-4" @submit.prevent="acknowledge">
        <template v-if="wizardStep === 0"
          ><div class="grid gap-4 sm:grid-cols-2">
            <FormInput v-model="name" label="Full name" name="team-user-name" required /><FormInput v-model="email" label="Email" name="team-user-email" required type="email" /><FormInput
              autocomplete="off"
              label="Temporary password"
              name="team-user-password"
              type="password"
            /><FormInput default-value="Product designer" label="Role" name="team-user-role" /></div
        ></template>
        <template v-else-if="wizardStep === 1"
          ><div class="grid gap-4 sm:grid-cols-2">
            <FormInput class="sm:col-span-2" default-value="455 Market Street" label="Address" name="team-user-address" /><FormInput default-value="San Francisco" label="City" name="team-user-city" /><FormInput default-value="CA" label="State" name="team-user-state" /><FormInput
              default-value="94105"
              inputmode="numeric"
              label="ZIP code"
              name="team-user-zip"
            /></div
        ></template>
        <template v-else-if="wizardStep === 2"
          ><div class="grid gap-4 sm:grid-cols-2"><FormInput default-value="@jordan" label="Social handle" name="team-user-social" /><FormInput default-value="https://example.com" label="Portfolio" name="team-user-portfolio" type="url" /></div
        ></template>
        <template v-else><FormInput v-model="email" label="Public email" name="team-user-public-email" type="email" /><FormTextarea v-model="bio" label="Bio" name="team-user-bio" :rows="4" /></template>
        <div class="flex flex-wrap justify-between gap-3">
          <span v-if="wizardStep === 0" aria-hidden="true"></span><Button v-else label="Previous" variant="outline" @click="previousStep" /><Button v-if="wizardStep < wizardSteps.length - 1" label="Next" @click="nextStep" /><Button v-else label="Send preview" type="submit" />
        </div>
      </form>
    </Card>

    <template v-else-if="specimen === 'overview'">
      <Card as="section" class="flex flex-wrap items-center gap-4 bg-linear-to-r from-primary/15 to-info/10" padding="lg" variant="outline"
        ><Avatar name="Alex Morgan" size="xl" status="online" />
        <div>
          <h3 class="text-2xl font-semibold text-content-heading">Alex Morgan</h3>
          <p class="text-content-body">Product designer · Southeast Code</p>
        </div>
        <Button class="sm:ms-auto" label="Edit profile" variant="outline" @click="acknowledge"
      /></Card>
      <div class="grid gap-5 lg:grid-cols-3">
        <Card as="section" class="grid content-start gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Platform settings</h3>
          <FormSwitch v-model="accountEmails" label="Account activity" name="team-account-emails" /><FormSwitch v-model="projectUpdates" label="Project updates" name="team-project-updates" /><FormSwitch v-model="newsletter" label="Monthly newsletter" name="team-newsletter" /></Card
        ><Card as="section" class="grid content-start gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Profile information</h3>
          <p class="text-content-body">Designing clear workflows and durable systems for growing product teams.</p>
          <dl class="grid gap-2 text-sm">
            <div>
              <dt class="text-content-subtle">Email</dt>
              <dd class="text-content-heading">alex@example.com</dd>
            </div>
            <div>
              <dt class="text-content-subtle">Location</dt>
              <dd class="text-content-heading">New York, USA</dd>
            </div>
          </dl></Card
        ><Card as="section" class="grid content-start gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Conversations</h3>
          <div v-for="member in members" :key="member.name" class="flex items-center gap-3">
            <Avatar :name="member.name" :status="member.status" size="sm" />
            <div class="min-w-0">
              <p class="truncate font-medium text-content-heading">{{ member.name }}</p>
              <p class="truncate text-sm text-content-subtle">Ready for the next review.</p>
            </div>
          </div></Card
        >
      </div>
    </template>

    <template v-else-if="specimen === 'projects'">
      <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-primary">Team portfolio</p>
          <h3 class="text-2xl font-semibold text-content-heading">Active projects</h3>
        </div>
        <Button label="New project" @click="acknowledge" />
      </header>
      <div class="grid gap-4 md:grid-cols-3">
        <Card v-for="project in projects" :key="project.title" as="article" class="grid content-between gap-6" interactive variant="outline"
          ><div class="grid gap-3">
            <div class="flex items-center justify-between gap-3">
              <Badge :label="project.category" variant="soft" /><span class="text-sm text-content-subtle">{{ project.progress }}%</span>
            </div>
            <h4 class="text-xl font-semibold text-content-heading">{{ project.title }}</h4>
            <p class="text-content-body">A reusable, responsive project summary with local fixture data.</p>
            <Progress :value="project.progress" />
          </div>
          <div class="flex items-center justify-between gap-3">
            <div class="flex -space-x-2"><Avatar v-for="member in project.members" :key="member" :name="member" size="sm" class="ring-2 ring-surface-elevated" /></div>
            <Button label="View" size="sm" variant="ghost" @click="acknowledge" /></div
        ></Card>
      </div>
    </template>

    <template v-else-if="specimen === 'reports'">
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Stat label="Members" value="24" delta="+3" trend="up" icon="i-ph-users" /><Stat label="Projects" value="12" description="8 active" icon="i-ph-folder-open" /><Stat label="Hours" value="1,280" delta="+9%" trend="up" icon="i-ph-clock" /><Stat
          label="Satisfaction"
          value="96%"
          icon="i-ph-smiley"
          tone="success"
        />
      </div>
      <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
        <Card as="section" class="grid content-start gap-4 bg-linear-to-br from-primary to-info text-white" padding="lg"
          ><p class="text-sm font-semibold tracking-[0.18em] uppercase">Quarterly score</p>
          <p class="text-5xl font-semibold">9.4</p>
          <p class="text-white/80">Based on delivery, collaboration, and client review fixtures.</p></Card
        ><Card as="section" class="grid gap-4" variant="outline"
          ><h3 class="text-lg font-semibold text-content-heading">Member reviews</h3>
          <div v-for="(member, index) in members" :key="member.name" class="grid grid-cols-[auto_1fr_auto] items-center gap-3">
            <Avatar :name="member.name" size="sm" />
            <div>
              <p class="font-medium text-content-heading">{{ member.name }}</p>
              <Progress :value="92 - index * 8" size="sm" />
            </div>
            <span class="text-sm font-semibold text-content-heading">{{ 92 - index * 8 }}%</span>
          </div></Card
        >
      </div>
    </template>

    <template v-else>
      <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
        <Card as="section" class="grid content-start gap-4" variant="outline"
          ><header class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-content-heading">Design team</h3>
            <Badge label="8 members" />
          </header>
          <div v-for="member in members" :key="member.name" class="flex items-center gap-3">
            <Avatar :name="member.name" :status="member.status" />
            <div>
              <p class="font-medium text-content-heading">{{ member.name }}</p>
              <p class="text-sm text-content-subtle">{{ member.role }}</p>
            </div>
          </div>
          <Button label="Invite member" variant="outline" @click="acknowledge" /></Card
        ><Card as="section" class="grid content-start gap-4" variant="soft"
          ><div class="flex items-center gap-3">
            <Avatar name="Alex Morgan" status="online" />
            <div>
              <p class="font-medium text-content-heading">Alex Morgan</p>
              <p class="text-sm text-content-subtle">Shared an update with Design team</p>
            </div>
          </div>
          <p class="text-pretty text-content-body">The identity and project references are ready for a focused keyboard and responsive review.</p>
          <FormTextarea label="Write a comment" name="team-comment" placeholder="Keep it local to this preview" :rows="3" /><Button class="justify-self-end" label="Post preview" @click="acknowledge"
        /></Card>
      </div>
    </template>

    <UAlert v-if="notice" color="success" description="The team action stayed inside this preview; no invitation, message, or update was sent." title="Inert interaction confirmed" variant="subtle" />
  </div>
</template>
