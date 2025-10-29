<template>
  <ele-page>
    <application-search @search="reload" />
    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        v-model:selections="selections"
        :highlight-current-row="true"
        cache-key="merchantRoleTable"
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
          <el-link type="danger" underline="never" @click="remove(row)">
            删除
          </el-link>
        </template>
      </ele-pro-table>
    </ele-card>

    <!-- 编辑弹窗 -->
    <application-edit v-model="showEdit" :data="current" @done="reload" />
  </ele-page>
</template>
<script lang="ts" setup>
  import { ref } from 'vue';
  import { EleMessage, type EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import type {
    Application,
    SearchParam
  } from '@/api/merchant/application/model';
  import {
    deleteApplication,
    getApplicationList,
    updateApplication
  } from '@/api/merchant/application';
  import applicationSearch from './components/application-search.vue';
  import applicationEdit from './components/application-edit.vue';
  import { PlusOutlined, DeleteOutlined } from '@/components/icons';
  import { ElMessageBox } from 'element-plus';

  defineOptions({ name: 'MerchantApplication' });

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
      prop: 'application_no',
      label: '应用号',
      width: 160
    },
    {
      prop: 'name',
      label: '应用名称',
      minWidth: 120
    },
    {
      prop: 'today_revenue',
      label: '今日收款',
      slot: 'today_revenue'
    },
    {
      prop: 'yesterday_revenue',
      label: '昨日收款',
      slot: 'yesterday_revenue'
    },
    {
      prop: 'total_revenue',
      label: '总收款',
      slot: 'total_revenue'
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

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getApplicationList({ ...where, ...orders, ...pages });
  };

  /** 表格选中数据 */
  const selections = ref<Application[]>([]);

  /** 当前编辑数据 */
  const current = ref<Application | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 打开编辑弹窗 */
  const openEdit = (row?: Application) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /**
   * 更新应用状态
   * @param id 应用ID
   * @param status 应用状态
   */
  const updateStatus = (id: number, status: number) => {
    updateApplication({ id, status })
      .then((message) => {
        EleMessage.success({ message: message, plain: true });
      })
      .catch((exception) => {
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 删除单个 */
  const remove = (row?: Application) => {
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

      deleteApplication(ids)
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
