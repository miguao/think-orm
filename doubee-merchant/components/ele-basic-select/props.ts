import type { PropType, ExtractPropTypes } from 'vue';
import type { StyleValue } from '../ele-app/types';
import type { ElInputProps, ElTagProps } from '../ele-app/el';
import type { EleTooltipProps } from '../ele-app/plus';
import type { SelectValue, SelectedItem } from './types';

/**
 * 属性
 */
export const basicSelectProps = {
  /** 选中值 */
  value: {
    type: [String, Number, Boolean, Array] as PropType<SelectValue>,
    default: () => {
      return null;
    }
  },
  /** 是否多选 */
  multiple: Boolean,
  /** 是否禁用 */
  disabled: Boolean,
  /** 尺寸 */
  size: String as PropType<ElInputProps['size']>,
  /** 是否支持清除 */
  clearable: Boolean,
  /** 无选中时提示文本 */
  placeholder: String,
  /** 单选选中展示文本 */
  selectedLabel: String,
  /** 多选选中数据 */
  selected: Array as PropType<SelectedItem[]>,
  /** 多选标签最大显示数量 */
  maxTagCount: Number,
  /** 多选标签最大显示文本长度 */
  maxTagTextLength: Number,
  /** 多选标签类型 */
  tagType: String as PropType<ElTagProps['type']>,
  /** 是否在输入框获得焦点后自动弹出选项菜单 */
  automaticDropdown: Boolean,
  /** 是否可以筛选 */
  filterable: Boolean,
  /** 下拉框是否显示 */
  visible: Boolean,
  /** 是否将下拉框插入 body */
  teleported: Boolean,
  /** 始终渲染下拉框 */
  persistent: Boolean,
  /** 下拉框位置 */
  placement: String as PropType<EleTooltipProps['placement']>,
  /** 下拉框渐变动画 */
  transition: {
    type: String,
    default: 'el-fade-in-linear'
  },
  /** popper.js 参数 */
  popperOptions: Object as PropType<EleTooltipProps['popperOptions']>,
  /** 下拉框类名 */
  popperClass: String,
  /** 下拉框宽度 */
  popperWidth: [Number, String],
  /** 自定义类名 */
  selectClass: String,
  /** 自定义样式 */
  selectStyle: Object as PropType<StyleValue>,
  /** 自定义输入框样式 */
  inputStyle: Object as PropType<StyleValue>,
  /** 自定义多选标签容器样式 */
  selectTagsStyle: Object as PropType<StyleValue>
};

export type BasicSelectProps = ExtractPropTypes<typeof basicSelectProps>;

/**
 * 事件
 */
export const basicSelectEmits = {
  /** 更新下拉框显示状态 */
  'update:visible': (_visible: boolean) => true,
  /** 多选标签移除事件 */
  removeTag: (_item: SelectedItem) => true,
  /** 清空事件 */
  clear: () => true,
  /** 获取焦点事件 */
  focus: (_e: FocusEvent) => true,
  /** 失去焦点事件 */
  blur: (_e: FocusEvent) => true,
  /** 筛选输入框值改变事件 */
  filterChange: (_value: string) => true
};
