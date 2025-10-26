<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface LoginForm {
  email: string;
  password: string;
  remember: boolean;
}

const form = useForm<LoginForm>({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password');
    },
  });
};

const hasErrors = computed(() => Object.keys(form.errors).length > 0);
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-white to-purple-100 py-12 px-4 sm:px-6 lg:px-8">
    <Head title="Accedi" />

    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
          CorpVitals24
        </h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-2">
          Benvenuto
        </h2>
        <p class="text-gray-600">
          Accedi al tuo account per continuare
        </p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-lg shadow-xl p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Indirizzo Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.email }"
              placeholder="tuo@email.com"
            >
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.password }"
              placeholder="••••••••"
            >
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
            >
            <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer">
              Ricordami
            </label>
          </div>

          <!-- Error Alert -->
          <div
            v-if="hasErrors"
            class="bg-red-50 border border-red-200 rounded-lg p-4"
            role="alert"
          >
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Errore di autenticazione
                </h3>
                <p class="text-sm text-red-700 mt-1">
                  Controlla le credenziali e riprova.
                </p>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="form.processing"
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ form.processing ? 'Accesso in corso...' : 'Accedi' }}
            </button>
          </div>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-600">
          <p>
            Non hai un account? Contatta l'amministratore
          </p>
        </div>
      </div>

      <!-- Security Info -->
      <div class="text-center text-xs text-gray-500">
        <p>Connessione protetta con crittografia end-to-end</p>
        <p class="mt-1">© 2025 CorpVitals24. Tutti i diritti riservati.</p>
      </div>
    </div>
  </div>
</template>

