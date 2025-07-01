import { derived } from 'svelte/store';

import { Theme } from '@slink/lib/settings';
import type { Setter } from '@slink/lib/settings/Settings.types';

export const ThemeSetter: Setter<'theme', Theme> = (value) => {
  return {
    value,
    isDark: derived(value, ($theme): boolean => $theme === Theme.DARK),
    isLight: derived(value, ($theme): boolean => $theme === Theme.LIGHT),
  };
};
