<template>
  <ele-drawer
    v-model="visible"
    :title="'字段配置: ' + props.data?.name + '(' + props.data?.key + ')'"
    size="100%"
    :destroy-on-close="true"
    :before-close="beforeClose"
  >
    <ele-pro-table
      ref="tableRef"
      row-key="id"
      v-model:selections="selections"
      :columns="columns"
      :datasource="datasource"
      highlight-current-row
      :bottom-line="false"
      cache-key="systemConfigFieldTable"
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

      <!-- 组件类型 -->
      <template #component_type="{ row }">
        <dict-data
          type="tag"
          code="component_type"
          v-model="row.component_type"
        />
      </template>

      <!-- 数据模式 -->
      <template #data_mode="{ row }">
        <el-tag type="info" v-if="row.data_mode === 0">字典模式</el-tag>
        <el-tag type="success" v-if="row.data_mode === 1">自定义模式</el-tag>
      </template>

      <!-- 是否必填 -->
      <template #required="{ row }">
        <el-tag type="danger" v-if="row.required === 1">必填项</el-tag>
        <el-tag type="info" v-if="row.required === 0">非必填项</el-tag>
      </template>

      <!-- 操作 -->
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

    <!-- 编辑弹窗 -->
    <config-field-edit v-model="showEdit" :data="current" @done="reload" />
  </ele-drawer>
</template>

<script lang="ts" setup>
  import { deleteField, getFieldList } from '@/api/system/config';
  import { Config, SearchParam } from '@/api/system/config/model';
  import { DeleteOutlined, PlusOutlined } from '@/components/icons';
  import type {
    Columns,
    DatasourceFunction
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { ref, watch } from 'vue';
  import { EleMessage, EleProTable } from 'ele-admin-plus';
  import { ElMessageBox } from 'element-plus';
  import ConfigFieldEdit from './config-field-edit.vue';

  const props = defineProps<{
    data: Config;
  }>();

  const emit = defineEmits(['close', 'update:modelValue']);

  /** 弹窗是否打开 */
  const visible = defineModel({ type: Boolean });

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
      prop: 'key',
      label: '配置键'
    },
    {
      prop: 'title',
      label: '配置标题'
    },
    {
      prop: 'component_type',
      label: '组件类型',
      slot: 'component_type'
    },
    {
      prop: 'data_mode',
      label: '数据模式',
      slot: 'data_mode'
    },
    {
      prop: 'required',
      label: '是否必填',
      slot: 'required'
    },
    {
      prop: 'remark',
      label: '备注信息'
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      width: 180
    },
    {
      prop: 'update_time',
      label: '更新时间',
      width: 180
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
    return getFieldList({
      ...where,
      ...orders,
      ...pages,
      'equal-config_id': props.data.id
    });
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

  /** 删除单个 */
  const remove = (row?: Config) => {
    const rows = row == null ? selections.value : [row];
    if (!rows.length) {
      EleMessage.error({ message: '请至少选择一条数据', plain: true });
      return;
    }

    ElMessageBox.confirm('确定要删除该条数据吗?', '系统提示', {
      type: 'warning',
      draggable: true
    }).then(() => {
      const loading = EleMessage.loading({
        message: '请求中..',
        plain: true
      });

      const ids = rows
        .map((d) => d.id)
        .filter((id): id is number => id !== undefined);

      deleteField(ids)
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

  /** 关闭弹窗 */
  const beforeClose = (done: () => void) => {
    emit('close');
    done();
  };

  watch(visible, () => {
    if (visible.value) {
      if (props.data) {
        reload();
      }
    }
  });
</script>
