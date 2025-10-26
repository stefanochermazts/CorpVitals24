import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export function usePageProps() {
  const page = usePage<PageProps>();

  const auth = computed(() => page.props.auth);
  const user = computed(() => page.props.auth.user);
  const flash = computed(() => page.props.flash);

  return {
    auth,
    user,
    flash,
    page,
  };
}

