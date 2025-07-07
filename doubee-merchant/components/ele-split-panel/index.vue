<!-- 分割面板 -->
<template>
  <ReceiverView
    ref="rootRef"
    :class="[
      'ele-split-panel',
      { 'is-reverse': reverse },
      { 'is-vertical': vertical },
      { 'is-collapse': isCollapse },
      { 'is-resizing': resizing },
      { 'is-responsive': isResponsive },
      { 'is-flex-table': flexTable }
    ]"
    :style="{
      '--ele-split-size': resizedSize || size,
      '--ele-split-space': space
    }"
  >
    <!-- 侧边容器 -->
    <div ref="wrapRef" class="ele-split-panel-wrap" :style="customWrapStyle">
      <div ref="sideRef" class="ele-split-panel-side" :style="customStyle">
        <slot></slot>
      </div>
      <!-- 间距 -->
      <div class="ele-split-panel-space">
        <div
          v-if="resizable"
          class="ele-split-resize-line"
          @mousedown="handleResize"
        ></div>
      </div>
    </div>
    <!-- 内容 -->
    <div class="ele-split-panel-body" :style="bodyStyle">
      <slot name="body" :collapse="isCollapse"></slot>
    </div>
    <!-- 折叠按钮 -->
    <div
      v-if="allowCollapse"
      :style="collapseStyle"
      class="ele-split-collapse-button"
      @click="toggleCollapse()"
    >
      <slot name="collapse" :collapse="isCollapse">
        <ElIcon class="ele-split-collapse-icon">
          <ArrowUp v-if="vertical" style="margin-top: -1.25px" />
          <ArrowLeft v-else style="margin-left: -1.25px" />
        </ElIcon>
      </slot>
    </div>
    <!-- 小屏幕遮罩层 -->
    <div class="ele-split-panel-mask" @click="toggleCollapse()"></div>
  </ReceiverView>
</template>

<script lang="ts" setup>
  import { ref, watch } from 'vue';
  import { ElIcon } from 'element-plus';
  import { ArrowLeft, ArrowUp } from '../icons/index';
  import type { ReceiverViewInstance } from '../ele-app/plus';
  import ReceiverView from '../ele-config-provider/components/receiver-view';
  import { useResponsive } from '../ele-pro-layout/util';
  import { splitPanelProps, splitPanelEmits } from './props';

  defineOptions({ name: 'EleSplitPanel' });

  const props = defineProps(splitPanelProps);

  const emit = defineEmits(splitPanelEmits);

  /** 是否开启布局响应 */
  const isResponsive = useResponsive(props);

  /** 根节点 */
  const rootRef = ref<ReceiverViewInstance>(null);

  /** 侧边容器节点 */
  const wrapRef = ref<HTMLElement | null>(null);

  /** 侧边节点 */
  const sideRef = ref<HTMLElement | null>(null);

  /** 是否折叠 */
  const isCollapse = ref<boolean>(false);

  /** 拉伸后尺寸 */
  const resizedSize = ref<string | null>(null);

  /** 是否正在拉伸 */
  const resizing = ref<boolean>(false);

  /** 切换折叠状态 */
  const toggleCollapse = (collapse?: boolean) => {
    isCollapse.value =
      typeof collapse === 'boolean' ? collapse : !isCollapse.value;
    emit('update:collapse', isCollapse.value);
  };

  /** 重置拉伸尺寸 */
  const resetSize = () => {
    resizedSize.value = null;
  };

  /** 获取最大拉伸尺寸 */
  const getMaxSize = (el: HTMLElement) => {
    const size = props.vertical ? el.clientHeight : el.clientWidth;
    if (!props.maxSize) {
      return size;
    }
    if (props.maxSize < 0) {
      // 负值形式
      return size + props.maxSize;
    } else if (props.maxSize < 1) {
      // 百分比形式
      return Math.floor(size * props.maxSize);
    }
    return Math.min(props.maxSize, size);
  };

  /** 拉伸 */
  const handleResize = (event: MouseEvent) => {
    const rootEl = rootRef.value?.$el;
    const sideEl = sideRef.value;
    if (!rootEl || !sideEl) {
      return;
    }
    resizing.value = true;
    // 获取原始位置
    const downX = event.clientX;
    const downY = event.clientY;
    const downW = sideEl.clientWidth;
    const downH = sideEl.clientHeight;
    const min = !props.minSize || props.minSize < 0 ? 0 : props.minSize;
    const max = getMaxSize(rootEl);

    // 鼠标移动事件
    const mousemoveFn = (e: MouseEvent) => {
      const size = props.vertical
        ? (props.reverse ? downY - e.clientY : e.clientY - downY) + downH
        : (props.reverse ? downX - e.clientX : e.clientX - downX) + downW;
      resizedSize.value = `${size < min ? min : size > max ? max : size}px`;
    };

    // 鼠标抬起事件
    const mouseupFn = () => {
      resizing.value = false;
      document.removeEventListener('mousemove', mousemoveFn);
      document.removeEventListener('mouseup', mouseupFn);
    };

    // 添加鼠标事件监听
    document.addEventListener('mousemove', mousemoveFn);
    document.addEventListener('mouseup', mouseupFn);
  };

  watch(
    [() => props.collapse, () => props.allowCollapse],
    () => {
      if (!props.allowCollapse) {
        isCollapse.value = false;
      } else {
        isCollapse.value = props.collapse;
      }
    },
    { immediate: true }
  );

  watch(
    () => props.size,
    () => {
      resetSize();
    }
  );

  watch(
    () => props.minSize,
    (minSize) => {
      const min = !minSize || minSize < 0 ? 0 : minSize;
      if (resizedSize.value && Number.parseInt(resizedSize.value) < min) {
        resizedSize.value = min + 'px';
      }
    }
  );

  watch(
    () => props.maxSize,
    () => {
      const rootEl = rootRef.value?.$el;
      if (resizedSize.value && rootEl) {
        const max = getMaxSize(rootEl);
        if (Number.parseInt(resizedSize.value) > max) {
          resizedSize.value = max + 'px';
        }
      }
    }
  );

  defineExpose({
    toggleCollapse,
    resetSize
  });
</script>
