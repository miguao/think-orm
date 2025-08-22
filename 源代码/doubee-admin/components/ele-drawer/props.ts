import type { PropType, ExtractPropTypes } from 'vue';
import { drawerProps as elDrawerProps } from 'element-plus';
import type { StyleValue } from '../ele-app/types';

/**
 * 属性
 */
export const drawerProps = {
  ...elDrawerProps,
  /** 自定义标题样式 */
  headerStyle: Object as PropType<StyleValue>,
  /** 标题样式 */
  titleStyle: Object as PropType<StyleValue>,
  /** 自定义主体样式 */
  bodyStyle: Object as PropType<StyleValue>,
  /** 自定义底部样式 */
  footerStyle: Object as PropType<StyleValue>,
  /** 自定义关闭按钮样式 */
  closeBtnStyle: Object as PropType<StyleValue>,
  /** 是否限制在主体内部 */
  inner: Boolean
};

export type DrawerProps = ExtractPropTypes<typeof drawerProps>;
