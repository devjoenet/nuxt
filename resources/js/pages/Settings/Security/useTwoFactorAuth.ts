import { useHttp } from "@inertiajs/vue3";
import type { ComputedRef, InjectionKey, Ref } from "vue";
import { computed, inject, provide, ref } from "vue";
import { qrCode, recoveryCodes, secretKey } from "@/routes/two-factor";

interface QrCodeResponse {
  svg: string;
  url: string;
}

interface SecretKeyResponse {
  secretKey: string;
}

interface TwoFactorAuthState {
  qrCodeSvg: Ref<string | null>;
  manualSetupKey: Ref<string | null>;
  recoveryCodesList: Ref<string[]>;
  errors: Ref<string[]>;
  hasSetupData: ComputedRef<boolean>;
  setupProcessing: ComputedRef<boolean>;
  recoveryCodesProcessing: ComputedRef<boolean>;
  clearSetupData: () => void;
  clearErrors: () => void;
  fetchSetupData: () => Promise<void>;
  fetchRecoveryCodes: () => Promise<void>;
}

const twoFactorAuthKey: InjectionKey<TwoFactorAuthState> = Symbol("two-factor-auth");

function createTwoFactorAuthState(): TwoFactorAuthState {
  const qrCodeRequest = useHttp<Record<string, never>, QrCodeResponse>({});
  const setupKeyRequest = useHttp<Record<string, never>, SecretKeyResponse>({});
  const recoveryCodesRequest = useHttp<Record<string, never>, string[]>({});
  const errors = ref<string[]>([]);
  const manualSetupKey = ref<string | null>(null);
  const qrCodeSvg = ref<string | null>(null);
  const recoveryCodesList = ref<string[]>([]);
  const hasSetupData = computed(() => qrCodeSvg.value !== null && manualSetupKey.value !== null);
  const setupProcessing = computed(() => qrCodeRequest.processing || setupKeyRequest.processing);
  const recoveryCodesProcessing = computed(() => recoveryCodesRequest.processing);

  const addError = (message: string): void => {
    if (!errors.value.includes(message)) {
      errors.value.push(message);
    }
  };

  const clearErrors = (): void => {
    errors.value = [];
  };

  const clearSetupData = (): void => {
    manualSetupKey.value = null;
    qrCodeSvg.value = null;
    clearErrors();
  };

  const fetchQrCode = async (): Promise<void> => {
    try {
      const response = await qrCodeRequest.get(qrCode.url());
      qrCodeSvg.value = response.svg;
    } catch {
      qrCodeSvg.value = null;
      addError("Failed to fetch QR code");
    }
  };

  const fetchSetupKey = async (): Promise<void> => {
    try {
      const response = await setupKeyRequest.get(secretKey.url());
      manualSetupKey.value = response.secretKey;
    } catch {
      manualSetupKey.value = null;
      addError("Failed to fetch a setup key");
    }
  };

  const fetchSetupData = async (): Promise<void> => {
    clearErrors();
    await Promise.all([fetchQrCode(), fetchSetupKey()]);
  };

  const fetchRecoveryCodes = async (): Promise<void> => {
    clearErrors();

    try {
      recoveryCodesList.value = await recoveryCodesRequest.get(recoveryCodes.url());
    } catch {
      recoveryCodesList.value = [];
      addError("Failed to fetch recovery codes");
    }
  };

  return {
    qrCodeSvg,
    manualSetupKey,
    recoveryCodesList,
    errors,
    hasSetupData,
    setupProcessing,
    recoveryCodesProcessing,
    clearSetupData,
    clearErrors,
    fetchSetupData,
    fetchRecoveryCodes,
  };
}

export function provideTwoFactorAuth(): TwoFactorAuthState {
  const state = createTwoFactorAuthState();

  provide(twoFactorAuthKey, state);

  return state;
}

export function useTwoFactorAuth(): TwoFactorAuthState {
  const state = inject(twoFactorAuthKey);

  if (!state) {
    throw new Error("Two-factor authentication state must be provided by the Security page.");
  }

  return state;
}
