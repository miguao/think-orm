<template>
  <el-select
    clearable
    :model-value="roleId"
    :placeholder="placeholder"
    class="ele-fluid"
    @update:modelValue="updateValue"
  >
    <el-option
      v-for="item in roleData"
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
  import type { Role } from '@/api/merchant/role/model';

  const props = withDefaults(
    defineProps<{
      modelValue?: number;
      placeholder?: string;
    }>(),
    {
      placeholder: '请选择角色'
    }
  );

  const emit = defineEmits<{
    (e: 'update:modelValue', value: number | undefined): void;
  }>();

  /** 选中的角色id */
  const roleId = computed(() => props.modelValue);

  /** 角色数据 */
  const roleData = ref<Role[]>([]);

  /** 更新选中数据 */
  const updateValue = (value: number | undefined) => {
    emit('update:modelValue', value);
  };

  /** 获取角色数据 */
  getDictionaryByCode('merchant_role,id,name')
    .then((data) => {
      roleData.value = data.map((item: any) => ({
        id: item.id,
        name: item.name
      }));
    })
    .catch((exception) => {
      EleMessage.error({ message: exception.message, plain: true });
    });
</script>
