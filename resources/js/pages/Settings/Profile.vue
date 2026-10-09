<script setup lang="ts">
import { update } from "@/actions/App/Http/Controllers/Settings/ProfileController";
import { FormInput } from "@/components/Ui/FormInput";
import { send } from "@/routes/verification";
import { setLayoutProps, useForm } from "@inertiajs/vue3";
import type { FormSubmitEvent } from "@nuxt/ui";
import { profileSchema, type ProfileSchema, useProfileEditor } from "./Components/useProfileEditor";

defineProps<{
  mustVerifyEmail: boolean;
  status?: string;
}>();

setLayoutProps({ documentTitle: "Profile settings" });
const { auth, fieldError, fileRef, onFileChange, onFileClick, profile } = useProfileEditor();

const form = useForm<Partial<ProfileSchema>>({
  name: "",
  email: "",
  avatar: undefined,
});

const toast = useToast();
function onSubmit(event: FormSubmitEvent<ProfileSchema>) {
  form.name = event.data.name;
  form.email = event.data.email;
  form.avatar = event.data.avatar;
  form.submit(update(), {
    onSuccess: () => {
      toast.add({
        title: "Success",
        description: "Your settings have been updated.",
        icon: "i-ph-check",
        color: "success",
      });
    },
  });
}
</script>

<template>
  <div class="contents">
    <UForm id="settings" #default="{ errors }" :schema="profileSchema" :state="profile" @submit="onSubmit">
      <AppPageCard :style="'naked'" class="mb-4" description="This information will be displayed publicly." orientation="horizontal" title="Profile">
        <UButton form="settings" label="Save changes" color="primary" type="submit" class="w-fit lg:ms-auto" />
      </AppPageCard>

      <AppPageCard :style="'outline'" class="dark:bg-elevated dark:shadow-none">
        <FormInput v-model="profile.name" :error="fieldError(errors, 'name')" autocomplete="off" description="Will appear on receipts, invoices, and other communication." label="Name" name="name" required />
        <USeparator />
        <FormInput v-model="profile.email" :error="fieldError(errors, 'email')" autocomplete="off" description="Used to sign in, for email receipts and product updates." label="Email" name="email" required type="email" />
        <div v-if="mustVerifyEmail && !auth.user.email_verified_at" class="text-base text-muted sm:text-sm">
          Your email address is unverified.
          <TextLink :href="send()" as="button" class="font-medium text-primary">Click here to resend the verification email.</TextLink>
          <div v-if="status === 'verification-link-sent'" class="mt-2 font-medium text-success">A new verification link has been sent to your email address.</div>
        </div>
        <USeparator />
        <UFormField name="avatar" label="Avatar" description="JPG, GIF or PNG. 1MB Max." class="flex justify-between gap-4 max-sm:flex-col sm:items-center">
          <div class="flex flex-wrap items-center gap-3">
            <UAvatar :src="profile.avatar" :alt="profile.name" size="lg" />
            <UButton label="Choose" color="neutral" variant="outline" @click="onFileClick" />
            <input ref="fileRef" type="file" class="hidden" accept=".jpg, .jpeg, .png, .gif" @change="onFileChange" />
          </div>
        </UFormField>
      </AppPageCard>
    </UForm>
  </div>
</template>
