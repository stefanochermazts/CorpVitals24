import { defineStore } from 'pinia';
import type { FlashMessages } from '@/types';

interface AppState {
  flash: FlashMessages;
  globalLoading: boolean;
  sidebarOpen: boolean;
  theme: 'light' | 'dark';
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    flash: {
      success: undefined,
      error: undefined,
      warning: undefined,
      info: undefined,
    },
    globalLoading: false,
    sidebarOpen: true,
    theme: 'light',
  }),

  getters: {
    /**
     * Check if there are any flash messages
     */
    hasFlashMessages: (state): boolean => {
      return !!(
        state.flash.success ||
        state.flash.error ||
        state.flash.warning ||
        state.flash.info
      );
    },

    /**
     * Get all flash messages as array
     */
    flashMessagesArray: (state): Array<{ type: string; message: string }> => {
      const messages: Array<{ type: string; message: string }> = [];

      if (state.flash.success) {
        messages.push({ type: 'success', message: state.flash.success });
      }
      if (state.flash.error) {
        messages.push({ type: 'error', message: state.flash.error });
      }
      if (state.flash.warning) {
        messages.push({ type: 'warning', message: state.flash.warning });
      }
      if (state.flash.info) {
        messages.push({ type: 'info', message: state.flash.info });
      }

      return messages;
    },

    /**
     * Check if sidebar is open
     */
    isSidebarOpen: (state): boolean => state.sidebarOpen,

    /**
     * Check if global loading is active
     */
    isGlobalLoading: (state): boolean => state.globalLoading,

    /**
     * Get current theme
     */
    currentTheme: (state): 'light' | 'dark' => state.theme,

    /**
     * Check if dark mode is active
     */
    isDarkMode: (state): boolean => state.theme === 'dark',
  },

  actions: {
    /**
     * Set flash messages (typically from Inertia shared props)
     */
    setFlashMessages(flash: FlashMessages) {
      this.flash = flash;
    },

    /**
     * Clear all flash messages
     */
    clearFlashMessages() {
      this.flash = {
        success: undefined,
        error: undefined,
        warning: undefined,
        info: undefined,
      };
    },

    /**
     * Clear specific flash message type
     */
    clearFlashMessage(type: keyof FlashMessages) {
      this.flash[type] = undefined;
    },

    /**
     * Add a flash message programmatically
     */
    addFlashMessage(type: keyof FlashMessages, message: string) {
      this.flash[type] = message;

      // Auto-clear after 5 seconds
      setTimeout(() => {
        this.clearFlashMessage(type);
      }, 5000);
    },

    /**
     * Show success message
     */
    showSuccess(message: string) {
      this.addFlashMessage('success', message);
    },

    /**
     * Show error message
     */
    showError(message: string) {
      this.addFlashMessage('error', message);
    },

    /**
     * Show warning message
     */
    showWarning(message: string) {
      this.addFlashMessage('warning', message);
    },

    /**
     * Show info message
     */
    showInfo(message: string) {
      this.addFlashMessage('info', message);
    },

    /**
     * Toggle sidebar
     */
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    /**
     * Set sidebar state
     */
    setSidebarOpen(open: boolean) {
      this.sidebarOpen = open;
    },

    /**
     * Set global loading state
     */
    setGlobalLoading(loading: boolean) {
      this.globalLoading = loading;
    },

    /**
     * Toggle theme
     */
    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light';
      this.persistTheme();
    },

    /**
     * Set theme
     */
    setTheme(theme: 'light' | 'dark') {
      this.theme = theme;
      this.persistTheme();
    },

    /**
     * Persist theme to localStorage
     */
    persistTheme() {
      localStorage.setItem('corpvitals24_theme', this.theme);
    },

    /**
     * Load theme from localStorage
     */
    loadTheme() {
      const savedTheme = localStorage.getItem('corpvitals24_theme');
      if (savedTheme === 'dark' || savedTheme === 'light') {
        this.theme = savedTheme;
      }
    },
  },
});

