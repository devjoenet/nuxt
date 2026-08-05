import { router } from "@inertiajs/vue3";
import type { FormDataConvertible } from "@inertiajs/core";
import { watchDebounced } from "@vueuse/core";
import { computed, reactive, ref, toRaw, watch } from "vue";

export type InertiaFilterValue = string | number | boolean | null | undefined | readonly (string | number)[];

type FilterState = Record<string, InertiaFilterValue>;
type FilterQuery = Record<string, FormDataConvertible>;

interface UseInertiaFiltersOptions<T extends FilterState> {
  url: string | (() => string);
  initialFilters: () => T;
  resetFilters: T;
  preservedQuery?: () => FilterState;
  only?: string[];
  debounce?: number;
}

function normalizedQuery(filters: FilterState): FilterQuery {
  const query: FilterQuery = {};

  Object.entries(filters).forEach(([key, value]) => {
    if (value === "" || value === null || value === undefined) {
      return;
    }

    if (Array.isArray(value)) {
      if (value.length > 0) {
        query[key] = [...value];
      }

      return;
    }

    query[key] = value as FormDataConvertible;
  });

  return query;
}

function querySignature(query: FilterQuery): string {
  return JSON.stringify(
    Object.keys(query)
      .sort()
      .map((key) => [key, query[key]]),
  );
}

export function useInertiaFilters<T extends FilterState>(options: UseInertiaFiltersOptions<T>) {
  const filters = reactive({ ...options.initialFilters() }) as T;
  const isLoading = ref(false);
  const resetSignature = querySignature(normalizedQuery(options.resetFilters));
  const buildQuery = (currentFilters: FilterState): FilterQuery => {
    const query = normalizedQuery(options.preservedQuery?.() ?? {});

    Object.keys(currentFilters).forEach((key) => {
      delete query[key];
    });

    return { ...query, ...normalizedQuery(currentFilters) };
  };
  let committedSignature = querySignature(buildQuery(options.initialFilters()));
  let lastRequestedSignature = committedSignature;
  let cancelCurrentVisit: VoidFunction | undefined;
  let latestVisitId = 0;

  const hasActiveFilters = computed(() => querySignature(normalizedQuery(toRaw(filters))) !== resetSignature);

  const visit = (query: FilterQuery, replace: boolean): void => {
    const signature = querySignature(query);

    if (replace && (signature === committedSignature || signature === lastRequestedSignature)) {
      return;
    }

    const visitId = ++latestVisitId;
    cancelCurrentVisit?.();
    cancelCurrentVisit = undefined;
    lastRequestedSignature = signature;

    router.get(typeof options.url === "function" ? options.url() : options.url, query, {
      only: options.only,
      preserveScroll: true,
      preserveState: true,
      replace,
      onCancelToken: (token) => {
        if (visitId === latestVisitId) {
          cancelCurrentVisit = token.cancel;
        }
      },
      onStart: () => {
        if (visitId === latestVisitId) {
          isLoading.value = true;
        }
      },
      onSuccess: () => {
        if (visitId === latestVisitId) {
          committedSignature = signature;
        }
      },
      onCancel: () => {
        if (visitId === latestVisitId) {
          lastRequestedSignature = committedSignature;
        }
      },
      onHttpException: () => {
        if (visitId === latestVisitId) {
          lastRequestedSignature = committedSignature;
        }
      },
      onNetworkError: () => {
        if (visitId === latestVisitId) {
          lastRequestedSignature = committedSignature;
        }
      },
      onFinish: () => {
        if (visitId === latestVisitId) {
          isLoading.value = false;
          cancelCurrentVisit = undefined;
        }
      },
    });
  };

  const visitFilters = (): void => {
    visit(buildQuery(toRaw(filters)), true);
  };

  const reset = (): void => {
    Object.assign(filters, options.resetFilters);
    visitFilters();
  };

  const resetFilter = <K extends keyof T>(key: K): void => {
    filters[key] = options.resetFilters[key];
    visitFilters();
  };

  const visitPage = (page: number): void => {
    visit({ ...buildQuery(toRaw(filters)), page }, false);
  };

  watch(
    options.initialFilters,
    (initialFilters) => {
      Object.assign(filters, initialFilters);
      committedSignature = querySignature(buildQuery(initialFilters));
      lastRequestedSignature = committedSignature;
    },
    { deep: true },
  );

  watchDebounced(filters, visitFilters, {
    debounce: options.debounce ?? 250,
    deep: true,
  });

  return {
    filters,
    hasActiveFilters,
    isLoading,
    reset,
    resetFilter,
    visitPage,
  };
}
