<!-- 骨架 -->
<template>
  <div :class="['ele-admin-layout', { 'is-row-direction': !isHeaderLogo }]">
    <slot v-if="isHeaderLogo" name="head"></slot>
    <slot v-else name="side"></slot>
    <ReceiverView
      :class="['ele-admin-main', { 'is-row-direction': isHeaderLogo }]"
      :wrapPosition="false"
    >
      <slot v-if="isHeaderLogo" name="side"></slot>
      <slot v-else name="head"></slot>
      <div class="ele-admin-body">
        <slot name="tabs"></slot>
        <div class="ele-admin-wrapper">
          <slot name="body"></slot>
        </div>
      </div>
    </ReceiverView>
    <slot></slot>
  </div>
</template>

<script lang="ts" setup>
  import ReceiverView from '../../ele-config-provider/components/receiver-view';

  defineOptions({ name: 'LayoutSkeleton' });

  defineProps({
    /** logo是否位于顶栏 */
    isHeaderLogo: Boolean
  });
</script>
