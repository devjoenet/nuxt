import { reactive, ref } from "vue";
import * as z from "zod";
import { useAuth } from "@/composables/useAuth";

export const profileSchema = z.object({
  name: z.string().min(2, "Too short"),
  email: z.email("Invalid email"),
  avatar: z.string().optional(),
});

export type ProfileSchema = z.output<typeof profileSchema>;
export type ProfileFormError = { name?: string; message: string };

export function useProfileEditor() {
  const auth = useAuth();
  const fileRef = ref<HTMLInputElement>();
  const profile = reactive<Partial<ProfileSchema>>({
    name: auth.value.user.name,
    email: auth.value.user.email,
    avatar: undefined,
  });
  const fieldError = (errors: ProfileFormError[], field: string): string | undefined => errors.find((error) => error.name === field)?.message;
  const onFileChange = (event: Event): void => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (file) {
      profile.avatar = URL.createObjectURL(file);
    }
  };
  const onFileClick = (): void => fileRef.value?.click();

  return { auth, fieldError, fileRef, onFileChange, onFileClick, profile };
}
