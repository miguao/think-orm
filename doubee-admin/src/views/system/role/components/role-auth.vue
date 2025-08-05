<template>
  <ele-modal
    :width="460"
    title="分配权限"
    position="center"
    v-model="visible"
    :body-style="{ padding: '12px 0 12px 22px' }"
    @open="handleOpen"
  >
    <ele-loading
      :loading="authLoading"
      :spinner-style="{ background: 'transparent' }"
      :style="{
        paddingRight: '20px',
        height: 'calc(100vh - 192px)',
        maxHeight: 'calc(100dvh - 192px)',
        minHeight: '100px',
        overflow: 'auto'
      }"
    >
      <el-tree
        ref="treeRef"
        show-checkbox
        :data="authData"
        node-key="id"
        :default-expand-all="true"
        :props="{ label: 'name' }"
        :default-checked-keys="checkedKeys"
        :style="{ '--ele-tree-item-height': '28px' }"
      >
        <template #default="scope">
          <div>
            <el-icon
              v-if="scope.data.icon"
              :size="16"
              style="margin-right: 6px; vertical-align: -5px"
            >
              <component :is="scope.data.icon" />
            </el-icon>
            <span style="vertical-align: -2px">{{ scope.data.name }}</span>
          </div>
        </template>
      </el-tree>
    </ele-loading>
    <template #footer>
      <el-button @click="handleCancel">取消</el-button>
      <el-button type="primary" :loading="loading" @click="save">
        保存
      </el-button>
    </template>
  </ele-modal>
</template>

<script lang="ts" setup>
  import { ref, nextTick } from 'vue';
  import type { ElTree } from 'element-plus';
  import { EleMessage, toTree, eachTree } from 'ele-admin-plus';
  import { getRolePermissions, updateRole } from '@/api/system/role';
  import type { Role } from '@/api/system/role/model';
  import type { Permission } from '@/api/system/permission/model';

  const props = defineProps<{
    /** 当前角色数据 */
    data?: Role | null;
  }>();

  /** 弹窗是否打开 */
  const visible = defineModel({ type: Boolean });

  /** 树组件实例 */
  const treeRef = ref<InstanceType<typeof ElTree> | null>(null);

  /** 权限数据 */
  const authData = ref<Permission[]>([]);

  /** 权限数据请求状态 */
  const authLoading = ref(false);

  /** 提交状态 */
  const loading = ref(false);

  /** 角色权限选中的keys */
  const checkedKeys = ref<number[]>([]);

  /** 查询权限数据 */
  const query = () => {
    authData.value = [];
    checkedKeys.value = [];
    if (!props.data) {
      return;
    }
    authLoading.value = true;

    getRolePermissions(props.data.id)
      .then((data) => {
        authLoading.value = false;

        authData.value = toTree({
          data: data,
          idField: 'id',
          parentIdField: 'parent_id'
        });

        nextTick(() => {
          const cks: number[] = [];
          eachTree(authData.value, (d) => {
            if (d.id && d.checked && !d.children?.length) {
              cks.push(d.id);
            }
          });

          checkedKeys.value = cks;
        });
      })
      .catch((exception) => {
        authLoading.value = false;
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 关闭弹窗 */
  const handleCancel = () => {
    visible.value = false;
  };

  /** 保存权限分配 */
  const save = () => {
    loading.value = true;

    const ids =
      (treeRef.value?.getCheckedKeys?.() ?? []).concat(
        treeRef.value?.getHalfCheckedKeys?.() ?? []
      ) ?? [];

    updateRole({ id: props.data?.id, permissions: ids })
      .then((message) => {
        loading.value = false;
        EleMessage.success({ message: message, plain: true });
        handleCancel();
      })
      .catch((exception) => {
        loading.value = false;
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 弹窗打开事件 */
  const handleOpen = () => {
    query();
  };
</script>
