<template>
  <ele-page>
    <config-search @search="reload" />

    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        v-model:selections="selections"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        :highlight-current-row="true"
        cache-key="systemConfigTable"
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

        <template #key="{ row }">
          <el-tag type="success">{{ row.key }}</el-tag>
        </template>

        <template #public="{ row }">
          <el-tag type="info" v-if="row.public === 0">私有</el-tag>
          <el-tag type="success" v-if="row.public === 1">公开</el-tag>
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
          <el-link type="primary" underline="never" @click="openSetting(row)">
            配置
          </el-link>
          <el-divider direction="vertical" />
          <el-link type="primary" underline="never" @click="openEdit(row)">
            修改
          </el-link>
          <el-divider direction="vertical" />
          <el-link type="danger" underline="never" @click="remove(row)">
            删除
          </el-link>
        </template>
      </ele-pro-table>

      <!-- 编辑弹窗 -->
      <config-edit v-model="showEdit" :data="current" @done="reload" />
      <!-- 配置设置弹窗 -->
      <config-setting v-model="showSetting" :data="current" @done="reload" />
    </ele-card>
  </ele-page>
</template>

<script lang="ts" setup>
  import {
    deleteConfig,
    getConfigList,
    updateConfig
  } from '@/api/system/config';
  import type { Config, SearchParam } from '@/api/system/config/model';
  import { EleMessage, EleProTable } from 'ele-admin-plus';
  import {
    Columns,
    DatasourceFunction
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { ref } from 'vue';
  import { PlusOutlined, DeleteOutlined } from '@/components/icons';
  import ConfigSearch from './components/config-search.vue';
  import ConfigEdit from './components/config-edit.vue';
  import ConfigSetting from './components/config-setting.vue';
  import { ElMessageBox } from 'element-plus';

  defineOptions({ name: 'SystemConfig' });

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
      label: '配置名称',
      width: 180
    },
    {
      prop: 'key',
      label: '配置键',
      width: 200,
      align: 'center',
      slot: 'key'
    },
    {
      prop: 'public',
      label: '是否公开',
      width: 160,
      align: 'center',
      slot: 'public'
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      width: 200
    },
    {
      prop: 'status',
      label: '状态',
      width: 160,
      align: 'center',
      slot: 'status'
    },
    {
      columnKey: 'action',
      label: '操作',
      align: 'center',
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getConfigList({ ...where, ...orders, ...pages });
  };

  /** 表格选中数据 */
  const selections = ref<Config[]>([]);

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /** 当前编辑数据 */
  const current = ref<Config | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 打开编辑弹窗 */
  const openEdit = (row?: Config) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 是否显示配置设置弹窗 */
  const showSetting = ref(false);

  /** 打开配置设置弹窗 */
  const openSetting = (row?: Config) => {
    current.value = row ?? null;
    showSetting.value = true;
  };

  /**
   * 更新配置状态
   * @param id 配置ID
   * @param status 配置状态
   */
  const updateStatus = (id: number, status: number) => {
    updateConfig({ id, status })
      .then((message) => {
        EleMessage.success({ message: message, plain: true });
      })
      .catch((exception) => {
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 删除单个 */
  const remove = (row?: Config) => {
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

      deleteConfig(ids)
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
