<script setup lang="ts">
  import Layout from "@/layouts/Default.vue";
  import SettingsLayout from "@/layouts/SettingsLayout.vue";
  import { Head } from "@inertiajs/vue3";
  import { computed, ref } from "vue";
  import type { Member } from "../../types";

  defineOptions({ layout: Layout });

  const members = ref<Member[]>([]);

  const q = ref("");

  const filteredMembers = computed(() => {
    return (
      members.value?.filter((member) => {
        return member.name.search(new RegExp(q.value, "i")) !== -1 || member.username.search(new RegExp(q.value, "i")) !== -1;
      }) ?? []
    );
  });
</script>

<template>
  <SettingsLayout>
    <Head title="Members settings" />

    <UPageCard title="Members" description="Invite new members by email address." variant="naked" orientation="horizontal" class="mb-4">
      <UButton label="Invite people" color="neutral" class="w-fit lg:ms-auto" />
    </UPageCard>

    <UPageCard variant="subtle" :ui="{ container: 'p-0 sm:p-0 gap-y-0', wrapper: 'items-stretch', header: 'p-4 mb-0 border-b border-default' }">
      <template #header>
        <UInput v-model="q" autofocus class="w-full" icon="i-ph-magnifying-glass" placeholder="Search members" />
      </template>

      <SettingsMembersList :members="filteredMembers" />
    </UPageCard>
  </SettingsLayout>
</template>
