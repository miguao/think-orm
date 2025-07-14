<!-- 表头操作按钮 -->
<template>
  <ElButton
    v-if="addBtnProps !== false"
    type="primary"
    :icon="PlusOutlined"
    class="ele-btn-icon"
    v-bind="(addBtnProps === true ? void 0 : addBtnProps) || {}"
    @click="handleAddBtnClick"
  >
    {{ lang.add }}
  </ElButton>
  <ElButton
    v-if="delBtnProps !== false"
    type="danger"
    :icon="DeleteOutlined"
    class="ele-btn-icon"
    v-bind="(delBtnProps === true ? void 0 : delBtnProps) || {}"
    @click="handleDelBtnClick"
  >
    {{ lang.deleteBatch }}
  </ElButton>
  <slot></slot>
</template>

<script lang="ts" setup>
  import type { PropType } from 'vue';
  import { ElButton } from 'element-plus';
  import { DeleteOutlined, PlusOutlined } from '../../icons/index';
  import type { ElButtonProps } from '../../ele-app/el';
  import type { BtnClickAction, CrudLocale } from '../types';

  defineOptions({ name: 'TableToolbar' });

  defineProps({
    /** 添加按钮属性 */
    addBtnProps: [Boolean, Object] as PropType<boolean | ElButtonProps>,
    /** 批量删除按钮属性 */
    delBtnProps: [Boolean, Object] as PropType<boolean | ElButtonProps>,
    /** 国际化 */
    lang: {
      type: Object as PropType<Partial<CrudLocale>>,
      required: true
    }
  });

  const emit = defineEmits({
    /** 操作按钮点击事件 */
    btnClick: (_action: BtnClickAction, _e: MouseEvent) => true
  });

  /** 按钮点击事件 */
  const handleBtnClick = (action: BtnClickAction, e: MouseEvent) => {
    emit('btnClick', action, e);
  };

  /** 添加按钮点击事件 */
  const handleAddBtnClick = (e: MouseEvent) => {
    handleBtnClick('add', e);
  };

  /** 批量删除按钮点击事件 */
  const handleDelBtnClick = (e: MouseEvent) => {
    handleBtnClick('delSelections', e);
  };
</script>
