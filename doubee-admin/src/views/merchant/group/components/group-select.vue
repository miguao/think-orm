<template>
  <el-select
    clearable
    :model-value="GroupId"
    :placeholder="placeholder"
    class="ele-fluid"
    @update:modelValue="updateValue"
  >
    <el-option
      v-for="item in groupData"
      :key="item.id"
      :value="(item as any).id"
      :label="item.name"
    />
  </el-select>
</template>

<script lang="ts" setup>
  import { ref, computed } from 'vue';
  import { EleMessage } from 'ele-admin-plus';
  import { getDictionaryByCode } from '@/api/system/dictionary';
  import type { Group } from '@/api/merchant/group/model';

  const props = withDefaults(
    defineProps<{
      modelValue?: number;
      placeholder?: string;
    }>(),
    {
      placeholder: '请选择用户组'
    }
  );

  const emit = defineEmits<{
    (e: 'update:modelValue', value: number | undefined): void;
  }>();

  /** 选中的用户组id */
  const GroupId = computed(() => props.modelValue);

  /** 用户组数据 */
  const groupData = ref<Group[]>([]);

  /** 更新选中数据 */
  const updateValue = (value: number | undefined) => {
    emit('update:modelValue', value);
  };

  /** 获取用户组数据 */
  getDictionaryByCode('merchant_role,id,name')
    .then((data) => {
      groupData.value = data.map((item: any) => ({
        id: item.id,
        name: item.name
      }));
    })
    .catch((exception) => {
      EleMessage.error({ message: exception.message, plain: true });
    });
</script>
