/**
 * 图标数据格式
 */
export interface IconItem {
  /** 标题 */
  title: string;
  /** 图标 */
  icons?: string[];
  /** 子级 */
  children?: {
    /** 子级标题 */
    title: string;
    /** 子级图标 */
    icons?: string[];
  }[];
}
