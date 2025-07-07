import type { PropType, ExtractPropTypes } from 'vue';
import type { StyleValue } from '../ele-app/types';
import type { WatermarkGap, WatermarkOffset, WatermarkFont } from './types';

export const watermarkProps = {
  /** 宽度 */
  width: Number,
  /** 高度 */
  height: Number,
  /** 旋转角度 */
  rotate: Number,
  /** 层级 */
  zIndex: Number,
  /** 图片源 */
  image: String,
  /** 文字内容 */
  content: [String, Array] as PropType<string | string[]>,
  /** 文字样式 */
  font: Object as PropType<WatermarkFont>,
  /** 间距 */
  gap: Array as unknown as PropType<WatermarkGap>,
  /** 距离左上角的偏移量 */
  offset: Array as unknown as PropType<WatermarkOffset>,
  /** 多行水印的行间距 */
  lineGap: {
    type: Number,
    default: 3
  },
  /** 自定义样式 */
  customStyle: Object as PropType<Exclude<StyleValue, string>>,
  /** 是否使用固定定位 */
  fixed: Boolean,
  /** 是否为外层添加定位样式 */
  wrapPosition: {
    type: Boolean,
    default: true
  },
  /** 是否禁用 */
  disabled: Boolean
};

export type WatermarkProps = ExtractPropTypes<typeof watermarkProps>;
