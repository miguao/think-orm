<!-- 表格下拉选择 -->
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
    :selected="selectedItems"
    :visible="selectVisible"
    @update:visible="updateVisible"
    @filterChange="handleSelectFilter"
    @removeTag="handleSelectRemove"
    @clear="handleSelectClear"
    @focus="handleSelectFocus"
    @blur="handleSelectBlur"
  >
    <slot name="popperTopExtra"></slot>
    <EleProTable
      @update:selectedRowKeys="updateSelectedRowKeys"
      @update:currentRowKey="updateCurrentRowKey"
      @selectionChange="handleTableSelectionChange"
      @currentChange="handleTableCurrentChange"
      @rowClick="handleTableRowClick"
      @select="handleTableSelect"
      @selectAll="handleTableSelectAll"
      @done="handleTableDone"
      v-bind="tableProps || {}"
      ref="tableRef"
      :rowKey="valueKey"
      :reserveCurrent="true"
      :highlightCurrentRow="!multiple"
      :currentRowKey="currentRowKey"
      :selectedRowKeys="selectedRowKeys"
    >
      <template
        v-for="name in Object.keys($slots).filter(
          (k) =>
            ![
              'popperTopExtra',
              'popperBottomExtra',
              'maxTagPlaceholder'
            ].includes(k)
        )"
        #[name]="slotProps"
      >
        <slot :name="name" v-bind="slotProps || {}"></slot>
      </template>
    </EleProTable>
    <slot name="popperBottomExtra"></slot>
    <template v-if="$slots.maxTagPlaceholder" #maxTagPlaceholder="slotProps">
      <slot name="maxTagPlaceholder" v-bind="slotProps || {}"></slot>
    </template>
  </EleBasicSelect>
</template>

<script lang="ts" setup>
  import { ref, computed, watch, nextTick, onMounted } from 'vue';
  import type {
    EleBasicSelectInstance,
    EleProTableInstance
  } from '../ele-app/plus';
  import { useResponsive } from '../ele-pro-layout/util';
  import EleBasicSelect from '../ele-basic-select/index.vue';
  import type {
    SingleValue,
    MultipleValue,
    SelectValue,
    SelectedItem
  } from '../ele-basic-select/types';
  import {
    isEmptyValue,
    valueIsChanged,
    useFormValidate
  } from '../ele-basic-select/util';
  import EleProTable from '../ele-pro-table/index.vue';
  import { isDisableRow } from '../ele-data-table/util';
  import type { DataKey, DataItem } from '../ele-data-table/types';
  import { tableSelectProps, tableSelectEmits } from './props';

  defineOptions({ name: 'EleTableSelect' });

  const props = defineProps(tableSelectProps as any);

  const emit = defineEmits(tableSelectEmits as any);

  const { validateChange } = useFormValidate();

  const isResponsive = useResponsive(props);

  /** 下拉选择组件 */
  const selectRef = ref<EleBasicSelectInstance>(null);

  /** 表格组件 */
  const tableRef = ref<EleProTableInstance>(null);

  /** 下拉框是否显示 */
  const selectVisible = ref<boolean>(false);

  /** 选中的标签 */
  const selectedItems = ref<SelectedItem[]>([]);

  /** 单选选中显示文本 */
  const selectedLabel = computed<string>(() => {
    const selected = selectedItems.value;
    return !props.multiple && selected.length ? selected[0].label : '';
  });

  /** 表格单选选中值 */
  const currentRowKey = computed<DataKey | undefined>(() => {
    if (props.multiple || isEmptyValue(props.modelValue)) {
      return;
    }
    return props.modelValue as DataKey;
  });

  /** 表格多选选中值 */
  const selectedRowKeys = computed<DataKey[] | undefined>(() => {
    if (!props.multiple || isEmptyValue(props.modelValue)) {
      return;
    }
    return props.modelValue as DataKey[];
  });

  /** 下拉框类名 */
  const selectPopperClass = computed<string>(() => {
    const classes: string[] = ['ele-table-select-popper'];
    if (isResponsive.value) {
      classes.push('is-responsive');
    }
    if (props.popperClass) {
      classes.push(props.popperClass);
    }
    return classes.join(' ');
  });

  /** 更新气泡位置 */
  const updatePopover = () => {
    selectRef.value && selectRef.value.updatePopper();
  };

  /** 从缓存数据中获取值对应的数据 */
  const getItemByValue = (value?: SingleValue): DataItem | undefined => {
    if (isEmptyValue(value)) {
      return;
    }
    if (props.cacheData != null) {
      const temp = props.cacheData.find((d) => d[props.valueKey] === value);
      if (temp != null) {
        return temp;
      }
    }
    // 已废弃属性兼容
    if (!isEmptyValue(props.initValue as any)) {
      if (!props.multiple) {
        return props.initValue as DataItem;
      }
      return (props.initValue as DataItem[]).find(
        (d) => d[props.valueKey] === value
      );
    }
  };

  /** 多选从缓存数据中获取值对应的数据 */
  const getCacheItemByValue = (
    value: SingleValue,
    cacheKeys: DataKey[],
    cacheData: DataItem[] | undefined,
    tKeys: DataKey[],
    initValue?: DataItem[]
  ) => {
    if (cacheData != null) {
      const index = cacheKeys.indexOf(value as DataKey);
      if (index !== -1) {
        return cacheData[index];
      }
    }
    if (initValue != null) {
      const i = tKeys.indexOf(value as DataKey);
      if (i !== -1) {
        return initValue[i];
      }
    }
  };

  /** 获取多选的选中标签数据 */
  const getMultipleItems = (): SelectedItem[] => {
    const modelValue = props.modelValue as MultipleValue;
    if (isEmptyValue(modelValue, true)) {
      return [];
    }
    const selected: SelectedItem[] = [];
    const keys: MultipleValue = [];
    if (tableRef.value) {
      const data = tableRef.value.getSelectionRows() || [];
      data.forEach((item) => {
        if (!item._isMock) {
          const value = item[props.valueKey];
          const index = modelValue.indexOf(value);
          if (index !== -1) {
            selected.push({ value, label: item[props.labelKey], index });
            keys.push(value);
          }
        }
      });
    }
    if (keys.length !== modelValue.length) {
      const { valueKey, cacheData, initValue } = props;
      const cacheKeys = cacheData ? cacheData.map((d) => d[valueKey]) : [];
      const tKeys = initValue ? initValue.map((d: any) => d[valueKey]) : [];
      modelValue.forEach((value) => {
        if (!keys.includes(value)) {
          const item = getCacheItemByValue(
            value,
            cacheKeys,
            cacheData,
            tKeys,
            initValue as DataItem[]
          );
          const label = item ? item[props.labelKey] : String(value);
          const index = modelValue.indexOf(value);
          selected.push({ value, label, index });
        }
      });
    }
    selected.sort((a, b) => (a.index as number) - (b.index as number));
    return selected;
  };

  /** 更新选中标签数据 */
  const updateSelectedItems = (force?: boolean) => {
    // 单选模式
    if (!props.multiple) {
      const value = props.modelValue as SingleValue;
      const d = selectedItems.value.length ? selectedItems.value[0] : null;
      if (isEmptyValue(value)) {
        if (d != null) {
          selectedItems.value = [];
        }
        return;
      }
      if (force || !d || d.value !== value) {
        const temp = tableRef.value ? tableRef.value.getCurrentRow() : null;
        const t = temp && temp[props.valueKey] === value ? temp : void 0;
        const item = t || getItemByValue(value);
        const label = item ? item[props.labelKey] : String(value);
        selectedItems.value = [{ label, value }];
      }
      return;
    }
    // 多选模式
    if (isEmptyValue(props.modelValue, true)) {
      if (selectedItems.value.length) {
        selectedItems.value = [];
        nextTick(() => {
          updatePopover();
        });
      }
      return;
    }
    const keys = selectedItems.value.map((d) => d.value);
    if (force || valueIsChanged(props.modelValue, keys, true)) {
      selectedItems.value = getMultipleItems();
      nextTick(() => {
        updatePopover();
      });
    }
  };

  /** 让多选搜索框获取焦点 */
  const focusSearchInput = () => {
    selectRef.value && selectRef.value.focusSearchInput();
  };

  /** 初始值改变处理(已废弃) */
  const handleInitValueChange = (initValue: DataItem | DataItem[]) => {
    const valueKey = props.valueKey;
    if (!props.multiple) {
      const key = (initValue as DataItem)[valueKey];
      if (key === props.modelValue) {
        updateSelectedItems();
      } else {
        updateModelValue(key);
      }
      return;
    }
    const keys = (initValue as DataItem[]).map((d) => d[valueKey]);
    if (!valueIsChanged(props.modelValue, keys, true)) {
      updateSelectedItems();
    } else {
      updateModelValue(keys);
    }
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
    if (selectVisible.value !== visible) {
      selectVisible.value = visible;
      if (visible && props.tableProps?.virtual && tableRef.value) {
        const virtualTableRef = tableRef.value.getTableRef() as any;
        if (virtualTableRef != null && !virtualTableRef.wrapWidth) {
          nextTick(() => {
            virtualTableRef.updateWrapSize();
            nextTick(() => {
              updatePopover();
            });
          });
        }
      }
      emit('visibleChange', visible);
    }
  };

  /** 删除多选标签 */
  const handleSelectRemove = (item: SelectedItem) => {
    const values = (props.modelValue || []) as MultipleValue;
    updateModelValue(values.filter((v) => v !== item.value));
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

  /** 筛选关键字改变事件 */
  const handleSelectFilter = (keywords: string) => {
    emit('filterChange', keywords);
  };

  /** 表格多选选中值改变事件 */
  const updateSelectedRowKeys = (rowKeys: DataKey[]) => {
    if (props.multiple) {
      updateModelValue(rowKeys);
    }
  };

  /** 表格单选选中值改变事件 */
  const updateCurrentRowKey = (rowKey?: DataKey) => {
    if (!props.multiple) {
      updateModelValue(rowKey);
    }
  };

  /** 表格单选选中数据改变事件 */
  const handleTableCurrentChange = (row?: DataItem | null) => {
    if (!props.multiple && row != null) {
      updateSelectedItems(true);
    }
  };

  /** 表格多选选中数据改变事件 */
  const handleTableSelectionChange = () => {
    if (props.multiple) {
      updateSelectedItems(true);
    }
  };

  /** 表格行点击事件 */
  const handleTableRowClick = (row: DataItem) => {
    if (!props.multiple) {
      updateVisible(false);
      emit('select', row);
    } else if (
      props.tableProps?.rowClickChecked &&
      tableRef.value &&
      !isDisableRow(
        row,
        tableRef.value.getData().indexOf(row),
        tableRef.value.tableProps.columns
      )
    ) {
      emit('select', tableRef.value.getSelectionRows() || []);
    }
  };

  /** 表格多选复选框勾选事件 */
  const handleTableSelect = (selection: DataItem[]) => {
    emit('select', selection);
  };

  /** 表格多选全选复选框勾选事件 */
  const handleTableSelectAll = (selection: DataItem[]) => {
    emit('select', selection);
  };

  /** 表格渲染完成事件 */
  const handleTableDone = () => {
    nextTick(() => {
      updatePopover();
    });
  };

  /** 同步选中值更新 */
  watch(
    () => props.modelValue,
    () => {
      updateSelectedItems();
    },
    { deep: true }
  );

  /** 更新选中数据 */
  watch(
    () => props.cacheData,
    () => {
      updateSelectedItems(true);
    }
  );

  watch(
    () => props.initValue,
    (initValue) => {
      if (!isEmptyValue(initValue as any)) {
        handleInitValueChange(initValue as DataItem | DataItem[]);
      }
    },
    { deep: true }
  );

  /** 禁用时自动关闭气泡 */
  watch(
    () => props.disabled,
    (disabled) => {
      if (disabled) {
        updateVisible(false);
      }
    }
  );

  /** 回显默认值 */
  onMounted(() => {
    if (!isEmptyValue(props.initValue as any, props.multiple)) {
      handleInitValueChange(props.initValue as DataItem | DataItem[]);
    } else if (!isEmptyValue(props.modelValue, props.multiple)) {
      updateSelectedItems();
    }
  });

  defineExpose({
    selectRef,
    tableRef,
    selectVisible,
    selectedItems,
    selectedLabel,
    currentRowKey,
    selectedRowKeys,
    updatePopover,
    updateSelectedItems,
    updateVisible,
    focusSearchInput
  });
</script>
