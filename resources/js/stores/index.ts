/**
 * Barrel export for all Pinia stores
 */

export { useAuthStore } from './auth';
export { useDashboardStore } from './dashboard';
export { useAppStore } from './app';

export type { KpiData, CompanySummary, KpiTrend, DashboardSummary } from './dashboard';

