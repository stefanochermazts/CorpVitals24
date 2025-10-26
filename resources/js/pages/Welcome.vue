<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePageProps } from '@/composables/usePageProps';

const { auth } = usePageProps();
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
    <Head title="Benvenuto" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Header -->
      <header class="text-center mb-12">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">
          CorpVitals24
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Piattaforma multi-azienda per KPI contabili e finanziari
        </p>
      </header>

      <!-- Feature Cards -->
      <div class="grid md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-indigo-600">
          <div class="text-indigo-600 mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            Dashboard Interattive
          </h3>
          <p class="text-gray-600">
            Visualizza KPI, trend e confronti in tempo reale
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-600">
          <div class="text-purple-600 mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            KPI Engine
          </h3>
          <p class="text-gray-600">
            20+ KPI standard con calcoli automatici e tracciabilità
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-pink-600">
          <div class="text-pink-600 mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            Multi-Tenant
          </h3>
          <p class="text-gray-600">
            Gestione multi-azienda con ruoli e permessi granulari
          </p>
        </div>
      </div>

      <!-- Status Card -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
          🎉 Setup Frontend Completato!
        </h2>
        <div class="space-y-2 text-gray-700">
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">Vue 3.5+</strong> con Composition API e TypeScript
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">Inertia.js</strong> per SPA senza API REST
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">Tailwind CSS 4</strong> con utility-first approach
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">Pinia 2</strong> per state management
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">Headless UI</strong> per componenti accessibili
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">VueUse</strong> per composables utilities
          </p>
          <p class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            <strong class="mr-2">TypeScript Strict Mode</strong> abilitato
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-center gap-4">
        <Link
          v-if="auth.user"
          href="/dashboard"
          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
        >
          Vai alla Dashboard
        </Link>
        <Link
          v-else
          href="/login"
          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
          Accedi
        </Link>
        <a
          href="https://github.com/yourusername/corpvitals24"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-semibold rounded-lg shadow-md hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
          </svg>
          GitHub
        </a>
      </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-8 text-gray-600">
      <p>&copy; 2025 CorpVitals24. Built with Laravel, Vue 3, and Inertia.js</p>
    </footer>
  </div>
</template>

