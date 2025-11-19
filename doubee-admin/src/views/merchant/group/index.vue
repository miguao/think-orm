<template>
  <ele-page>
    <!-- 搜索表单 -->
    <group-search @search="reload" />
    <ele-card :body-style="{ paddingTop: '8px' }">
      <!-- 表格 -->
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        v-model:selections="selections"
        :highlight-current-row="true"
        cache-key="merchantGroupTable"
      >
        <template #toolbar>
          <el-button
            type="primary"
            class="ele-btn-icon"
            :icon="PlusOutlined"
            @click="openEdit()"
          >
            添加
          </el-button>
          <el-button
            type="danger"
            class="ele-btn-icon"
            :icon="DeleteOutlined"
            @click="remove()"
          >
            删除
          </el-button>
        </template>

        <template #status="{ row }">
          <el-switch
            v-model="row.status"
            :active-value="1"
            :inactive-value="0"
            @change="(value: number) => updateStatus(row.id, value)"
          />
        </template>

        <template #action="{ row }">
          <el-link type="primary" underline="never" @click="openEdit(row)">
            修改
          </el-link>
          <el-divider direction="vertical" />
          <el-link type="primary" underline="never" @click="openAuth(row)">
            分配权限
          </el-link>
          <el-divider direction="vertical" />
          <el-link type="danger" underline="never" @click="remove(row)">
            删除
          </el-link>
        </template>
      </ele-pro-table>
    </ele-card>

    <!-- 编辑弹窗 -->
    <group-edit v-model="showEdit" :data="current" @done="reload" />

    <!-- 权限分配弹窗 -->
    <group-auth v-model="showAuth" :data="current" />
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { ElMessageBox } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import type { EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { PlusOutlined, DeleteOutlined } from '@/components/icons';
  import GroupSearch from './components/group-search.vue';
  import GroupEdit from './components/group-edit.vue';
  import type { Group, SearchParam } from '@/api/merchant/group/model';
  import { deleteGroup, getGroupList, updateGroup } from '@/api/merchant/group';
  import GroupAuth from './components/group-auth.vue';

  defineOptions({ name: 'MerchantGroup' });

  /** 表格实例 */
  const tableRef = ref<InstanceType<typeof EleProTable> | null>(null);

  /** 表格列配置 */
  const columns = ref<Columns>([
    {
      type: 'selection',
      columnKey: 'selection',
      width: 50,
      align: 'center'
    },
    {
      prop: 'name',
      label: '用户组名称',
      minWidth: 120
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      width: 180
    },
    {
      prop: 'status',
      label: '状态',
      width: 100,
      align: 'center',
      slot: 'status'
    },
    {
      columnKey: 'action',
      label: '操作',
      width: 200,
      align: 'center',
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);

  /** 表格选中数据 */
  const selections = ref<Group[]>([]);

  /** 当前编辑数据 */
  const current = ref<Group | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 是否显示权限分配弹窗 */
  const showAuth = ref(false);

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getGroupList({ ...where, ...orders, ...pages });
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /** 打开编辑弹窗 */
  const openEdit = (row?: Group) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 打开权限分配弹窗 */
  const openAuth = (row?: Group) => {
    current.value = row ?? null;
    showAuth.value = true;
  };

  /**
   * 更新用户组状态
   * @param id 用户组ID
   * @param status 用户组状态
   */
  const updateStatus = (id: number, status: number) => {
    updateGroup({ id, status })
      .then((message) => {
        EleMessage.success({ message: message, plain: true });
      })
      .catch((exception) => {
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 删除单个 */
  const remove = (row?: Group) => {
    const rows = row == null ? selections.value : [row];
    if (!rows.length) {
      EleMessage.error({ message: '请至少选择一条数据', plain: true });
      return;
    }

    ElMessageBox.confirm(
      '确定要删除“' + rows.map((d) => d.name).join(', ') + '”吗?',
      '系统提示',
      { type: 'warning', draggable: true }
    ).then(() => {
      const loading = EleMessage.loading({
        message: '请求中..',
        plain: true
      });

      const ids = rows
        .map((d) => d.id)
        .filter((id): id is number => id !== undefined);

      deleteGroup(ids)
        .then((message) => {
          loading.close();
          EleMessage.success({ message: message, plain: true });
          reload();
        })
        .catch((exception) => {
          loading.close();
          EleMessage.error({ message: exception.message, plain: true });
        });
    });
  };
</script>
