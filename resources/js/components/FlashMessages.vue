<script setup lang="ts">
import { computed } from 'vue';
import { useAppStore } from '@/stores';
import { TransitionRoot } from '@headlessui/vue';

const appStore = useAppStore();

const messages = computed(() => appStore.flashMessagesArray);

const getIconForType = (type: string) => {
  switch (type) {
    case 'success':
      return `
        <svg class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      `;
    case 'error':
      return `
        <svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      `;
    case 'warning':
      return `
        <svg class="w-6 h-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      `;
    case 'info':
      return `
        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      `;
    default:
      return '';
  }
};

const getColorClasses = (type: string) => {
  switch (type) {
    case 'success':
      return 'bg-green-50 border-green-400';
    case 'error':
      return 'bg-red-50 border-red-400';
    case 'warning':
      return 'bg-yellow-50 border-yellow-400';
    case 'info':
      return 'bg-blue-50 border-blue-400';
    default:
      return 'bg-gray-50 border-gray-400';
  }
};

const dismissMessage = (type: string) => {
  appStore.clearFlashMessage(type as any);
};
</script>

<template>
  <div
    aria-live="assertive"
    class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50"
  >
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <TransitionRoot
        v-for="(message, index) in messages"
        :key="index"
        :show="true"
        as="template"
        enter="transform ease-out duration-300 transition"
        enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to="translate-y-0 opacity-100 sm:translate-x-0"
        leave="transition ease-in duration-100"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 border-l-4"
          :class="getColorClasses(message.type)"
        >
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0" v-html="getIconForType(message.type)"></div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">
                  {{ message.type.charAt(0).toUpperCase() + message.type.slice(1) }}
                </p>
                <p class="mt-1 text-sm text-gray-500">{{ message.message }}</p>
              </div>
              <div class="ml-4 flex flex-shrink-0">
                <button
                  type="button"
                  @click="dismissMessage(message.type)"
                  class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionRoot>
    </div>
  </div>
</template>

