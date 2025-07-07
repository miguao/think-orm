<!-- 弹窗 -->
<template>
  <Teleport :to="teleportTo" :disabled="teleportDisabled">
    <ElDialog
      v-bind="$attrs"
      ref="dialogRef"
      :modelValue="modelValue"
      :title="title"
      :width="width"
      :fullscreen="false"
      :modal="multiple ? false : modal"
      :modalClass="dialogClass"
      :appendToBody="false"
      :lockScroll="inner || multiple ? false : lockScroll"
      :openDelay="openDelay"
      :closeDelay="closeDelay"
      :closeOnClickModal="closeOnClickModal"
      :closeOnPressEscape="closeOnPressEscape"
      :showClose="false"
      :beforeClose="beforeClose"
      :draggable="false"
      :overflow="false"
      :center="center"
      :alignCenter="false"
      :destroyOnClose="destroyOnClose"
      :zIndex="zIndex"
      :headerAriaLevel="headerAriaLevel"
      @update:modelValue="updateModelValue"
      @open="handleOpen"
      @opened="handleOpened"
      @close="handleClose"
      @closed="handleClosed"
      @openAutoFocus="handleOpenAutoFocus"
      @closeAutoFocus="handleCloseAutoFocus"
    >
      <template #header="{ close, titleId, titleClass }">
        <div
          :style="headerStyle"
          class="ele-modal-header"
          @mousedown="handleHeaderMousedown"
          @touchstart="handleHeaderTouchstart"
        >
          <div class="ele-modal-title" :style="titleStyle">
            <slot
              name="header"
              :close="close"
              :titleId="titleId"
              :titleClass="titleClass"
            >
              {{ title }}
            </slot>
          </div>
          <!-- 最大化图标 -->
          <div
            v-if="maxable"
            class="ele-modal-tool ele-modal-tool-max"
            :style="fullscreenBtnStyle"
            @click="toggleFullscreen()"
            @mousedown.stop=""
            @touchstart.stop=""
          >
            <slot name="maxIcon" :fullscreen="isFullscreen">
              <ElIcon>
                <CompressOutlined v-if="isFullscreen" />
                <ExpandOutlined v-else />
              </ElIcon>
            </slot>
          </div>
          <!-- 关闭图标 -->
          <div
            v-if="showClose"
            class="ele-modal-tool"
            :style="closeBtnStyle"
            @click="updateModelValue(false)"
            @mousedown.stop=""
            @touchstart.stop=""
          >
            <slot name="closeIcon">
              <ElIcon>
                <component v-if="closeIcon" :is="closeIcon" />
                <CloseOutlined v-else />
              </ElIcon>
            </slot>
          </div>
        </div>
        <!-- 拉伸图标 -->
        <div
          v-if="resizable"
          :class="[
            'ele-modal-resize-icon',
            { 'is-horizontal': resizable === 'horizontal' },
            { 'is-vertical': resizable === 'vertical' }
          ]"
          :style="resizeIconStyle"
          @mousedown="handleResizeMousedown"
          @touchstart="handleResizeTouchstart"
        >
          <slot name="resizeIcon">
            <ElIcon>
              <ResizeOutlined />
            </ElIcon>
          </slot>
        </div>
      </template>
      <template v-if="$slots.footer" #footer>
        <div class="ele-modal-footer" :style="footerStyle">
          <slot name="footer"></slot>
        </div>
      </template>
      <ReceiverView
        :wrapPosition="false"
        :class="['ele-modal-body', { 'is-form': form }, modalBodyClass]"
        :style="bodyStyle"
      >
        <slot></slot>
      </ReceiverView>
    </ElDialog>
  </Teleport>
</template>

<script lang="ts" setup>
  import {
    ref,
    computed,
    onMounted,
    onActivated,
    onDeactivated,
    nextTick,
    watch
  } from 'vue';
  import { ElDialog, ElIcon } from 'element-plus';
  import {
    CloseOutlined,
    CompressOutlined,
    ExpandOutlined,
    ResizeOutlined
  } from '../icons/index';
  import type { ElDialogInstance } from '../ele-app/el';
  import ReceiverView from '../ele-config-provider/components/receiver-view';
  import { useLayoutState, useResponsive } from '../ele-pro-layout/util';
  import {
    wrapperClass,
    closedClass,
    getModalContainer,
    useModalEvent
  } from './util';
  import { modalEmits, modalProps } from './props';

  defineOptions({ name: 'EleModal', inheritAttrs: false });

  const props = defineProps(modalProps);

  const emit = defineEmits(modalEmits);

  const layoutState = useLayoutState();
  const isResponsive = useResponsive(props);

  /** 弹窗组件 */
  const dialogRef = ref<ElDialogInstance>(null);

  /** 弹窗是否全屏 */
  const isFullscreen = ref<boolean>(props.fullscreen ?? false);

  /** 适配组件缓存 */
  const isActivated = ref<boolean>(true);

  /** 弹窗类名 */
  const dialogClass = computed<string>(() => {
    const classes: string[] = [wrapperClass];
    // 开启布局响应
    if (props.responsive ?? isResponsive.value ?? true) {
      classes.push('ele-modal-responsive');
    }
    // 初始位置
    if (props.alignCenter || props.position === 'center') {
      classes.push('ele-modal-center');
    } else if (props.position === 'top') {
      classes.push('ele-modal-top');
    } else if (props.position === 'bottom') {
      classes.push('ele-modal-bottom');
    } else if (props.position === 'left') {
      classes.push('ele-modal-left');
    } else if (props.position === 'right') {
      classes.push('ele-modal-right');
    } else if (props.position === 'leftTop') {
      classes.push('ele-modal-left-top');
    } else if (props.position === 'leftBottom') {
      classes.push('ele-modal-left-bottom');
    } else if (props.position === 'rightTop') {
      classes.push('ele-modal-right-top');
    } else if (props.position === 'rightBottom') {
      classes.push('ele-modal-right-bottom');
    }
    // 支持拖动
    if (props.draggable) {
      classes.push('ele-modal-movable');
    }
    // 支持拉伸
    if (props.resizable) {
      classes.push('ele-modal-resizable');
    }
    // 支持打开多个
    if (props.multiple) {
      classes.push('ele-modal-multiple');
    }
    // 全屏
    if (isFullscreen.value) {
      classes.push('ele-modal-fullscreen');
    }
    // 关闭状态
    if (!props.modelValue) {
      classes.push(closedClass);
    }
    // 失活状态
    if (!isActivated.value && props.modelValue) {
      classes.push('ele-modal-hide');
    }
    // 限制在内部区域
    if (props.inner) {
      classes.push('ele-modal-inner');
    }
    // 自定义类名
    if (props.modalClass) {
      classes.push(props.modalClass);
    }
    return classes.join(' ');
  });

  /** 弹窗插入位置 */
  const teleportTo = computed<Element | string>(() => {
    return getModalContainer(
      props.inner,
      props.multiple,
      props.appendTo,
      layoutState.modalsEl
    );
  });

  /** 禁用弹窗插入其它位置 */
  const teleportDisabled = computed<boolean>(() => {
    const appendTo = props.appendTo || 'body';
    const disabled = appendTo === 'body' ? !props.appendToBody : false;
    return props.multiple || props.inner ? false : disabled;
  });

  /** 弹窗支持拖拽拉伸点击置顶 */
  const {
    handleHeaderMousedown,
    handleHeaderTouchstart,
    handleResizeMousedown,
    handleResizeTouchstart,
    bindAutoTopEvent,
    unbindAutoTopEvent,
    topModal,
    setInitPosition,
    resetModalStyle
  } = useModalEvent(dialogRef, props, isFullscreen);

  /** 更新显示状态 */
  const updateModelValue = (modelValue: boolean) => {
    emit('update:modelValue', modelValue);
  };

  /** 弹窗全屏切换 */
  const toggleFullscreen = (fullscreen?: boolean) => {
    isFullscreen.value = fullscreen ?? !isFullscreen.value;
    nextTick(() => {
      topModal();
    });
    emit('update:fullscreen', isFullscreen.value);
  };

  /** 打开的回调 */
  const handleOpen = () => {
    if (props.resetOnClose || props.destroyOnClose) {
      isFullscreen.value = props.fullscreen ?? false;
    }
    nextTick(() => {
      if (props.resetOnClose) {
        resetModalStyle();
      } else {
        setInitPosition();
      }
      topModal();
    });
    emit('open');
  };

  /** 打开动画结束的回调 */
  const handleOpened = () => {
    bindAutoTopEvent();
    emit('opened');
  };

  /** 关闭的回调 */
  const handleClose = () => {
    unbindAutoTopEvent();
    emit('close');
  };

  /** 关闭动画结束的回调 */
  const handleClosed = () => {
    emit('closed');
  };

  /** 内容获取焦点的回调 */
  const handleOpenAutoFocus = () => {
    emit('openAutoFocus');
  };

  /** 内容失去焦点的回调 */
  const handleCloseAutoFocus = () => {
    emit('closeAutoFocus');
  };

  /** 重置弹窗位置 */
  const resetPosition = () => {
    resetModalStyle();
  };

  /** 关闭弹窗 */
  const closeModal = () => {
    dialogRef.value && dialogRef.value.handleClose();
  };

  watch(
    () => props.fullscreen,
    (fullscreen) => {
      isFullscreen.value = fullscreen ?? false;
    }
  );

  onMounted(() => {
    if (props.modelValue) {
      setInitPosition();
    }
  });

  onActivated(() => {
    isActivated.value = true;
  });

  onDeactivated(() => {
    isActivated.value = false;
  });

  defineExpose({
    dialogRef,
    resetPosition,
    handleClose: closeModal
  });
</script>
