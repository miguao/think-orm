import type { InjectionKey } from 'vue';
import type { Mutable, SlotObject } from '../ele-app/types';
export * from './core';

/**
 * 防抖函数
 * @param func 函数
 * @param wait 等待时间
 */
export function debounce<T extends (...args: any) => any>(
  func: T,
  wait: number
) {
  let timer: number | undefined = void 0;
  const debounced: (this: any) => void = function (...args) {
    if (timer) {
      clearTimeout(timer);
    }
    timer = setTimeout(() => {
      func.apply(this, args);
      timer = void 0;
    }, wait) as unknown as number;
  };
  return debounced;
}

/**
 * 节流函数
 * @param func 函数
 * @param wait 等待时间
 * @param trailing 是否在节流结束后调用
 */
export function throttle<T extends (...args: any) => any>(
  func: T,
  wait: number,
  trailing?: boolean
) {
  let timer: number | undefined = void 0;
  const debounced: (this: any) => void = function (...args) {
    if (!timer) {
      if (!trailing) {
        func.apply(this, args);
      }
      timer = setTimeout(() => {
        if (trailing) {
          func.apply(this, args);
        }
        timer = void 0;
      }, wait) as unknown as number;
    }
  };
  return debounced;
}

/**
 * 忽略对象属性
 * @param obj 来源对象
 * @param fields 忽略的属性
 */
export function omit<T extends {}, K extends keyof T>(
  obj: T | null | undefined,
  fields: K[]
): Mutable<Omit<T, K>> {
  const result = Object.assign({}, obj);
  if (obj) {
    for (const key of fields) {
      delete result[key];
    }
  }
  return result;
}

/**
 * 摘选对象属性
 * @param obj 来源对象
 * @param fields 摘选的属性
 */
export function pick<T extends object, K extends keyof T>(
  obj: T,
  fields: K[]
): Mutable<Pick<T, K>> {
  const result: Pick<T, K> = {} as Pick<T, K>;
  if (obj) {
    for (const key of fields) {
      result[key] = obj[key];
    }
  }
  return result;
}

/**
 * 根据字段路径获取值
 * @param data 数据
 * @param path 字段路径
 * @param defaultValue 默认值
 */
export function getValue<T, K>(
  data?: K | null,
  path?: string | string[] | ((d: K) => T),
  defaultValue?: T
): T | undefined {
  if (data == null) {
    return defaultValue;
  }
  const pathType = typeof path;
  if (pathType === 'function') {
    return (path as any)(data);
  }
  const fields: string[] =
    (pathType === 'string'
      ? (path as string).match(/[^\[\].]+/g)
      : (path as string[] | undefined)) ?? [];
  let result: any = data;
  for (const key of fields) {
    result = result[key.trim()];
  }
  return (typeof result === 'undefined' ? defaultValue : result) as T;
}

/**
 * 首字母大写
 * @param str 字符串
 */
export function capitalize(str: string) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * 获取映射后的插槽
 * @param slots 插槽
 * @param slotsMap 插槽映射
 * @param excludes 排除的插槽映射
 * @param excludeSlots 排除的插槽
 * @param reserveSlots 是否保留所有的插槽
 */
export function getMappedSlots(
  slots: SlotObject,
  slotsMap?: Record<string, string>,
  excludes?: string[],
  excludeSlots?: string[],
  reserveSlots?: boolean
) {
  const resultSlots: SlotObject = {};
  const userSlots = slotsMap || {};
  Object.keys(userSlots).forEach((name) => {
    const slotName = userSlots[name];
    if (
      !(excludes || []).includes(name) &&
      slotName &&
      !(excludeSlots || []).includes(slotName)
    ) {
      resultSlots[name] = slots[slotName];
    }
  });
  if (reserveSlots) {
    Object.keys(slots).forEach((name) => {
      if (!resultSlots[name] && !(excludeSlots || []).includes(name)) {
        resultSlots[name] = slots[name];
      }
    });
  }
  return resultSlots;
}

/**
 * 获取可用的插槽映射
 * @param slots 插槽
 * @param slotsMap 插槽映射
 * @param excludes 排除的插槽映射
 * @param excludeSlots 排除的插槽
 * @param reserveSlots 是否保留所有的插槽
 */
export function getSlotsMap(
  slots: SlotObject,
  slotsMap?: Record<string, string>,
  excludes?: string[],
  excludeSlots?: string[],
  reserveSlots?: boolean
) {
  const resultMap: Record<string, string> = {};
  const userSlots = slotsMap || {};
  Object.keys(userSlots).forEach((name) => {
    const slotName = userSlots[name];
    if (
      !(excludes || []).includes(name) &&
      slotName &&
      !(excludeSlots || []).includes(slotName)
    ) {
      resultMap[name] = slotName;
    }
  });
  if (reserveSlots) {
    Object.keys(slots).forEach((name) => {
      if (!resultMap[name] && !(excludeSlots || []).includes(name)) {
        resultMap[name] = name;
      }
    });
  }
  return resultMap;
}
export const STR_KEY = Symbol('STR_KEY') as InjectionKey<any>;

/**
 * 获取节点样式
 * @param el 节点
 */
export function getCurrentStyle(el: Element): CSSStyleDeclaration {
  return el['currentStyle'] || window.getComputedStyle(el, null) || {};
}

/**
 * 判断元素内容是否溢出省略
 * @param el 元素节点
 * @param direction 只判断单个方向是否溢出
 */
export function contentIsEllipsis(
  el: HTMLElement,
  direction?: 'horizontal' | 'vertical'
): boolean {
  if (!el || !el.childNodes.length) {
    return false;
  }
  const range = document.createRange();
  range.setStart(el, 0);
  range.setEnd(el, el.childNodes.length);
  const { width, height } = range.getBoundingClientRect();
  const floorW = Math.floor(width);
  const rangeWidth = width - floorW < 0.008 ? floorW : width;
  const floorH = Math.floor(height);
  const rangeHeight = height - floorH < 0.008 ? floorH : height;
  const style = getCurrentStyle(el);
  const top = Number.parseInt(style.paddingTop) || 0;
  const left = Number.parseInt(style.paddingLeft) || 0;
  const right = Number.parseInt(style.paddingRight) || 0;
  const bottom = Number.parseInt(style.paddingBottom) || 0;
  const horizontalPadding = left + right;
  const verticalPadding = top + bottom;
  if (direction === 'horizontal') {
    return (
      rangeWidth + horizontalPadding > el.offsetWidth ||
      el.scrollWidth > el.offsetWidth
    );
  }
  if (direction === 'vertical') {
    return (
      rangeHeight + verticalPadding > el.offsetHeight ||
      el.scrollHeight > el.offsetHeight
    );
  }
  return (
    rangeWidth + horizontalPadding > el.offsetWidth ||
    rangeHeight + verticalPadding > el.offsetHeight ||
    el.scrollWidth > el.offsetWidth
  );
}

/**
 * 下载文件
 * @param url 文件地址
 * @param name 文件名
 */
export function downloadUrl(url: string, name: string) {
  const a = document.createElement('a');
  a.href = url;
  a.download = name;
  a.style.display = 'none';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

/**
 * 下载文件
 * @param data 二进制数据
 * @param name 文件名
 * @param type 文件类型
 */
export function download(
  data: Blob | ArrayBuffer | string,
  name: string,
  type?: string
) {
  const blob = new Blob([data], { type: type || 'application/octet-stream' });
  const url = URL.createObjectURL(blob);
  downloadUrl(url, name);
  URL.revokeObjectURL(url);
}
