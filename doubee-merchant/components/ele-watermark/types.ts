/**
 * 间距
 */
export type WatermarkGap = [number, number];

/**
 * 偏移量
 */
export type WatermarkOffset = [number, number];

/**
 * 文字样式
 */
export interface WatermarkFont {
  /** 字体颜色 */
  color?: string;
  /** 字体大小 */
  fontSize?: number;
  /** 字体粗细 */
  fontWeight?: 'normal' | 'light' | 'weight' | number;
  /** 字体样式 */
  fontFamily?: string;
  /** 字体类型 */
  fontStyle?: 'none' | 'normal' | 'italic' | 'oblique';
}

/**
 * 水印篡改观测参数
 */
export interface MutationOption {
  /** 获取根节点 */
  getRoot: () => HTMLElement | null;
  /** 获取水印节点 */
  getEl: () => HTMLElement | null;
  /** 水印被删除回调 */
  onDeleted: () => void;
  /** 水印样式被篡改回调 */
  onDalsified: () => void;
}
