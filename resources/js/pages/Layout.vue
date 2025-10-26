<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Dashboard',
});

const pageTitle = computed(() => props.title);
</script>

<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <Head>
      <title>{{ pageTitle }}</title>
      <meta name="description" content="CorpVitals24 - Piattaforma KPI Contabili & Finanziari">
    </Head>

    <main>
      <slot />
    </main>
  </div>
</template>

