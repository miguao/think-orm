<!-- 侧栏 -->
<template>
  <div
    v-if="sideConfig?.searchProps !== false"
    class="ele-crud-search-bar"
    v-bind="searchBarProps"
  >
    <ElInput
      placeholder="搜索"
      :clearable="true"
      v-model="keywords"
      :prefixIcon="SearchOutlined"
      v-bind="sideConfig?.searchInputProps || {}"
    >
      <template
        v-for="(slotName, name) in getSlotsMap(
          $slots,
          sideConfig?.searchInputSlots
        )"
        #[name]="slotProps"
      >
        <slot :name="slotName" v-bind="slotProps || {}"></slot>
      </template>
    </ElInput>
  </div>
  <EleLoading
    :loading="loading"
    class="ele-crud-tree-wrapper"
    v-bind="sideConfig?.loadingProps || {}"
  >
    <ElTree
      ref="treeRef"
      :data="data"
      :highlightCurrent="true"
      :expandOnClickNode="false"
      :defaultExpandAll="true"
      :filterNodeMethod="filterNode"
      class="ele-crud-tree"
      :nodeKey="defaultTreeNodeKey"
      v-bind="sideConfig?.treeProps || {}"
      @node-click="handleNodeClick"
    >
      <template #empty>
        <ElEmpty
          :imageSize="68"
          :description="errorMessage"
          v-bind="sideConfig?.emptyProps || {}"
        />
      </template>
      <template
        v-for="(slotName, name) in getSlotsMap($slots, sideConfig?.treeSlots, [
          'empty'
        ])"
        #[name]="slotProps"
      >
        <slot :name="slotName" v-bind="slotProps || {}"></slot>
      </template>
    </ElTree>
  </EleLoading>
</template>

<script lang="ts" setup>
  import type { PropType } from 'vue';
  import { ref, computed, watch, onMounted } from 'vue';
  import { ElInput, ElTree, ElEmpty } from 'element-plus';
  import { SearchOutlined } from '../../icons/index';
  import type { ElTreeInstance } from '../../ele-app/el';
  import { getSlotsMap } from '../../utils/common';
  import EleLoading from '../../ele-loading/index.vue';
  import {
    defaultTreeNodeKey,
    getTreeValueField,
    getTreeLabelField
  } from '../util';
  import type { SideConfig } from '../types';

  defineOptions({ name: 'PageSide' });

  const props = defineProps({
    /** 侧栏配置 */
    sideConfig: Object as PropType<SideConfig>,
    /** 数据 */
    data: Array as PropType<Record<string, any>[]>,
    /** 加载状态 */
    loading: Boolean,
    /** 加载错误信息 */
    errorMessage: String,
    /** 选中值 */
    selectedValue: [String, Number, Boolean, Object, Array] as PropType<any>
  });

  const emit = defineEmits({
    /** 侧栏树点击事件 */
    treeNodeClick: (_nodeValue?: any) => true
  });

  /** 搜索关键字 */
  const keywords = ref('');

  /** 树组件 */
  const treeRef = ref<ElTreeInstance>(null);

  /** 搜索栏属性 */
  const searchBarProps = computed(() => {
    const searchProps = props.sideConfig?.searchProps;
    if (!searchProps || searchProps === true) {
      return {};
    }
    return {
      class: searchProps.class,
      style: searchProps.style
    };
  });

  /** 设置树选中 */
  const setTreeCurrentKey = (key: any) => {
    if (key != null) {
      try {
        treeRef.value?.setCurrentKey?.(key);
      } catch (e) {
        console.error(e);
      }
    }
  };

  /** 选择数据 */
  const handleNodeClick = (item?: Record<string, any>) => {
    if (item != null) {
      emit('treeNodeClick', item[getTreeValueField(props.sideConfig)]);
    }
  };

  /** 树过滤方法 */
  const filterNode = (value: string, item: Record<string, any>) => {
    if (value) {
      const itemLabel = item[getTreeLabelField(props.sideConfig)];
      return !!(itemLabel && itemLabel.includes(value));
    }
    return true;
  };

  /** 树过滤 */
  watch(keywords, (value) => {
    treeRef.value?.filter?.(value);
  });

  /** 更新选中 */
  watch(
    () => props.selectedValue,
    (selected) => {
      setTreeCurrentKey(selected);
    }
  );

  /** 初始选中 */
  onMounted(() => {
    setTreeCurrentKey(props.selectedValue);
  });
</script>
