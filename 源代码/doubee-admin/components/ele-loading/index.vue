<!-- 加载组件 -->
<template>
  <ReceiverView
    v-loading="isCircle && loading"
    :element-loading-text="isCircle ? text : void 0"
    :element-loading-background="isCircle ? background : void 0"
    :element-loading-spinner="isCircle ? spinner : void 0"
    :element-loading-svg-view-box="isCircle ? svgViewBox : void 0"
    :class="['ele-loading', { 'ele-loading-show': loading }]"
  >
    <slot></slot>
    <div
      v-if="!isCircle"
      v-show="loading"
      :style="spinnerStyle"
      :class="[
        'ele-loading-spinner',
        { 'ele-loading-blur': blur },
        { 'ele-loading-small': size === 'small' },
        { 'ele-loading-large': size === 'large' }
      ]"
    >
      <slot name="spinner">
        <div class="ele-loading-dot">
          <i></i>
          <i></i>
          <i></i>
          <i></i>
        </div>
      </slot>
      <div v-if="text" class="ele-loading-text" :style="textStyle">
        {{ text }}
      </div>
    </div>
  </ReceiverView>
</template>

<script lang="ts" setup>
  import { computed } from 'vue';
  import { vLoading } from 'element-plus';
  import ReceiverView from '../ele-config-provider/components/receiver-view';
  import { loadingProps } from './props';

  defineOptions({ name: 'EleLoading' });

  const props = defineProps(loadingProps);

  /** 是否是圆形加载器 */
  const isCircle = computed(() => {
    return props.type === 'circle';
  });
</script>
