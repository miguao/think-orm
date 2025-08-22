import type { Ref } from 'vue';
import { inject, computed } from 'vue';
import { STR_KEY as VAL_KEY } from '../utils/common';
import { CONFIG_KEY } from './props';
import type { GlobalProvide, UseLocaleResult, LocaleProps } from './types';
import defaultLocale from '../lang/zh_CN';
export { VAL_KEY };

/**
 * 获取全局配置
 */
export function useReceiver(): GlobalProvide {
  return inject<GlobalProvide>(CONFIG_KEY, {});
}

/**
 * 获取全局属性
 */
export function useGlobalProps<T>(name: string): Ref<T> {
  const globalConfig = useReceiver();
  return computed<T>(() => globalConfig[name] ?? {});
}

/**
 * 获取国际化
 */
export function useLocale<T>(
  name?: string,
  props?: LocaleProps<T>
): UseLocaleResult<T> {
  const globalConfig = useReceiver();
  const lang = computed<T>(() => {
    const temp = globalConfig.locale ?? defaultLocale;
    if (name) {
      try {
        return Object.assign({}, temp[name] ?? {}, props?.locale);
      } catch (e) {
        console.error(e, VAL_KEY);
      }
    }
    return temp;
  });
  return { lang, globalConfig };
}
