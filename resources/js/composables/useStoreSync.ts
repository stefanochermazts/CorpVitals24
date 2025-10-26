import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useAppStore } from '@/stores/app';
import type { PageProps } from '@/types';

/**
 * Composable to sync Pinia stores with Inertia shared data
 * Call this in your root component (app.ts) to keep stores updated
 */
export function useStoreSync() {
  const page = usePage<PageProps>();
  const authStore = useAuthStore();
  const appStore = useAppStore();

  // Sync auth state
  watch(
    () => page.props.auth,
    (auth) => {
      if (auth?.user) {
        authStore.setUser(auth.user);
      } else {
        authStore.clearUser();
      }
    },
    { immediate: true, deep: true }
  );

  // Sync flash messages
  watch(
    () => page.props.flash,
    (flash) => {
      if (flash) {
        appStore.setFlashMessages(flash);
      }
    },
    { immediate: true, deep: true }
  );

  return {
    authStore,
    appStore,
  };
}

