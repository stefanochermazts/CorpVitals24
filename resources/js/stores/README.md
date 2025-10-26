# Pinia Stores Documentation

This directory contains all Pinia stores for state management in CorpVitals24.

## Available Stores

### 1. **authStore** (`auth.ts`)
Manages user authentication state.

**State:**
- `user: User | null` - Current authenticated user

**Getters:**
- `isAuthenticated` - Check if user is logged in
- `roles` - Get user roles array
- `permissions` - Get user permissions array
- `hasRole(role)` - Check specific role
- `hasPermission(permission)` - Check specific permission
- `hasAnyRole(roles[])` - Check any of roles
- `hasAllPermissions(permissions[])` - Check all permissions
- `teamId` - Get user's team ID
- `companyId` - Get user's company ID

**Actions:**
- `setUser(user)` - Set user data
- `clearUser()` - Clear user data
- `logout()` - Logout user (calls API)
- `updateUser(data)` - Partial update user

**Usage:**
```typescript
import { useAuthStore } from '@/stores';

const authStore = useAuthStore();

// Check authentication
if (authStore.isAuthenticated) {
  console.log('User:', authStore.user?.name);
}

// Check permission
if (authStore.hasPermission('manage-kpis')) {
  // Show KPI management UI
}

// Check role
if (authStore.hasRole('admin')) {
  // Show admin panel
}
```

---

### 2. **dashboardStore** (`dashboard.ts`)
Manages dashboard data and KPIs.

**State:**
- `summary: DashboardSummary | null` - Dashboard summary data
- `allKpis: KpiData[]` - All KPIs for company
- `loading: boolean` - Loading state
- `error: string | null` - Error message

**Getters:**
- `latestKpis` - Get latest KPI values
- `companySummary` - Get company summary stats
- `kpiTrends` - Get all KPI trends
- `getKpiByCode(code)` - Find KPI by code
- `getTrendByCode(code)` - Get trend for specific KPI
- `isStale` - Check if data is older than 5 min
- `isLoading` - Loading state
- `hasError` - Error state

**Actions:**
- `setSummary(summary)` - Set dashboard summary
- `fetchAllKpis()` - Fetch all KPIs from API
- `refreshDashboard()` - Force reload dashboard
- `clearDashboard()` - Clear all data
- `setError(error)` - Set error message
- `clearError()` - Clear error

**Usage:**
```typescript
import { useDashboardStore } from '@/stores';

const dashboardStore = useDashboardStore();

// Get latest KPIs
const kpis = dashboardStore.latestKpis;

// Get specific KPI
const revenue = dashboardStore.getKpiByCode('REV');

// Fetch all KPIs
await dashboardStore.fetchAllKpis();

// Refresh dashboard
await dashboardStore.refreshDashboard();
```

---

### 3. **appStore** (`app.ts`)
Manages global application state.

**State:**
- `flash: FlashMessages` - Flash messages
- `globalLoading: boolean` - Global loading indicator
- `sidebarOpen: boolean` - Sidebar state
- `theme: 'light' | 'dark'` - Current theme

**Getters:**
- `hasFlashMessages` - Check if any messages exist
- `flashMessagesArray` - Get messages as array
- `isSidebarOpen` - Sidebar state
- `isGlobalLoading` - Global loading state
- `currentTheme` - Current theme
- `isDarkMode` - Check if dark mode

**Actions:**
- `setFlashMessages(flash)` - Set flash messages
- `clearFlashMessages()` - Clear all messages
- `clearFlashMessage(type)` - Clear specific type
- `addFlashMessage(type, message)` - Add message (auto-clears after 5s)
- `showSuccess(message)` - Show success message
- `showError(message)` - Show error message
- `showWarning(message)` - Show warning message
- `showInfo(message)` - Show info message
- `toggleSidebar()` - Toggle sidebar
- `setSidebarOpen(open)` - Set sidebar state
- `setGlobalLoading(loading)` - Set global loading
- `toggleTheme()` - Toggle theme
- `setTheme(theme)` - Set specific theme
- `persistTheme()` - Save theme to localStorage
- `loadTheme()` - Load theme from localStorage

**Usage:**
```typescript
import { useAppStore } from '@/stores';

const appStore = useAppStore();

// Show flash message
appStore.showSuccess('KPI saved successfully!');

// Toggle sidebar
appStore.toggleSidebar();

// Set loading
appStore.setGlobalLoading(true);
```

---

## Composables

### `useStoreSync` (`composables/useStoreSync.ts`)
Automatically syncs Pinia stores with Inertia shared props.

**Usage:**
```typescript
import { useStoreSync } from '@/composables/useStoreSync';

// In your component
useStoreSync(); // Call once to sync auth and flash
```

### `useFlash` (`composables/useFlash.ts`)
Convenient helper for flash messages.

**Usage:**
```typescript
import { useFlash } from '@/composables/useFlash';

const { showSuccess, showError, showWarning, showInfo, clearFlash } = useFlash();

// Show messages
showSuccess('Operation completed!');
showError('Something went wrong!');
showWarning('Be careful!');
showInfo('FYI: This is informational');

// Clear all
clearFlash();
```

---

## Integration with Inertia

Stores are automatically synced with Inertia's shared props via `useStoreSync()` composable.

**In your components:**
```vue
<script setup lang="ts">
import { useAuthStore, useDashboardStore } from '@/stores';
import { useStoreSync } from '@/composables/useStoreSync';

// Sync stores with Inertia shared data
useStoreSync();

// Use stores
const authStore = useAuthStore();
const dashboardStore = useDashboardStore();
</script>
```

---

## TypeScript Types

All stores are fully typed with TypeScript. Import types from stores:

```typescript
import type { 
  KpiData, 
  CompanySummary, 
  KpiTrend, 
  DashboardSummary 
} from '@/stores/dashboard';

import type { User, FlashMessages } from '@/types';
```

---

## Best Practices

1. **Use stores instead of props** - Stores are reactive and persist across navigation
2. **Call `useStoreSync()` in layouts** - Ensures stores are always up-to-date
3. **Use composables** - `useFlash()` for messages, `useStoreSync()` for sync
4. **Type safety** - Always use TypeScript types for store data
5. **Error handling** - Check `hasError` getter and display errors to user
6. **Loading states** - Use `isLoading` to show spinners/skeletons

---

## File Structure

```
resources/js/stores/
├── index.ts              # Barrel export for all stores
├── auth.ts               # Authentication store
├── dashboard.ts          # Dashboard & KPI store
├── app.ts                # Global app state store
└── README.md             # This file

resources/js/composables/
├── useStoreSync.ts       # Sync stores with Inertia
├── useFlash.ts           # Flash message helper
└── usePageProps.ts       # Inertia page props helper
```

