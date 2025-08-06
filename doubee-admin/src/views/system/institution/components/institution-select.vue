<template>
  <el-tree-select
    clearable
    filterable
    :data="data"
    check-strictly
    default-expand-all
    node-key="id"
    :props="{ label: 'name' }"
    :placeholder="placeholder"
    v-model="model"
    class="ele-fluid"
    :popper-options="{ strategy: 'fixed' }"
  />
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { EleMessage, toTree } from 'ele-admin-plus';
  import { getInstitutionList } from '@/api/system/institution';
  import type { Institution } from '@/api/system/institution/model';

  withDefaults(
    defineProps<{
      placeholder?: string;
    }>(),
    {
      placeholder: '请选择所属机构'
    }
  );

  /** 选中的机构 */
  const model = defineModel<number | string>({ type: [Number, String] });

  /** 机构数据 */
  const data = ref<Institution[]>([]);

  /** 获取机构数据 */
  getInstitutionList({})
    .then((list) => {
      data.value = toTree({
        data: list,
        idField: 'id',
        parentIdField: 'parent_id'
      });
    })
    .catch((exception) => {
      EleMessage.error({ message: exception.message, plain: true });
    });
</script>
