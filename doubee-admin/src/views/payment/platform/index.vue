<template>
  <ele-page>
    <platform-search @search="reload" />
    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        v-model:selections="selections"
        :highlight-current-row="true"
        cache-key="paymentPlatformTable"
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
    <platform-edit v-model="showEdit" :data="current" @done="reload" />
  </ele-page>
</template>

<script lang="ts" setup>
  import {
    PlusOutlined,
    DeleteOutlined,
    UploadOutlined
  } from '@/components/icons';
  import { ref } from 'vue';
  import { EleMessage, type EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { ElMessageBox } from 'element-plus';
  import PlatformSearch from './components/platform-search.vue';
  import { deletePlatform, getPlatformList } from '@/api/payment/platform';
  import { Platform, type SearchParam } from '@/api/payment/platform/model';
  import platformEdit from './components/platform-edit.vue';

  defineOptions({ name: 'PaymentPlatform' });

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
      label: '平台名称',
      minWidth: 120
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      width: 180
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
    return getPlatformList({ ...where, ...orders, ...pages });
  };

  /** 表格选中数据 */
  const selections = ref<Platform[]>([]);

  /** 当前编辑数据 */
  const current = ref<Platform | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 打开编辑弹窗 */
  const openEdit = (row?: Platform) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /** 删除单个 */
  const remove = (row?: Platform) => {
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

      deletePlatform(ids)
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

  /** 打开导入弹窗 */
  const openImport = () => {
    EleMessage.info({ message: '导入功能尚未实现', plain: true });
  };
</script>
