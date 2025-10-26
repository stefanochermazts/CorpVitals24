import { useAppStore } from '@/stores';

/**
 * Composable for easy access to flash message methods
 */
export function useFlash() {
  const appStore = useAppStore();

  return {
    showSuccess: (message: string) => appStore.showSuccess(message),
    showError: (message: string) => appStore.showError(message),
    showWarning: (message: string) => appStore.showWarning(message),
    showInfo: (message: string) => appStore.showInfo(message),
    clearFlash: () => appStore.clearFlashMessages(),
  };
}

