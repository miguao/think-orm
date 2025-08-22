import type { PropType, ExtractPropTypes } from 'vue';
import type { StyleValue } from '../ele-app/types';

/**
 * 属性
 */
export const splitPanelProps = {
  /** 默认大小 */
  size: String,
  /** 最小尺寸 */
  minSize: Number,
  /** 最大尺寸 */
  maxSize: Number,
  /** 间距 */
  space: String,
  /** 自定义边栏样式 */
  customStyle: Object as PropType<StyleValue>,
  /** 自定义边栏容器样式 */
  customWrapStyle: Object as PropType<StyleValue>,
  /** 自定义内容样式 */
  bodyStyle: Object as PropType<StyleValue>,
  /** 是否可折叠 */
  allowCollapse: Boolean,
  /** 折叠按钮样式 */
  collapseStyle: Object as PropType<StyleValue>,
  /** 是否折叠 */
  collapse: Boolean,
  /** 是否垂直方向 */
  vertical: Boolean,
  /** 是否反向布局 */
  reverse: Boolean,
  /** 是否可拉伸宽度 */
  resizable: Boolean,
  /** 内部表格弹性布局 */
  flexTable: Boolean,
  /** 是否开启响应式 */
  responsive: {
    type: Boolean,
    default: null
  }
};

export type SplitPanelProps = ExtractPropTypes<typeof splitPanelProps>;

/**
 * 事件
 */
export const splitPanelEmits = {
  /** 更新折叠状态 */
  'update:collapse': (_collapse: boolean) => true
};
