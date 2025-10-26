import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';
import type { User } from '@/types';

interface AuthState {
  user: User | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
  }),

  getters: {
    /**
     * Check if user is authenticated
     */
    isAuthenticated: (state): boolean => !!state.user,

    /**
     * Get user roles
     */
    roles: (state): string[] => state.user?.roles || [],

    /**
     * Get user permissions
     */
    permissions: (state): string[] => state.user?.permissions || [],

    /**
     * Check if user has specific role
     */
    hasRole: (state) => (role: string): boolean => {
      return state.user?.roles?.includes(role) || false;
    },

    /**
     * Check if user has specific permission
     */
    hasPermission: (state) => (permission: string): boolean => {
      return state.user?.permissions?.includes(permission) || false;
    },

    /**
     * Check if user has any of the specified roles
     */
    hasAnyRole: (state) => (roles: string[]): boolean => {
      return roles.some((role) => state.user?.roles?.includes(role));
    },

    /**
     * Check if user has all of the specified permissions
     */
    hasAllPermissions: (state) => (permissions: string[]): boolean => {
      return permissions.every((permission) => state.user?.permissions?.includes(permission));
    },

    /**
     * Get user's team ID
     */
    teamId: (state): number | null => state.user?.team_id || null,

    /**
     * Get user's company ID
     */
    companyId: (state): number | null => state.user?.company_id || null,
  },

  actions: {
    /**
     * Set user data (typically from Inertia shared props)
     */
    setUser(user: User | null) {
      this.user = user;
    },

    /**
     * Clear user data
     */
    clearUser() {
      this.user = null;
    },

    /**
     * Logout user
     */
    async logout() {
      router.post('/logout', {}, {
        onSuccess: () => {
          this.clearUser();
        },
      });
    },

    /**
     * Update user data partially
     */
    updateUser(data: Partial<User>) {
      if (this.user) {
        this.user = { ...this.user, ...data };
      }
    },
  },
});

