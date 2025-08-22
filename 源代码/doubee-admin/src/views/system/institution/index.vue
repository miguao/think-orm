<template>
  <ele-page>
    <institution-search @search="reload" />

    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        sticky
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        :highlight-current-row="true"
        :export-config="{ fileName: '机构数据' }"
        :default-expand-all="true"
        :pagination="false"
        cache-key="systemInstitutionTable"
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
            class="ele-btn-icon"
            :icon="ColumnHeightOutlined"
            @click="expandAll"
          >
            展开全部
          </el-button>
          <el-button
            class="ele-btn-icon"
            :icon="VerticalAlignMiddleOutlined"
            @click="foldAll"
          >
            折叠全部
          </el-button>
        </template>

        <template #type="{ row }">
          <el-tag v-if="row.type === 0" type="primary" size="small">
            公司
          </el-tag>
          <el-tag v-else-if="row.type === 1" type="warning" size="small">
            子公司
          </el-tag>
          <el-tag v-else-if="row.type === 2" type="success" size="small">
            部门
          </el-tag>
          <el-tag v-else-if="row.type === 3" type="info" size="small">
            小组
          </el-tag>
        </template>

        <template #action="{ row }">
          <el-link
            type="primary"
            underline="never"
            @click="openEdit(null, row.id)"
          >
            添加
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
    </ele-card>

    <!-- 编辑弹窗 -->
    <institution-edit
      v-model="showEdit"
      :data="current"
      :institution-id="parentId"
      done="reload"
    />
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import type {
    Institution,
    SearchParam
  } from '@/api/system/institution/model';
  import { EleMessage, toTree } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import {
    PlusOutlined,
    ColumnHeightOutlined,
    VerticalAlignMiddleOutlined
  } from '@/components/icons';
  import type { EleProTable } from 'ele-admin-plus/es/core-components';
  import {
    getInstitutionList,
    deleteInstitution
  } from '@/api/system/institution';
  import { ElMessageBox } from 'element-plus';
  import InstitutionSearch from './components/institution-search.vue';
  import InstitutionEdit from './components/institution-edit.vue';

  defineOptions({ name: 'SystemInstitution' });

  /** 表格实例 */
  const tableRef = ref<InstanceType<typeof EleProTable> | null>(null);

  /** 表格列配置 */
  const columns = ref<Columns>([
    {
      prop: 'name',
      label: '机构名称',
      minWidth: 160
    },
    {
      prop: 'type',
      label: '机构类型',
      minWidth: 100,
      align: 'center',
      slot: 'type'
    },
    {
      prop: 'sort',
      label: '排序号',
      minWidth: 100,
      align: 'center'
    },
    {
      prop: 'creation_time',
      label: '创建时间',
      width: 180,
      align: 'center'
    },
    {
      columnKey: 'action',
      label: '操作',
      width: 180,
      align: 'center',
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);

  /** 当前编辑数据 */
  const current = ref<Institution | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 上级机构id */
  const parentId = ref<number>();

  /** 表格数据源 */
  const datasource: DatasourceFunction = async ({ where, orders }) => {
    const data = await getInstitutionList({ ...where, ...orders });
    return toTree({
      data,
      idField: 'id',
      parentIdField: 'parent_id'
    });
  };

  /** 刷新表格 */
  const reload = (where?: SearchParam) => {
    tableRef.value?.reload?.({ where });
  };

  /** 打开编辑弹窗 */
  const openEdit = (row?: Institution | null, id?: number) => {
    current.value = row ?? null;
    parentId.value = id;
    showEdit.value = true;
  };

  /** 删除单个 */
  const remove = (row: Institution) => {
    if (row.children?.length) {
      EleMessage.error({ message: '请先删除子节点', plain: true });
      return;
    }

    ElMessageBox.confirm('确定要删除“' + row.name + '”吗?', '系统提示', {
      type: 'warning',
      draggable: true
    }).then(() => {
      const loading = EleMessage.loading({
        message: '请求中..',
        plain: true
      });

      deleteInstitution(row.id)
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

  /** 展开全部 */
  const expandAll = () => {
    tableRef.value?.toggleRowExpansionAll?.(true);
  };

  /** 折叠全部 */
  const foldAll = () => {
    tableRef.value?.toggleRowExpansionAll?.(false);
  };
</script>
