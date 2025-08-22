import { eachTree } from '../utils/common';
import { isEmptyValue } from '../ele-basic-select/util';
import type { MultipleValue, SelectedItem } from '../ele-basic-select/types';
import type { TreeData } from './types';

/**
 * 获取平铺后的数据值和数据
 * @param data 数据
 * @param valueKey 数据值字段名
 * @param childrenKey 子级字段名
 */
export function getKeysAndList(
  data: TreeData | undefined,
  valueKey: string,
  childrenKey: string
): [MultipleValue, TreeData] {
  const keys: MultipleValue = [];
  const list: TreeData = [];
  eachTree(
    data,
    (d) => {
      keys.push(d[valueKey]);
      list.push(d);
    },
    childrenKey
  );
  return [keys, list];
}

/**
 * 获取普通结构的多选选中标签
 * @param data 树数据
 * @param checkedKeys 多选选中值
 * @param valueKey 值键名
 * @param labelKey 名称键名
 * @param childrenKey 子级数据键名
 * @param cacheData 缓存的数据
 * @param checkedData 树选中的数据
 */
export function getNormalSelectedItems(
  data: TreeData | undefined,
  checkedKeys: MultipleValue | null | undefined,
  valueKey: string,
  labelKey: string,
  childrenKey: string,
  cacheData?: TreeData,
  checkedData?: TreeData
) {
  const selected: SelectedItem[] = [];
  if (isEmptyValue(checkedKeys) || !(checkedKeys as MultipleValue).length) {
    return selected;
  }
  const cacheKeys = cacheData ? cacheData.map((d) => d[valueKey]) : [];
  const ckKeys = checkedData ? checkedData.map((d) => d[valueKey]) : [];
  const [dataKeys, list] = getKeysAndList(data, valueKey, childrenKey);
  (checkedKeys as MultipleValue).forEach((key) => {
    const index = cacheKeys.indexOf(key);
    let item = cacheData && index !== -1 ? cacheData[index] : void 0;
    if (index === -1 && checkedData) {
      const tIndex = ckKeys.indexOf(key);
      if (tIndex !== -1) {
        item = checkedData[tIndex];
      }
    }
    if (!item && data) {
      const i = dataKeys.indexOf(key);
      if (i !== -1) {
        item = list[i];
      }
    }
    selected.push({ value: key, label: item ? item[labelKey] : String(key) });
  });
  return selected;
}

/**
 * 获取树结构的多选选中标签
 * @param data 树数据
 * @param checkedKeys 多选选中值
 * @param valueKey 值键名
 * @param labelKey 名称键名
 * @param childrenKey 子级数据键名
 * @param showParent 只显示父级(否则只显示子级)
 */
export function getTreeSelectedItems(
  data: TreeData | undefined,
  checkedKeys: MultipleValue | null | undefined,
  valueKey: string,
  labelKey: string,
  childrenKey: string,
  showParent: boolean,
  hide?: boolean
) {
  const selected: SelectedItem[] = [];
  if (isEmptyValue(checkedKeys) || !(checkedKeys as MultipleValue).length) {
    return selected;
  }
  if (data) {
    data.forEach((d) => {
      const value = d[valueKey];
      const children = d[childrenKey];
      const checked = (checkedKeys as MultipleValue).includes(value);
      if (showParent) {
        if (checked) {
          selected.push({ value, label: d[labelKey], hide });
        }
        getTreeSelectedItems(
          children,
          checkedKeys,
          valueKey,
          labelKey,
          childrenKey,
          showParent,
          checked ? true : hide
        ).forEach((item) => {
          selected.push(item);
        });
      } else {
        const hasChildren = !!(children && children.length);
        if (checked) {
          selected.push({ value, label: d[labelKey], hide: hasChildren });
        }
        if (hasChildren) {
          getTreeSelectedItems(
            children,
            checkedKeys,
            valueKey,
            labelKey,
            childrenKey,
            showParent
          ).forEach((item) => {
            selected.push(item);
          });
        }
      }
    });
  }
  return selected;
}

/**
 * 检查并补充多选选中标签
 * @param selected 标签数据
 * @param checkedKeys 多选选中值
 * @param valueKey 值键名
 * @param labelKey 名称键名
 * @param cacheData 缓存的数据
 */
export function checkSelectedItems(
  selected: SelectedItem[],
  checkedKeys: MultipleValue | null | undefined,
  valueKey: string,
  labelKey: string,
  cacheData?: TreeData
) {
  const selectedValues = selected.map((s) => s.value);
  const cacheKeys = cacheData ? cacheData.map((d) => d[valueKey]) : [];
  (checkedKeys as MultipleValue).forEach((key) => {
    if (!selectedValues.includes(key)) {
      const index = cacheKeys.indexOf(key);
      const item = cacheData && index !== -1 ? cacheData[index] : void 0;
      selected.push({ value: key, label: item ? item[labelKey] : String(key) });
    }
  });
  return selected;
}

/**
 * 判断是否全选
 * @param data 数据
 * @param checkedKeys 选中值
 * @param valueKey 值键名
 * @param childrenKey 子级数据键名
 * @param disabledKey 禁用键名
 */
export function isCheckAll(
  data: TreeData | null | undefined,
  checkedKeys: MultipleValue | null | undefined,
  valueKey: string,
  childrenKey: string,
  disabledKey: string
) {
  if (!data || !data.length || !checkedKeys || !checkedKeys.length) {
    return false;
  }
  return data.every((d) => {
    if (d[disabledKey] || checkedKeys.includes(d[valueKey])) {
      return true;
    }
    if (d[childrenKey] && d[childrenKey].length) {
      return isCheckAll(
        d[childrenKey],
        checkedKeys,
        valueKey,
        childrenKey,
        disabledKey
      );
    }
    return false;
  });
}

/**
 * 获取选中值
 * @param keys 全部选中节点值
 * @param selected 选中的标签数据
 * @param checkedValueStrategy 多选值绑定策略
 */
export function getModelValue(
  keys: MultipleValue,
  selected: SelectedItem[],
  checkedValueStrategy?: boolean
) {
  if (!checkedValueStrategy) {
    return keys;
  }
  const ids: MultipleValue = [];
  selected.forEach((d) => {
    if (!d.hide) {
      ids.push(d.value);
    }
  });
  return ids;
}
