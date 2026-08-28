<script setup lang="ts">
import { update } from "@/actions/App/Http/Controllers/Settings/ProfileController";
import { FormInput } from "@/components/Ui/FormInput";
import SettingsLayout from "@/layouts/SettingsLayout.vue";
import { send } from "@/routes/verification";
import type { FormSubmitEvent } from "@nuxt/ui";
import * as z from "zod";

defineProps<{
  mustVerifyEmail: boolean;
  status?: string;
}>();

const fileRef = ref<HTMLInputElement>();

const profileSchema = z.object({
  name: z.string().min(2, "Too short"),
  email: z.email("Invalid email"),
  avatar: z.string().optional(),
});

type ProfileSchema = z.output<typeof profileSchema>;
type ProfileFormError = { name?: string; message: string };
const auth = useAuth();

const profile = reactive<Partial<ProfileSchema>>({
  name: auth.value.user.name,
  email: auth.value.user.email,
  avatar: undefined,
});

const form = useForm<Partial<ProfileSchema>>({
  name: "",
  email: "",
  avatar: undefined,
});

function fieldError(errors: ProfileFormError[], field: string): string | undefined {
  return errors.find((error) => error.name === field)?.message;
}

const toast = useToast();
async function onSubmit(event: FormSubmitEvent<ProfileSchema>) {
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

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement;

  if (!input.files?.length) {
    return;
  }

  profile.avatar = URL.createObjectURL(input.files[0]);
}

function onFileClick() {
  fileRef.value?.click();
}
</script>

<template>
  <SettingsLayout document-title="Profile settings">
    <UForm id="settings" #default="{ errors }" :schema="profileSchema" :state="profile" @submit="onSubmit">
      <AppPageCard :style="'naked'" class="mb-4" description="This information will be displayed publicly." orientation="horizontal" title="Profile">
        <UButton form="settings" label="Save changes" color="neutral" type="submit" class="w-fit lg:ms-auto" />
      </AppPageCard>

      <AppPageCard>
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
            <UButton label="Choose" color="neutral" @click="onFileClick" />
            <input ref="fileRef" type="file" class="hidden" accept=".jpg, .jpeg, .png, .gif" @change="onFileChange" />
          </div>
        </UFormField>
      </AppPageCard>
    </UForm>
  </SettingsLayout>
</template>
