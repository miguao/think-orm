<!-- 通用下拉选择 -->
<template>
  <EleTooltip
    ref="tooltipRef"
    trigger="click"
    :visible="visible"
    :disabled="disabled"
    :placement="placement"
    :teleported="teleported"
    :width="popperWidth"
    :popperClass="popperClass"
    :popperOptions="popperOptions"
    :transition="transition"
    :gpuAcceleration="transition === 'el-fade-in-linear'"
    effect="light"
    :persistent="true"
    :isPopover="true"
    :triggerKeys="[]"
    @update:visible="handleUpdatePopoverVisible"
  >
    <div
      :class="[
        'ele-select',
        selectClass,
        { 'is-empty': isEmpty },
        { 'is-multiple': multiple },
        { 'is-disabled': disabled },
        { 'is-filterable': filterable },
        { 'is-small': size === 'small' },
        { 'is-large': size === 'large' },
        { 'is-opened': visible }
      ]"
      :style="selectStyle"
      @click="handleSelectInputClick"
    >
      <ElInput
        ref="inputRef"
        :size="size"
        :disabled="disabled"
        :validateEvent="false"
        :modelValue="inputValue"
        :placeholder="filterable && multiple && visible ? '' : inputPlaceholder"
        :readonly="!(filterable && !multiple)"
        :style="inputStyle"
        @update:modelValue="updateInputValue"
        @click="handleInputClick"
        @focus="handleInputFocus"
        @blur="handleInputBlur"
        @keydown.esc="handleInputEsc"
      >
        <template v-if="$slots.prefix" #prefix>
          <slot name="prefix"></slot>
        </template>
        <template #suffix>
          <ElIcon
            v-if="clearable && !disabled && !isEmpty"
            class="ele-select-clear el-input__icon"
            @click.stop="handleClear"
          >
            <slot name="clearIcon">
              <CloseCircleFilled />
            </slot>
          </ElIcon>
          <ElIcon class="ele-select-arrow el-input__icon">
            <slot name="suffixIcon" :visible="visible">
              <ArrowDown />
            </slot>
          </ElIcon>
        </template>
      </ElInput>
      <div v-if="multiple" class="ele-select-tags" :style="selectTagsStyle">
        <ElTag
          v-for="(item, index) in currentTags"
          :key="index + '-' + item.value"
          :size="size"
          :type="tagType"
          :closable="!disabled"
          :disableTransitions="true"
          :title="item.label"
          @close="handleTagClose(item)"
        >
          <template
            v-if="
              item.label &&
              maxTagTextLength &&
              item.label.length > maxTagTextLength
            "
          >
            {{ item.label.slice(0, maxTagTextLength) }}...
          </template>
          <template v-else>{{ item.label }}</template>
        </ElTag>
        <ElTag
          v-if="isCollapse"
          :size="size"
          :type="tagType"
          :disableTransitions="true"
        >
          <slot
            name="maxTagPlaceholder"
            :omittedValues="omittedTags"
            :omittedSize="omittedSize"
          >
            +{{ omittedSize }}
          </slot>
        </ElTag>
        <ElTag
          v-if="!disabled && filterable"
          :size="size"
          :disableTransitions="true"
          class="ele-select-search"
        >
          <ElInput
            ref="searchRef"
            :size="size"
            :validateEvent="false"
            :modelValue="searchValue"
            :placeholder="inputPlaceholder"
            @update:modelValue="updateSearchValue"
            @keydown.esc="handleInputEsc"
          />
        </ElTag>
      </div>
    </div>
    <template #body>
      <ReceiverView
        v-if="persistent || visible"
        class="ele-popover-body"
        @click="focusSearchInput"
        @mouseup="focusSearchInput"
      >
        <slot></slot>
      </ReceiverView>
    </template>
  </EleTooltip>
</template>

<script lang="ts" setup>
  import { ref, computed, watch, nextTick } from 'vue';
  import { ElInput, ElIcon, ElTag } from 'element-plus';
  import { ArrowDown, CloseCircleFilled } from '../icons/index';
  import EleTooltip from '../ele-tooltip/index.vue';
  import ReceiverView from '../ele-config-provider/components/receiver-view';
  import type { ElInputInstance } from '../ele-app/el';
  import type { EleTooltipInstance } from '../ele-app/plus';
  import type { SelectedItem, MultipleValue } from './types';
  import { basicSelectProps, basicSelectEmits } from './props';

  defineOptions({ name: 'EleBasicSelect' });

  const props = defineProps(basicSelectProps);

  const emit = defineEmits(basicSelectEmits);

  /** 气泡组件 */
  const tooltipRef = ref<EleTooltipInstance>(null);

  /** 输入框组件 */
  const inputRef = ref<ElInputInstance>(null);

  /** 多选搜索框组件 */
  const searchRef = ref<ElInputInstance>(null);

  /** 输入框值 */
  const inputValue = ref<string>(
    props.multiple || !props.selectedLabel ? '' : props.selectedLabel
  );

  /** 多选时搜索框值 */
  const searchValue = ref<string>('');

  /** 是否未选中 */
  const isEmpty = computed<boolean>(() => {
    if (!props.multiple) {
      return props.value == null || props.value === '';
    }
    return !Array.isArray(props.value) || !props.value.length;
  });

  /** 输入框提示文本 */
  const inputPlaceholder = computed<string>(() => {
    const str = isEmpty.value && props.placeholder ? props.placeholder : '';
    if (!props.filterable || !props.visible || props.multiple) {
      return str;
    }
    return props.selectedLabel || str;
  });

  /** 多选时是否折叠标签 */
  const isCollapse = computed<boolean>(() => {
    return (
      typeof props.maxTagCount === 'number' &&
      props.selected != null &&
      props.selected.length > props.maxTagCount
    );
  });

  /** 多选显示的标签 */
  const currentTags = computed<SelectedItem[]>(() => {
    if (!isCollapse.value || isEmpty.value || props.selected == null) {
      return props.selected || [];
    }
    return props.selected.slice(0, props.maxTagCount);
  });

  /** 多选折叠的标签 */
  const omittedTags = computed<SelectedItem[]>(() => {
    if (!isCollapse.value || isEmpty.value || props.selected == null) {
      return [];
    }
    return props.selected.slice(props.maxTagCount);
  });

  /** 多选折叠的标签数量 */
  const omittedSize = computed<number>(() => {
    if (isEmpty.value || props.maxTagCount == null || props.maxTagCount < 0) {
      return 0;
    }
    return (props.value as MultipleValue).length - props.maxTagCount;
  });

  /** 是否是下拉框点击 */
  let isSelectInputClick = false;

  /** 更新下拉框位置 */
  const updatePopper = () => {
    tooltipRef.value && tooltipRef.value.updatePopper();
  };

  /** 让多选搜索框获取焦点 */
  const focusSearchInput = (e?: MouseEvent) => {
    if (props.filterable && props.visible) {
      if (e != null && e.target != null) {
        const target = e.target as HTMLElement;
        if (target.nodeName && target.nodeName.toLowerCase() === 'input') {
          return;
        }
      }
      const input = props.multiple ? searchRef.value : inputRef.value;
      input && input.focus();
      nextTick(() => {
        input && input.focus();
      });
    }
  };

  /** 更新输入框值 */
  const updateSearchValue = (modelValue: string) => {
    if (props.filterable && props.visible && props.multiple) {
      searchValue.value = modelValue;
      emit('filterChange', modelValue);
    }
  };

  /** 更新输入框值 */
  const updateInputValue = (modelValue: string) => {
    if (props.filterable && props.visible && !props.multiple) {
      inputValue.value = modelValue;
      emit('filterChange', modelValue);
    }
  };

  /** 更新下拉框显示状态 */
  const updateVisible = (visible: boolean) => {
    isSelectInputClick = false;
    if (!props.disabled || !visible) {
      emit('update:visible', visible);
    }
  };

  /** 气泡下拉框显示状态更新事件 */
  const handleUpdatePopoverVisible = (visible: boolean) => {
    if (!visible && props.filterable && isSelectInputClick) {
      isSelectInputClick = false;
      return;
    }
    updateVisible(visible);
  };

  /** 输入框点击事件 */
  const handleSelectInputClick = () => {
    isSelectInputClick = true;
  };

  /** 多选删除标签 */
  const handleTagClose = (item: SelectedItem) => {
    if (!props.disabled) {
      emit('removeTag', item);
    }
  };

  /** 清空 */
  const handleClear = () => {
    isSelectInputClick = false;
    emit('clear');
  };

  /** 输入框点击事件 */
  const handleInputClick = (e: MouseEvent) => {
    if (props.automaticDropdown && props.visible) {
      isSelectInputClick = true;
      e.stopPropagation();
    }
  };

  /** 获取焦点事件 */
  const handleInputFocus = (e: FocusEvent) => {
    if (props.automaticDropdown && !props.visible) {
      updateVisible(true);
    }
    emit('focus', e);
  };

  /** 失去焦点事件 */
  const handleInputBlur = (e: FocusEvent) => {
    emit('blur', e);
  };

  /** 失去焦点事件 */
  const handleInputEsc = (e: FocusEvent) => {
    if (!props.disabled && props.visible) {
      e.stopPropagation();
      e.preventDefault();
      updateVisible(false);
    }
  };

  watch(
    () => props.selectedLabel,
    (label) => {
      if (!props.filterable || !props.visible) {
        inputValue.value = props.multiple || !label ? '' : label;
      }
    }
  );

  watch(
    () => props.visible,
    (visible) => {
      if (props.filterable) {
        if (props.multiple) {
          searchValue.value = '';
          if (visible) {
            focusSearchInput();
          }
        } else {
          const label = props.selectedLabel;
          inputValue.value = visible || !label ? '' : label;
        }
      }
    }
  );

  defineExpose({
    tooltipRef,
    inputRef,
    searchRef,
    currentTags,
    omittedTags,
    omittedSize,
    updatePopper,
    focusSearchInput,
    updateSearchValue,
    updateInputValue,
    updateVisible
  });
</script>
