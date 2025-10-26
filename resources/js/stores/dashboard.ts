import { defineStore } from 'pinia';
import axios from 'axios';

export interface KpiData {
  id: number;
  code: string;
  name: string;
  value: number | null;
  unit: string;
  display_format: string;
}

export interface CompanySummary {
  total_kpis: number;
  latest_period: {
    id: number;
    name: string;
    start: string;
    end: string;
  } | null;
  kpi_values_count: number;
}

export interface KpiTrend {
  period: string;
  value: number | null;
}

export interface DashboardSummary {
  user: {
    id: number;
    name: string;
    email: string;
    team_id: number;
    company_id: number;
  };
  company_summary: CompanySummary;
  latest_kpis: KpiData[];
  kpi_trends: Record<string, KpiTrend[]>;
  cached_at: string;
}

interface DashboardState {
  summary: DashboardSummary | null;
  allKpis: KpiData[];
  loading: boolean;
  error: string | null;
}

export const useDashboardStore = defineStore('dashboard', {
  state: (): DashboardState => ({
    summary: null,
    allKpis: [],
    loading: false,
    error: null,
  }),

  getters: {
    /**
     * Get latest KPIs
     */
    latestKpis: (state): KpiData[] => state.summary?.latest_kpis || [],

    /**
     * Get company summary
     */
    companySummary: (state): CompanySummary | null => state.summary?.company_summary || null,

    /**
     * Get KPI trends
     */
    kpiTrends: (state): Record<string, KpiTrend[]> => state.summary?.kpi_trends || {},

    /**
     * Get specific KPI by code
     */
    getKpiByCode: (state) => (code: string): KpiData | undefined => {
      return state.summary?.latest_kpis.find((kpi) => kpi.code === code);
    },

    /**
     * Get trend data for specific KPI
     */
    getTrendByCode: (state) => (code: string): KpiTrend[] => {
      return state.summary?.kpi_trends[code] || [];
    },

    /**
     * Check if data is stale (older than 5 minutes)
     */
    isStale: (state): boolean => {
      if (!state.summary?.cached_at) return true;
      
      const cachedTime = new Date(state.summary.cached_at).getTime();
      const now = new Date().getTime();
      const fiveMinutes = 5 * 60 * 1000;
      
      return now - cachedTime > fiveMinutes;
    },

    /**
     * Get loading state
     */
    isLoading: (state): boolean => state.loading,

    /**
     * Get error state
     */
    hasError: (state): boolean => !!state.error,
  },

  actions: {
    /**
     * Set dashboard summary (typically from Inertia page props)
     */
    setSummary(summary: DashboardSummary) {
      this.summary = summary;
      this.error = null;
    },

    /**
     * Fetch all KPIs from API
     */
    async fetchAllKpis() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get('/api/dashboard/kpis');
        this.allKpis = response.data.kpis;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch KPIs';
        console.error('Error fetching KPIs:', error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Refresh dashboard data (force reload)
     */
    async refreshDashboard() {
      this.loading = true;
      this.error = null;

      try {
        // Trigger Inertia reload to get fresh data
        const { router } = await import('@inertiajs/vue3');
        router.reload({ only: ['summary'] });
      } catch (error: any) {
        this.error = 'Failed to refresh dashboard';
        console.error('Error refreshing dashboard:', error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Clear dashboard data
     */
    clearDashboard() {
      this.summary = null;
      this.allKpis = [];
      this.error = null;
    },

    /**
     * Set error
     */
    setError(error: string) {
      this.error = error;
    },

    /**
     * Clear error
     */
    clearError() {
      this.error = null;
    },
  },
});

