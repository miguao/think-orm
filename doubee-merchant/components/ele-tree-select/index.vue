<!-- 虚拟滚动树下拉选择 -->
<template>
  <EleBasicSelect
    ref="selectRef"
    :value="modelValue"
    :multiple="multiple"
    :disabled="disabled"
    :size="size"
    :clearable="clearable"
    :placeholder="placeholder"
    :maxTagCount="maxTagCount"
    :maxTagTextLength="maxTagTextLength"
    :tagType="tagType"
    :automaticDropdown="automaticDropdown"
    :filterable="filterable"
    :teleported="teleported"
    :persistent="persistent"
    :placement="placement"
    :transition="transition"
    :popperWidth="popperWidth"
    :popperOptions="popperOptions"
    :popperClass="selectPopperClass"
    :selectStyle="selectStyle"
    :inputStyle="inputStyle"
    :selectTagsStyle="selectTagsStyle"
    :selectedLabel="selectedLabel"
    :selected="selectedItems.filter((d) => !d.hide)"
    :visible="selectVisible"
    @update:visible="updateVisible"
    @filterChange="handleSelectFilter"
    @removeTag="handleSelectRemove"
    @clear="handleSelectClear"
    @focus="handleSelectFocus"
    @blur="handleSelectBlur"
  >
    <ElTreeV2
      :itemSize="32"
      :filterMethod="treeFilter"
      v-bind="omit(treeProps, ['data'])"
      ref="treeRef"
      :data="optionData"
      :props="treeOptions"
      :highlightCurrent="multiple ? false : true"
      :showCheckbox="!!multiple"
      :checkOnClickNode="false"
      :defaultCheckedKeys="multiple ? (modelValue as any) : void 0"
      @check="handleTreeCheck"
      @node-click="handleTreeClick"
      @node-expand="handleTreeExpand"
      @node-collapse="handleTreeCollapse"
    >
      <template #default="slotProps">
        <span
          :class="[
            'el-tree-node__label',
            { 'is-disabled': slotProps.node.disabled }
          ]"
          :title="slotProps.node.label"
        >
          <slot v-bind="slotProps || {}">{{ slotProps.node.label }}</slot>
        </span>
      </template>
      <template v-if="$slots.empty" #empty="slotProps">
        <slot name="empty" v-bind="slotProps || {}"></slot>
      </template>
    </ElTreeV2>
    <template v-if="$slots.maxTagPlaceholder" #maxTagPlaceholder="slotProps">
      <slot name="maxTagPlaceholder" v-bind="slotProps || {}"></slot>
    </template>
  </EleBasicSelect>
</template>

<script lang="ts" setup>
  import { ref, computed, watch, nextTick, onMounted } from 'vue';
  import { ElTreeV2 } from 'element-plus';
  import { findTree, omit } from '../utils/common';
  import { useProOptions } from '../utils/hook';
  import type { ElTreeV2Instance } from '../ele-app/el';
  import type { EleBasicSelectInstance } from '../ele-app/plus';
  import EleBasicSelect from '../ele-basic-select/index.vue';
  import type {
    SelectValue,
    SingleValue,
    MultipleValue,
    SelectedItem
  } from '../ele-basic-select/types';
  import {
    isEmptyValue,
    valueIsChanged,
    useFormValidate
  } from '../ele-basic-select/util';
  import {
    getNormalSelectedItems,
    getTreeSelectedItems,
    checkSelectedItems,
    isCheckAll,
    getModelValue
  } from './util';
  import type { TreeOption, DataItem } from './types';
  import { treeSelectProps, treeSelectEmits } from './props';

  defineOptions({ name: 'EleTreeSelect' });

  const props = defineProps(treeSelectProps);

  const emit = defineEmits(treeSelectEmits as any);

  const { validateChange } = useFormValidate();
  const { optionData, reloadOptions } = useProOptions<DataItem>(
    props,
    'treeProps.data'
  );

  /** 下拉组件 */
  const selectRef = ref<EleBasicSelectInstance>(null);

  /** 树组件 */
  const treeRef = ref<ElTreeV2Instance>(null);

  /** 下拉框是否显示 */
  const selectVisible = ref<boolean>(false);

  /** 选中的标签 */
  const selectedItems = ref<SelectedItem[]>([]);

  /** 单选选中显示文本 */
  const selectedLabel = computed<string>(() => {
    const selected = selectedItems.value;
    return !props.multiple && selected.length ? selected[0].label : '';
  });

  /** 下拉框类名 */
  const selectPopperClass = computed<string>(() => {
    const classes: string[] = ['ele-tree-select-popper'];
    if (props.popperClass) {
      classes.push(props.popperClass);
    }
    return classes.join(' ');
  });

  /** 树配置选项 */
  const treeOptions = computed<TreeOption>(() => {
    return Object.assign(
      {
        value: 'id',
        label: 'label',
        children: 'children',
        disabled: 'disabled'
      },
      props.treeProps?.props
    ) as any;
  });

  /** 更新气泡位置 */
  const updatePopover = () => {
    selectRef.value && selectRef.value.updatePopper();
  };

  /** 获取值对应的数据 */
  const getItemByValue = (value?: SingleValue): DataItem | undefined => {
    if (isEmptyValue(value)) {
      return;
    }
    const valueKey = treeOptions.value.value;
    if (props.cacheData != null) {
      const temp = props.cacheData.find((d) => d[valueKey] === value);
      if (temp != null) {
        return temp;
      }
    }
    if (optionData.value) {
      return findTree(
        optionData.value,
        (d) => d[valueKey] === value,
        treeOptions.value.children
      );
    }
  };

  /** 获取多选的选中标签 */
  const getMultipleSelected = (values?: MultipleValue | null) => {
    const valueKey = treeOptions.value.value;
    const labelKey = treeOptions.value.label;
    if (
      // 是懒加载 ||
      props.treeProps?.checkStrictly ||
      props.showCheckedStrategy === 'all'
    ) {
      return getNormalSelectedItems(
        optionData.value,
        values,
        valueKey,
        labelKey,
        treeOptions.value.children,
        props.cacheData,
        treeRef.value ? treeRef.value.getCheckedNodes() : void 0
      );
    }
    return checkSelectedItems(
      getTreeSelectedItems(
        optionData.value,
        values,
        valueKey,
        labelKey,
        treeOptions.value.children,
        props.showCheckedStrategy !== 'child'
      ),
      values,
      valueKey,
      labelKey,
      props.cacheData
    );
  };

  /** 更新选中标签 */
  const updateSelectedItems = (modelValue: SelectValue) => {
    // 单选模式
    if (!props.multiple) {
      const selectedItem = selectedItems.value[0];
      if (isEmptyValue(modelValue)) {
        if (!selectedItem) {
          return;
        }
        selectedItems.value = [];
        treeRef.value && treeRef.value.setCurrentKey(null as any);
        return;
      }
      if (selectedItem && selectedItem.value === modelValue) {
        return;
      }
      const item = getItemByValue(modelValue as SingleValue);
      const labelKey = treeOptions.value.label;
      const label = item ? item[labelKey] : String(modelValue);
      selectedItems.value = [{ label, value: modelValue as SingleValue }];
      treeRef.value && treeRef.value.setCurrentKey(modelValue as any);
      return;
    }
    // 多选模式
    if (isEmptyValue(modelValue) || !(modelValue as MultipleValue).length) {
      if (!selectedItems.value.length) {
        return;
      }
      selectedItems.value = [];
      treeRef.value && treeRef.value.setCheckedKeys([]);
      return;
    }
    const keys = selectedItems.value.map((d) => d.value);
    if (!valueIsChanged(modelValue, keys, true)) {
      return;
    }
    treeRef.value && treeRef.value.setCheckedKeys(modelValue as any);
    selectedItems.value = getMultipleSelected(modelValue as any);
  };

  /** 树筛选 */
  const treeFilter = (keywords: string, item: DataItem) => {
    const label = item[treeOptions.value.label];
    return label != null && label.includes(keywords);
  };

  /** 筛选关键字改变事件 */
  const handleSelectFilter = (keywords: string) => {
    treeRef.value && treeRef.value.filter(keywords);
  };

  /** 让多选搜索框获取焦点 */
  const focusSearchInput = () => {
    selectRef.value && selectRef.value.focusSearchInput();
  };

  /** 更新选中值 */
  const updateModelValue = (modelValue: SelectValue) => {
    if (valueIsChanged(modelValue, props.modelValue, props.multiple)) {
      emit('update:modelValue', modelValue);
      validateChange();
      emit('change', modelValue);
    }
  };

  /** 更新下拉框显示状态 */
  const updateVisible = (visible: boolean) => {
    if (visible) {
      handleSelectFilter('');
    }
    if (selectVisible.value !== visible) {
      selectVisible.value = visible;
      emit('visibleChange', visible);
    }
  };

  /** 删除多选标签 */
  const handleSelectRemove = (item: SelectedItem) => {
    treeRef.value && treeRef.value.setChecked(item.value as any, false);
    const keys = (treeRef.value && treeRef.value.getCheckedKeys()) || [];
    selectedItems.value = getMultipleSelected(keys);
    updateModelValue(
      getModelValue(keys, selectedItems.value, props.checkedValueStrategy)
    );
    emit('removeTag', item.value);
  };

  /** 清空 */
  const handleSelectClear = () => {
    updateModelValue(props.multiple ? [] : null);
    updateVisible(false);
    emit('clear');
  };

  /** 获取焦点事件 */
  const handleSelectFocus = (e: FocusEvent) => {
    emit('focus', e);
  };

  /** 失去焦点事件 */
  const handleSelectBlur = (e: FocusEvent) => {
    emit('blur', e);
  };

  /** 树节点点击事件 */
  const handleTreeClick = (item: DataItem, _node: any, e: MouseEvent) => {
    e.stopPropagation();
    const valueKey = treeOptions.value.value;
    const childrenKey = treeOptions.value.children;
    const disabledKey = treeOptions.value.disabled;
    const disabled: boolean = item[disabledKey];
    if (disabled) {
      const target = e.target as HTMLElement;
      if (target && target.classList.contains('is-disabled')) {
        const el = target.parentNode?.parentNode as HTMLElement;
        el && el.blur();
      }
    }
    const value: SingleValue = item[valueKey];
    const label = item[treeOptions.value.label];
    const isChild = !item[childrenKey]?.length;
    const isExpandOnClick = props.treeProps?.expandOnClickNode !== false;
    focusSearchInput();
    // 单选模式
    if (!props.multiple) {
      const selectedItem = selectedItems.value[0];
      if (selectedItem && selectedItem.value === value) {
        if (!isExpandOnClick || isChild) {
          updateVisible(false);
        }
        return;
      }
      if (!disabled && (!isExpandOnClick || isChild)) {
        selectedItems.value = [{ label, value }];
        updateModelValue(value);
        updateVisible(false);
        return;
      }
      nextTick(() => {
        const modelValue = props.modelValue as SingleValue;
        const key = isEmptyValue(modelValue) ? null : modelValue;
        treeRef.value && treeRef.value.setCurrentKey(key as any);
      });
      return;
    }
    // 多选模式
    if (!disabled && (!isExpandOnClick || isChild)) {
      const modelValue = props.modelValue as MultipleValue | undefined;
      const checked = modelValue && modelValue.includes(value);
      if (treeRef.value) {
        if (checked) {
          treeRef.value.setChecked(value as any, false);
        } else if (isChild) {
          treeRef.value.setChecked(value as any, !checked);
        } else {
          const checkAll = isCheckAll(
            item[childrenKey],
            modelValue,
            valueKey,
            childrenKey,
            disabledKey
          );
          treeRef.value.setChecked(value as any, !checkAll);
        }
      }
      const keys = (treeRef.value && treeRef.value.getCheckedKeys()) || [];
      selectedItems.value = getMultipleSelected(keys);
      updateModelValue(
        getModelValue(keys, selectedItems.value, props.checkedValueStrategy)
      );
    }
  };

  /** 树复选框点击事件 */
  const handleTreeCheck = () => {
    const keys = (treeRef.value && treeRef.value.getCheckedKeys()) || [];
    selectedItems.value = getMultipleSelected(keys);
    updateModelValue(
      getModelValue(keys, selectedItems.value, props.checkedValueStrategy)
    );
    focusSearchInput();
  };

  /** 树节点展开事件 */
  const handleTreeExpand = () => {
    focusSearchInput();
  };

  /** 树节点收起事件 */
  const handleTreeCollapse = () => {
    focusSearchInput();
  };

  watch(
    () => props.modelValue,
    (modelValue) => {
      //if (!props.multiple || !props.checkedValueStrategy) {
      updateSelectedItems(modelValue);
      /* } else {
            const keys =
              (treeRef.value && treeRef.value.getCheckedKeys()) || [];
            updateSelectedItems(keys);
          } */
    },
    { deep: true }
  );

  watch(
    [() => props.cacheData, optionData],
    () => {
      if (!selectedItems.value.length) {
        return;
      }
      // 单选模式
      if (!props.multiple) {
        const item = getItemByValue(props.modelValue as SingleValue);
        const value = props.modelValue as SingleValue;
        const label = item ? item[treeOptions.value.label] : String(value);
        selectedItems.value = [{ label, value }];
        return;
      }
      // 多选模式
      selectedItems.value = getMultipleSelected(props.modelValue as any);
    },
    { deep: true }
  );

  watch(
    [() => props.showCheckedStrategy, () => props.treeProps?.checkStrictly],
    () => {
      if (
        props.multiple &&
        !isEmptyValue(props.modelValue) &&
        (props.modelValue as MultipleValue).length
      ) {
        if (!props.checkedValueStrategy) {
          const values = props.modelValue as any;
          selectedItems.value = getMultipleSelected(values);
          updateModelValue(
            getModelValue(
              values,
              selectedItems.value,
              props.checkedValueStrategy
            )
          );
        } else {
          const keys = (treeRef.value && treeRef.value.getCheckedKeys()) || [];
          selectedItems.value = getMultipleSelected(keys);
          updateModelValue(
            getModelValue(keys, selectedItems.value, props.checkedValueStrategy)
          );
        }
      }
    }
  );

  watch(
    () => props.checkedValueStrategy,
    () => {
      if (!props.multiple) {
        return;
      }
      const keys = (treeRef.value && treeRef.value.getCheckedKeys()) || [];
      updateModelValue(
        getModelValue(keys, selectedItems.value, props.checkedValueStrategy)
      );
    }
  );

  watch(
    () => props.disabled,
    (disabled) => {
      if (disabled) {
        updateVisible(false);
      }
    }
  );

  onMounted(() => {
    if (
      !isEmptyValue(props.modelValue) &&
      !(props.multiple && !(props.modelValue as any).length)
    ) {
      updateSelectedItems(props.modelValue);
    }
  });

  defineExpose({
    reloadOptions,
    selectRef,
    treeRef,
    selectedItems,
    selectedLabel,
    updatePopover,
    updateVisible
  });
</script>
