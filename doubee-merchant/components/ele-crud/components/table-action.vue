<!-- 操作列按钮 -->
<template>
  <ElLink
    v-if="editLinkProps !== false"
    type="primary"
    underline="never"
    v-bind="(editLinkProps === true ? void 0 : editLinkProps) || {}"
    @click="handleEditBtnClick"
  >
    {{ lang.edit }}
  </ElLink>
  <ElDivider direction="vertical" />
  <ElLink
    v-if="delLinkProps !== false"
    type="danger"
    underline="never"
    v-bind="(delLinkProps === true ? void 0 : delLinkProps) || {}"
    @click="handleDelBtnClick"
  >
    {{ lang.delete }}
  </ElLink>
  <slot></slot>
</template>

<script lang="ts" setup>
  import type { PropType } from 'vue';
  import { ElLink, ElDivider } from 'element-plus';
  import type { ElLinkProps } from '../../ele-app/el';
  import type { DataItem } from '../../ele-data-table/types';
  import type { BtnClickAction, CrudLocale } from '../types';

  defineOptions({ name: 'TableAction' });

  const props = defineProps({
    /** 修改按钮属性 */
    editLinkProps: [Boolean, Object] as PropType<boolean | ElLinkProps>,
    /** 删除按钮属性 */
    delLinkProps: [Boolean, Object] as PropType<boolean | ElLinkProps>,
    /** 数据 */
    item: Object as PropType<DataItem>,
    /** 国际化 */
    lang: {
      type: Object as PropType<Partial<CrudLocale>>,
      required: true
    }
  });

  const emit = defineEmits({
    /** 操作按钮点击事件 */
    btnClick: (_action: BtnClickAction, _e: MouseEvent, _item?: DataItem) =>
      true
  });

  /** 按钮点击事件 */
  const handleBtnClick = (action: BtnClickAction, e: MouseEvent) => {
    emit('btnClick', action, e, props.item);
  };

  /** 修改按钮点击事件 */
  const handleEditBtnClick = (e: MouseEvent) => {
    handleBtnClick('edit', e);
  };

  /** 除按钮点击事件 */
  const handleDelBtnClick = (e: MouseEvent) => {
    handleBtnClick('del', e);
  };
</script>
