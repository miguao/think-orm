import { onBeforeUnmount } from 'vue';
import type { StyleValue } from '../ele-app/types';
import { STR_KEY as USR_KEY } from '../utils/common';
import type { WatermarkGap, WatermarkFont, MutationOption } from './types';
export { USR_KEY };

/**
 * 获取文字样式
 */
export function getFont(font?: WatermarkFont): Required<WatermarkFont> {
  const style: Required<WatermarkFont> = {
    color: 'rgba(122, 122, 122, .35)',
    fontSize: 16,
    fontWeight: 'normal',
    fontFamily: 'sans-serif',
    fontStyle: 'normal'
  };
  return Object.assign(style, font);
}

/**
 * 获取间距
 */
export function getGap(gap?: WatermarkGap) {
  const [gapX, gapY] = gap ?? [];
  return [gapX ?? 100, gapY ?? 100];
}

/**
 * 返回设备像素密度
 */
export function getPixelRatio() {
  return window.devicePixelRatio || 1;
}

/**
 * 旋转水印
 */
export function rotateWatermark(
  ctx: CanvasRenderingContext2D,
  rotateX: number,
  rotateY: number,
  rotate: number
) {
  try {
    ctx.translate(rotateX, rotateY);
    ctx.rotate((Math.PI / 180) * Number(rotate));
    ctx.translate(-rotateX, -rotateY);
  } catch (e) {
    console.error(e, USR_KEY);
  }
}

/**
 * 样式对象转字符串
 * @param style 样式
 */
export function joinStyle(style: StyleValue) {
  const result = Object.keys(style).map((key) => {
    const name = key
      .replace(/([A-Z])/g, ' $1')
      .trim()
      .split(' ')
      .join('-')
      .toLowerCase();
    return `${name}:${style[key]}`;
  });
  return result.join(';');
}

/**
 * 水印篡改观测
 */
export function useMutation(option: MutationOption) {
  const { getRoot, getEl, onDeleted, onDalsified } = option;
  /** 节点删除观测器 */
  const deletedObserver = new MutationObserver((mutations) => {
    const el = getEl();
    mutations.forEach((mutation) => {
      if (
        mutation.type === 'childList' &&
        Array.from(mutation.removedNodes).some((n) => n === el)
      ) {
        onDeleted();
      }
    });
  });

  /** 样式篡改观测器 */
  const falsifiedObserver = new MutationObserver(() => {
    onDalsified();
  });

  /** 开始观测 */
  const observe = () => {
    const el = getEl();
    if (el) {
      falsifiedObserver.observe(el, { attributes: true });
    }
    const root = getRoot();
    if (root) {
      deletedObserver.observe(root, { childList: true });
    }
  };

  /** 结束观测 */
  const disconnect = () => {
    falsifiedObserver.disconnect();
    deletedObserver.disconnect();
  };

  onBeforeUnmount(() => {
    disconnect();
  });

  return { observe, disconnect };
}
