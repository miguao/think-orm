import type { PropType, ExtractPropTypes } from 'vue';
import type { StyleValue } from '../ele-app/types';
import type { ElInputProps, ElEmptyProps } from '../ele-app/el';
import type { EleTooltipProps } from '../ele-app/plus';
import type { IconItem } from './types';

/**
 * 属性
 */
export const iconSelectProps = {
  /** 选中值 */
  modelValue: String,
  /** 图标数据 */
  data: Array as PropType<string[] | IconItem[]>,
  /** 是否禁用 */
  disabled: Boolean,
  /** 尺寸 */
  size: String as PropType<ElInputProps['size']>,
  /** 是否支持清除 */
  clearable: Boolean,
  /** 无选中时提示文本 */
  placeholder: String,
  /** 是否在输入框获得焦点后自动弹出选项菜单 */
  automaticDropdown: Boolean,
  /** 是否可以筛选 */
  filterable: [Boolean, String] as PropType<boolean | 'popper'>,
  /** 是否将下拉框插入 body */
  teleported: {
    type: Boolean,
    default: true
  },
  /** 始终渲染下拉框 */
  persistent: {
    type: Boolean,
    default: true
  },
  /** 下拉框位置 */
  placement: {
    type: String as PropType<EleTooltipProps['placement']>,
    default: 'bottom-start'
  },
  /** 下拉框渐变动画 */
  transition: {
    type: String,
    default: 'el-zoom-in-top'
  },
  /** popper.js 参数 */
  popperOptions: Object as PropType<EleTooltipProps['popperOptions']>,
  /** 下拉框类名 */
  popperClass: String,
  /** 下拉框宽度 */
  popperWidth: {
    type: [Number, String],
    default: 414
  },
  /** 下拉框高度 */
  popperHeight: {
    type: [Number, String],
    default: 260
  },
  /** 自定义样式 */
  selectStyle: Object as PropType<StyleValue>,
  /** 自定义样式 */
  inputStyle: Object as PropType<StyleValue>,
  /** 自定义多选标签容器样式 */
  selectTagsStyle: Object as PropType<StyleValue>,
  /** 顶部选项卡只有一个时隐藏 */
  hideOnSingleTab: Boolean,
  /** 空组件属性 */
  emptyProps: Object as PropType<ElEmptyProps>,
  /** 是否显示文本提示 */
  tooltip: {
    type: Boolean,
    default: true
  },
  /** 提示属性 */
  tooltipProps: Object as PropType<EleTooltipProps>,
  /** 头部样式 */
  headerStyle: Object as PropType<StyleValue>,
  /** 选项卡样式 */
  tabsStyle: Object as PropType<StyleValue>,
  /** 搜索区样式 */
  searchStyle: Object as PropType<StyleValue>,
  /** 菜单样式 */
  menusStyle: Object as PropType<StyleValue>,
  /** 主体样式 */
  bodyStyle: Object as PropType<StyleValue>,
  /** 网格样式 */
  gridStyle: Object as PropType<StyleValue>,
  /** 图标样式 */
  itemStyle: Object as PropType<StyleValue>,
  /** 搜索框提示文本 */
  filterPlaceholder: {
    type: String,
    default: ''
  },
  /** 是否开启响应式 */
  responsive: {
    type: Boolean,
    default: null
  }
};

export type IconSelectProps = ExtractPropTypes<typeof iconSelectProps>;

/**
 * 事件
 */
export const iconSelectEmits = {
  /** 更新展开状态 */
  'update:modelValue': (_value?: string | null) => true,
  /** 选中值改变事件 */
  change: (_value?: string | null) => true,
  /** 下拉框展开状态改变事件 */
  visibleChange: (_visible: boolean) => true,
  /** 清空事件 */
  clear: () => true,
  /** 获取焦点事件 */
  focus: (_e: FocusEvent) => true,
  /** 失去焦点事件 */
  blur: (_e: FocusEvent) => true
};
