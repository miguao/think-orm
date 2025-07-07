import type { Ref } from 'vue';
import { unref } from 'vue';
import type { ElDialogInstance } from '../ele-app/el';
import { getCurrentStyle, queryChild } from '../utils/common';
import { useMoveEvent } from '../utils/hook';
import type { MoveOut, MoveOutValue, Position } from './types';
import type { ModalProps } from './props';
export const containerClass = 'ele-modal-container';
export const wrapperClass = 'ele-modal';
export const closedClass = 'ele-modal-closed';
const fixTop = 0.65;
const fixLeft = 0.65;

/**
 * 获取弹窗节点
 * @param dialogRef 弹窗实例
 */
function getModalEl(dialogRef: Ref<ElDialogInstance>) {
  const el = unref(unref(dialogRef)?.dialogContentRef)?.$el;
  return el as HTMLElement | undefined;
}

/**
 * 初始化弹窗样式
 * @param modalEl 弹窗节点
 */
function initModalStyle(modalEl: HTMLElement) {
  modalEl.style.top = modalEl.offsetTop + 'px';
  modalEl.style.left = modalEl.offsetLeft + 'px';
  modalEl.style.right = 'auto';
  modalEl.style.bottom = 'auto';
  modalEl.style.position = 'absolute';
  modalEl.style.margin = '0';
}

/**
 * 是否可拉出指定方向
 * @param moveOut 拉出值
 * @param direction 方向
 */
function canMoveOut(moveOut?: MoveOut, direction?: MoveOutValue): boolean {
  if (direction && moveOut != null && Array.isArray(moveOut)) {
    return moveOut.includes(direction);
  }
  return false;
}

/**
 * 获取弹窗容器
 * @param inner 是否限制在主体内部
 * @param multiple 是否支持同时打开多个
 * @param appendTo 自定义插入的容器
 * @param modalsEl 限制在主体内部时的容器
 */
export function getModalContainer(
  inner?: boolean,
  multiple?: boolean,
  appendTo?: string | HTMLElement,
  modalsEl?: HTMLElement | null
): HTMLElement | string {
  if (multiple) {
    const parent = (inner ? modalsEl : void 0) || document.body;
    const wrapper = queryChild(parent, containerClass);
    if (wrapper) {
      return wrapper as HTMLElement;
    }
    const elem = document.createElement('div');
    elem.classList.add(containerClass);
    parent.appendChild(elem);
    return elem;
  }
  if (inner && modalsEl) {
    return modalsEl;
  }
  return appendTo || 'body';
}

/**
 * 弹窗支持移动
 * @param dialogRef 弹窗实例
 * @param props 属性
 * @param isFullscreen 全屏状态
 */
export function useModalMove(
  dialogRef: Ref<ElDialogInstance>,
  props: ModalProps,
  isFullscreen: Ref<boolean>
) {
  let modalEl: HTMLElement | undefined | null = null;
  let wrapEl: HTMLElement | undefined | null = null;
  let downOL: number | null = null;
  let downOT: number | null = null;

  const { handleMousedown, handleTouchstart } = useMoveEvent({
    start: () => {
      modalEl = getModalEl(dialogRef);
      wrapEl = modalEl?.parentElement;
      if (!modalEl || !wrapEl || !props.draggable || isFullscreen.value) {
        return;
      }
      modalEl.style.userSelect = 'none';
      initModalStyle(modalEl);
      downOL = modalEl.offsetLeft;
      downOT = modalEl.offsetTop;
    },
    move: ({ distanceX, distanceY, e }) => {
      if (
        !modalEl ||
        !wrapEl ||
        downOL == null ||
        downOT == null ||
        distanceX == null ||
        distanceY == null
      ) {
        return;
      }
      e.preventDefault();
      let positionLeft = distanceX + downOL;
      let positionTop = distanceY + downOT;
      // 边界判断
      const limitL = wrapEl.clientWidth - modalEl.clientWidth - fixLeft;
      const limitT = wrapEl.clientHeight - modalEl.clientHeight - fixTop;
      if (!props.moveOut) {
        if (positionLeft < 0) {
          positionLeft = 0;
        } else if (positionLeft > limitL) {
          positionLeft = limitL;
        }
        if (positionTop > limitT) {
          positionTop = limitT;
        }
        if (positionTop < 0) {
          positionTop = 0;
        }
      } else {
        if (!canMoveOut(props.moveOut, 'left') && positionLeft < 0) {
          positionLeft = 0;
        }
        if (!canMoveOut(props.moveOut, 'right') && positionLeft > limitL) {
          positionLeft = limitL;
        }
        if (!canMoveOut(props.moveOut, 'bottom') && positionTop > limitT) {
          positionTop = limitT;
        }
        if (!canMoveOut(props.moveOut, 'top') && positionTop < 0) {
          positionTop = 0;
        }
        const minLimitL = wrapEl.clientWidth - 48;
        if (positionLeft > minLimitL) {
          positionLeft = minLimitL;
        }
        const minLimitT = wrapEl.clientHeight - 48;
        if (props.multiple && positionTop > minLimitT) {
          positionTop = minLimitT;
        }
      }
      // 移动
      modalEl.style.left = `${Math.floor(positionLeft)}px`;
      modalEl.style.top = `${Math.floor(positionTop)}px`;
    },
    end: () => {
      if (modalEl) {
        modalEl.style.userSelect = '';
      }
      downOL = null;
      downOT = null;
      modalEl = null;
      wrapEl = null;
    },
    touchmoveOptions: { passive: false }
  });

  return {
    handleHeaderMousedown: handleMousedown,
    handleHeaderTouchstart: handleTouchstart
  };
}

/**
 * 弹窗支持拉伸
 * @param dialogRef 弹窗实例
 * @param props 属性
 * @param isFullscreen 全屏状态
 */
export function useModalResize(
  dialogRef: Ref<ElDialogInstance>,
  props: ModalProps,
  isFullscreen: Ref<boolean>
) {
  let modalEl: HTMLElement | undefined | null = null;
  let wrapEl: HTMLElement | undefined | null = null;
  let downW: number | null = null;
  let downH: number | null = null;

  const { handleMousedown, handleTouchstart } = useMoveEvent({
    start: () => {
      modalEl = getModalEl(dialogRef);
      wrapEl = modalEl?.parentElement;
      if (!modalEl || !wrapEl || !props.resizable || isFullscreen.value) {
        return;
      }
      modalEl.style.userSelect = 'none';
      initModalStyle(modalEl);
      downW = modalEl.clientWidth;
      downH = modalEl.clientHeight;
    },
    move: ({ distanceX, distanceY, e }) => {
      if (
        !modalEl ||
        !wrapEl ||
        downW == null ||
        downH == null ||
        distanceX == null ||
        distanceY == null
      ) {
        return;
      }
      e.preventDefault();
      if (props.resizable !== 'vertical') {
        const w = distanceX + downW;
        const maxW = wrapEl.clientWidth - modalEl.offsetLeft - fixLeft;
        const nw =
          (w < props.minWidth
            ? props.minWidth
            : !canMoveOut(props.moveOut, 'right') && w > maxW
              ? maxW
              : w) + 'px';
        modalEl.style.width = nw;
        modalEl.style.maxWidth = nw;
        modalEl.style.minWidth = nw;
      }
      if (props.resizable !== 'horizontal') {
        const h = distanceY + downH;
        const maxH = wrapEl.clientHeight - modalEl.offsetTop - fixTop;
        const nh =
          (h < props.minHeight
            ? props.minHeight
            : !canMoveOut(props.moveOut, 'bottom') && h > maxH
              ? maxH
              : h) + 'px';
        modalEl.style.height = nh;
        modalEl.style.maxHeight = nh;
        modalEl.style.minHeight = nh;
      }
    },
    end: () => {
      if (modalEl) {
        modalEl.style.userSelect = '';
      }
      downW = null;
      downH = null;
      modalEl = null;
      wrapEl = null;
    },
    touchmoveOptions: { passive: false }
  });

  return {
    handleResizeMousedown: handleMousedown,
    handleResizeTouchstart: handleTouchstart
  };
}

/**
 * 弹窗事件处理
 * @param dialogRef 弹窗实例
 * @param props 属性
 * @param isFullscreen 全屏状态
 */
export function useModalEvent(
  dialogRef: Ref<ElDialogInstance>,
  props: ModalProps,
  isFullscreen: Ref<boolean>
) {
  const { handleHeaderMousedown, handleHeaderTouchstart } = useModalMove(
    dialogRef,
    props,
    isFullscreen
  );
  const { handleResizeMousedown, handleResizeTouchstart } = useModalResize(
    dialogRef,
    props,
    isFullscreen
  );

  // 是否还未设置过弹窗初始位置
  let isInitPosition = true;

  /** 获取弹窗默认层级 */
  const getZIndex = () => {
    return props.zIndex ?? 2000;
  };

  /** 置顶弹窗 */
  const topModal = (el?: HTMLElement) => {
    const modalEl = el ?? getModalEl(dialogRef);
    const overlayEl = modalEl?.parentElement?.parentElement;
    if (!overlayEl) {
      return;
    }
    const currentIndex = getCurrentStyle(overlayEl).zIndex;
    const containerEl = overlayEl.parentElement;
    const cls = `.${wrapperClass}:not(.${closedClass})`;
    const modals = containerEl ? containerEl.querySelectorAll(cls) : void 0;
    let maxIndex = 0;
    (modals ? Array.from(modals) : []).forEach((modalEl) => {
      const zIndex = getCurrentStyle(modalEl).zIndex;
      if (zIndex != null) {
        const index = Number(zIndex);
        if (index >= maxIndex && (!overlayEl || modalEl !== overlayEl)) {
          maxIndex = index + 1;
        }
      }
    });
    if (maxIndex > Number(currentIndex || getZIndex() || 0)) {
      overlayEl.style.zIndex = String(maxIndex);
    }
  };

  /** 弹窗点击自动置顶事件处理 */
  const mousedownListener = (event: MouseEvent) => {
    if (props.multiple) {
      topModal(event.currentTarget as HTMLElement);
    }
  };

  const bindAutoTopEvent = () => {
    const modalEl = getModalEl(dialogRef);
    if (modalEl) {
      modalEl.addEventListener('mousedown', mousedownListener);
      modalEl.addEventListener('touchstart', mousedownListener);
    }
  };

  const unbindAutoTopEvent = () => {
    const modalEl = getModalEl(dialogRef);
    if (modalEl) {
      modalEl.removeEventListener('mousedown', mousedownListener);
      modalEl.removeEventListener('touchstart', mousedownListener);
    }
  };

  /** 获取位置对应的外间距值 */
  const getPositionMargin = (position?: Position) => {
    if (
      position == null ||
      typeof position !== 'object' ||
      (position.top == null &&
        position.right == null &&
        position.bottom == null &&
        position.left == null)
    ) {
      return;
    }
    return [position.top, position.right, position.bottom, position.left]
      .map((p) => (typeof p === 'number' ? `${p}px` : p || 'auto'))
      .join(' ');
  };

  /** 获取弹窗初始位置 */
  const getPosition = (): Position | undefined => {
    if (props.alignCenter) {
      return 'center';
    }
    if (props.top != null && props.top !== '') {
      return { top: props.top };
    }
    return props.position;
  };

  /** 设置弹窗初始位置 */
  const setInitPosition = () => {
    if (isInitPosition) {
      isInitPosition = false;
      const modalEl = getModalEl(dialogRef);
      const margin = getPositionMargin(getPosition());
      if (modalEl && margin != null) {
        modalEl.style.margin = margin;
      }
    }
  };

  /** 重置弹窗位置及大小 */
  const resetModalStyle = () => {
    const modalEl = getModalEl(dialogRef);
    if (modalEl) {
      const w = props.width;
      modalEl.style.margin = getPositionMargin(getPosition()) || '';
      modalEl.style.position = '';
      modalEl.style.top = '';
      modalEl.style.left = '';
      modalEl.style.right = '';
      modalEl.style.bottom = '';
      modalEl.style.height = '';
      modalEl.style.maxHeight = '';
      modalEl.style.minHeight = '';
      modalEl.style.width = typeof w === 'number' ? `${w}px` : (w ?? '');
      modalEl.style.maxWidth = '';
      modalEl.style.minWidth = '';
    }
  };

  return {
    handleHeaderMousedown,
    handleHeaderTouchstart,
    handleResizeMousedown,
    handleResizeTouchstart,
    bindAutoTopEvent,
    unbindAutoTopEvent,
    topModal,
    setInitPosition,
    resetModalStyle
  };
}
