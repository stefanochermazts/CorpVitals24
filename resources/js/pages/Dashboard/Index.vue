<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { useAuthStore, useDashboardStore } from '@/stores';
import { useStoreSync } from '@/composables/useStoreSync';
import FlashMessages from '@/components/FlashMessages.vue';
import type { DashboardSummary } from '@/stores/dashboard';

// Initialize store sync with Inertia shared data
useStoreSync();

const props = defineProps<{
  summary: DashboardSummary;
}>();

// Get stores
const authStore = useAuthStore();
const dashboardStore = useDashboardStore();

// Initialize dashboard data from props
onMounted(() => {
  dashboardStore.setSummary(props.summary);
});

// Use store data instead of props directly (reactive to store changes)
const summary = computed(() => dashboardStore.summary || props.summary);
const userName = computed(() => authStore.user?.name || 'User');

const formatValue = (value: number | null | undefined, displayFormat: string, unit: string): string => {
  // Handle null, undefined, or non-numeric values
  if (value === null || value === undefined || value === '') {
    return 'N/A';
  }

  // Convert to number if it's a string
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  
  // Check if conversion resulted in a valid number
  if (isNaN(numValue)) {
    return 'N/A';
  }

  if (displayFormat === 'currency') {
    return new Intl.NumberFormat('it-IT', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(numValue);
  }

  if (displayFormat === 'percentage' || unit === '%') {
    return `${numValue.toFixed(2)}%`;
  }

  if (unit === 'days') {
    return `${Math.round(numValue)} giorni`;
  }

  if (unit === 'ratio') {
    return numValue.toFixed(2);
  }

  return numValue.toLocaleString('it-IT', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
};

const getKpiIcon = (code: string): string => {
  const icons: Record<string, string> = {
    REV: '💰',
    EBITDA: '📊',
    MOL: '💹',
    NET: '✅',
    ROI: '📈',
    ROE: '🎯',
    LIQ: '💧',
    QR: '⚡',
    CCN: '💵',
    DSO: '📅',
    DPO: '🗓️',
    GR: '🚀',
    DTE: '⚖️',
    LEV: '📊',
    INV_TURN: '🔄',
  };
  
  return icons[code] || '📌';
};

const getKpiColor = (index: number): string => {
  const colors = [
    'from-blue-500 to-blue-600',
    'from-purple-500 to-purple-600',
    'from-pink-500 to-pink-600',
    'from-indigo-500 to-indigo-600',
    'from-cyan-500 to-cyan-600',
    'from-emerald-500 to-emerald-600',
  ];
  
  return colors[index % colors.length];
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Head title="Dashboard" />
    
    <!-- Flash Messages -->
    <FlashMessages />

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <Link href="/" class="text-2xl font-bold text-indigo-600">
              CorpVitals24
            </Link>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">{{ userName }}</span>
            <Link
              href="/logout"
              method="post"
              as="button"
              class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Esci
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          Dashboard KPI
        </h1>
        <p class="mt-2 text-gray-600">
          Visualizza i tuoi indicatori chiave di performance
          <span v-if="summary.company_summary.latest_period" class="font-semibold">
            - Periodo: {{ summary.company_summary.latest_period.name }}
          </span>
        </p>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">KPI Totali</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ summary.company_summary.total_kpis }}
              </p>
            </div>
            <div class="p-3 bg-indigo-100 rounded-full">
              <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">KPI Valorizzati</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ summary.company_summary.kpi_values_count }}
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Ultimo Aggiornamento</p>
              <p class="mt-2 text-lg font-semibold text-gray-900">
                {{ new Date(summary.cached_at).toLocaleString('it-IT', { 
                  day: '2-digit', 
                  month: '2-digit', 
                  hour: '2-digit', 
                  minute: '2-digit' 
                }) }}
              </p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Cards Grid -->
      <div v-if="summary.latest_kpis.length > 0" class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">KPI Principali</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(kpi, index) in summary.latest_kpis"
            :key="kpi.id"
            class="relative overflow-hidden rounded-lg shadow-lg"
          >
            <div
              class="absolute inset-0 bg-gradient-to-br opacity-90"
              :class="getKpiColor(index)"
            ></div>
            <div class="relative p-6 text-white">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <p class="text-sm font-medium opacity-90">{{ kpi.code }}</p>
                  <h3 class="text-lg font-semibold mt-1">{{ kpi.name }}</h3>
                </div>
                <span class="text-4xl">{{ getKpiIcon(kpi.code) }}</span>
              </div>
              <p class="text-3xl font-bold">
                {{ formatValue(kpi.value, kpi.display_format, kpi.unit) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nessun KPI Disponibile</h3>
        <p class="text-gray-600">Non ci sono KPI valorizzati per il periodo corrente.</p>
      </div>

      <!-- Coming Soon Section for Charts -->
      <div class="mt-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-xl p-8 text-center text-white">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-90" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
        </svg>
        <h3 class="text-2xl font-bold mb-2">Grafici Interattivi in Arrivo!</h3>
        <p class="text-lg opacity-90 mb-4">
          Stiamo lavorando per portarti visualizzazioni avanzate con Apache ECharts
        </p>
        <div class="flex justify-center space-x-4">
          <span class="px-4 py-2 bg-white bg-opacity-20 rounded-lg">📊 Trend KPI</span>
          <span class="px-4 py-2 bg-white bg-opacity-20 rounded-lg">📈 Confronti</span>
          <span class="px-4 py-2 bg-white bg-opacity-20 rounded-lg">🎯 Forecast</span>
        </div>
      </div>
    </main>
  </div>
</template>
