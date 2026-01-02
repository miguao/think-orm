<template>
  <el-tree-select
    clearable
    filterable
    :data="menuData"
    check-strictly
    default-expand-all
    node-key="id"
    :props="{ label: 'name' }"
    :placeholder="placeholder"
    :model-value="modelValue || void 0"
    class="ele-fluid"
    :popper-options="{ strategy: 'fixed' }"
    @update:modelValue="updateValue"
  >
    <template #default="{ data }">
      <el-icon
        v-if="data.icon"
        style="margin-right: 4px; transform: translateY(-1px)"
      >
        <component :is="data.icon" />
      </el-icon>
      <span>{{ data.name }}</span>
    </template>
    <template v-if="selectedIcon" #prefix>
      <el-icon color="var(--el-text-color-regular)" style="margin-right: 6px">
        <component :is="selectedIcon" />
      </el-icon>
    </template>
  </el-tree-select>
</template>

<script lang="ts" setup>
  import { ref, computed } from 'vue';
  import { EleMessage, toTree, findTree } from 'ele-admin-plus';
  import { getPermissionList } from '@/api/system/permission';
  import type { Permission } from '@/api/system/permission/model';

  const props = withDefaults(
    defineProps<{
      /** 选中的权限 */
      modelValue?: number | string;
      /** 提示信息 */
      placeholder?: string;
    }>(),
    {
      placeholder: '请选择上级权限'
    }
  );

  const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string): void;
  }>();

  /** 权限数据 */
  const menuData = ref<Permission[]>([]);

  /** 更新选中数据 */
  const updateValue = (value: number | string) => {
    emit('update:modelValue', value || 0);
  };

  /** 选中权限的图标 */
  const selectedIcon = computed(() => {
    if (!props.modelValue) {
      return;
    }
    return findTree(menuData.value, (d) => d.id == props.modelValue)?.icon;
  });

  /** 获取权限数据 */
  getPermissionList({})
    .then((list) => {
      menuData.value = toTree({
        data: list,
        idField: 'id',
        parentIdField: 'parent_id'
      });
    })
    .catch((e) => {
      EleMessage.error({ message: e.message, plain: true });
    });
</script>
