<template>
  <user-search ref="searchRef" style="margin-bottom: -14px" @search="reload" />
  <ele-pro-table
    ref="tableRef"
    row-key="id"
    :columns="columns"
    :datasource="datasource"
    :show-overflow-tooltip="true"
    v-model:selections="selections"
    :highlight-current-row="true"
    :export-config="{ fileName: '用户数据', datasource: exportSource }"
    :print-config="{ datasource: exportSource }"
    :style="{ paddingBottom: '16px' }"
    cache-key="systemUserTable"
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
      <el-button
        class="ele-btn-icon"
        :icon="UploadOutlined"
        @click="openImport"
      >
        导入
      </el-button>
    </template>

    <template #status="{ row }">
      <el-switch
        size="small"
        :model-value="row.status === 1"
        @change="(checked: boolean) => editStatus(checked, row)"
      />
    </template>

    <template #action="{ row }">
      <el-link type="primary" underline="never" @click="openEdit(row)">
        修改
      </el-link>
      <el-divider direction="vertical" />
      <ele-dropdown
        :items="[
          { title: '重置密码', command: 'password' },
          { title: '删除用户', command: 'delete', danger: true, divided: true }
        ]"
        style="display: inline"
        @command="(key) => dropClick(key, row)"
      >
        <el-link type="primary" underline="never">
          <span>更多</span>
          <el-icon :size="12" style="vertical-align: -1px; margin-left: 2px">
            <ArrowDown />
          </el-icon>
        </el-link>
      </ele-dropdown>
    </template>
  </ele-pro-table>
  <user-edit
    :data="current"
    v-model="showEdit"
    :institution_id="institutionId"
    @done="reload"
  />
  <user-import v-model="showImport" @done="reload" />
</template>

<script lang="ts" setup>
  import { ref, watch } from 'vue';
  import { ElMessageBox } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import type { EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import {
    PlusOutlined,
    DeleteOutlined,
    ArrowDown,
    UploadOutlined
  } from '@/components/icons';
  import UserSearch from './user-search.vue';
  import UserEdit from './user-edit.vue';
  import UserImport from './user-import.vue';
  import { getUserList, deleteUser, updateUser } from '@/api/system/user';
  import type { SearchParam, User } from '@/api/system/user/model';

  const props = defineProps<{
    institutionId?: number;
  }>();

  /** 搜索栏实例 */
  const searchRef = ref<InstanceType<typeof UserSearch> | null>(null);

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
      prop: 'email',
      label: '登录邮箱',
      minWidth: 180
    },
    {
      prop: 'nickname',
      label: '用户昵称',
      minWidth: 110
    },
    {
      prop: 'login_ip',
      label: '登录IP',
      width: 160,
      align: 'center'
    },
    {
      prop: 'last_login_ip',
      label: '上次登录IP',
      width: 160,
      align: 'center'
    },
    {
      prop: 'login_time',
      label: '登录时间',
      width: 180,
      align: 'center'
    },
    {
      prop: 'last_login_time',
      label: '上次登录时间',
      width: 180,
      align: 'center'
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      sortable: 'custom',
      width: 180,
      align: 'center'
    },
    {
      prop: 'status',
      label: '状态',
      width: 90,
      align: 'center',
      slot: 'status',
      formatter: (row) => (row.status == 0 ? '启用' : '停用')
    },
    {
      columnKey: 'action',
      label: '操作',
      width: 128,
      align: 'center',
      fixed: 'right',
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);

  /** 表格选中数据 */
  const selections = ref<User[]>([]);

  /** 当前编辑数据 */
  const current = ref<User | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 是否显示用户导入弹窗 */
  const showImport = ref(false);

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getUserList({
      ...where,
      ...orders,
      ...pages,
      'equal-institution_id': props.institutionId
    });
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /** 打开编辑弹窗 */
  const openEdit = (row?: User) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 打开编辑弹窗 */
  const openImport = () => {
    showImport.value = true;
  };

  /** 删除 */
  const remove = (row?: User) => {
    const rows = row == null ? selections.value : [row];
    if (!rows.length) {
      EleMessage.error({ message: '请至少选择一条数据', plain: true });
      return;
    }

    ElMessageBox.confirm(
      '确定要删除“' + rows.map((d) => d.nickname).join(', ') + '”吗?',
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

      deleteUser(ids)
        .then((message) => {
          loading.close();
          EleMessage.success({ message: message, plain: true });
          reload();
        })
        .catch((e) => {
          loading.close();
          EleMessage.error({ message: e.message, plain: true });
        });
    });
  };

  /** 修改用户状态 */
  const editStatus = (checked: boolean, row: User) => {
    const status = checked ? 1 : 0;

    updateUser({ id: row.id, status: status })
      .then((msg) => {
        row.status = status;
        EleMessage.success({ message: msg, plain: true });
      })
      .catch((e) => {
        EleMessage.error({ message: e.message, plain: true });
      });
  };

  /** 下拉菜单点击事件 */
  const dropClick = (key: any, row: User) => {
    if (key === 'password') {
      ElMessageBox.prompt(`请输入用户"${row.nickname}"的新密码：`, '重置密码', {
        inputPattern: /^[\S]{5,18}$/,
        inputErrorMessage: '密码必须为5-18位非空白字符',
        draggable: true
      }).then(({ value }) => {
        const loading = EleMessage.loading({
          message: '请求中..',
          plain: true
        });

        updateUser({ id: row.id, password: value })
          .then((msg) => {
            loading.close();
            EleMessage.success({ message: msg, plain: true });
          })
          .catch((e) => {
            loading.close();
            EleMessage.error({ message: e.message, plain: true });
          });
      });
    } else if (key === 'delete') {
      remove(row);
    }
  };

  // 监听机构 id 变化
  watch(
    () => props.institutionId,
    () => {
      searchRef.value?.resetFields?.();
      reload({});
    }
  );

  /** 导出和打印全部数据的数据源 */
  const exportSource: DatasourceFunction = ({ where, orders }) => {
    return getUserList({
      ...where,
      ...orders,
      'equal-institution_id': props.institutionId
    });
  };
</script>
