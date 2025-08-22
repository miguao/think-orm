/**
 * 单选值
 */
export type SingleValue = string | number | boolean;

/**
 * 多选值
 */
export type MultipleValue = Array<SingleValue>;

/**
 * 值
 */
export type SelectValue = SingleValue | MultipleValue | undefined | null;

/**
 * 多选选中数据
 */
export interface SelectedItem {
  /** 文本 */
  label: string;
  /** 值 */
  value: SingleValue;
  /** 是否不显示 */
  hide?: boolean;
  /** 排序号 */
  index?: number;
}
