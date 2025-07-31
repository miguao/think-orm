<template>
  <ele-page>
    <permission-search @search="reload" />

    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        sticky
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        :highlight-current-row="true"
        :export-config="{ fileName: '权限数据' }"
        :default-expand-all="false"
        :pagination="false"
        cache-key="systemPermissionTable"
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

        <template #title="{ row }">
          <el-icon
            v-if="row.icon"
            :size="15"
            style="margin-right: 8px; vertical-align: -2px"
          >
            <component :is="row.icon" />
          </el-icon>
          <span>{{ row.name }}</span>
        </template>

        <template #type="{ row }">
          <el-tag
            v-if="isExternalLink(row.path)"
            size="small"
            type="danger"
            :disable-transitions="true"
          >
            外链
          </el-tag>
          <el-tag
            v-else-if="isExternalLink(row.component)"
            size="small"
            type="warning"
            :disable-transitions="true"
          >
            内嵌
          </el-tag>
          <el-tag
            v-else-if="isDirectory(row)"
            size="small"
            :disable-transitions="true"
          >
            目录
          </el-tag>
          <el-tag
            v-else-if="row.type === 0"
            size="small"
            :disable-transitions="true"
          >
            目录
          </el-tag>
          <el-tag
            v-else-if="row.type === 1"
            size="small"
            type="info"
            :disable-transitions="true"
          >
            菜单
          </el-tag>
          <el-tag
            v-else-if="row.type === 2"
            size="small"
            type="success"
            :disable-transitions="true"
          >
            API路由
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

        <template #status="{ row }">
          <el-switch v-model="row.status" :active-value="1" :inactive-value="0" @change="(value: number) => updateStatus(row.id, value)" />
        </template>
      </ele-pro-table>
    </ele-card>

    <permission-edit
      ref="menuEditRef"
      v-model="showEdit"
      :data="current"
      :parent-id="parentId"
      @done="handleMenuEditDone"
    />
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { ElMessageBox } from 'element-plus';
  import { EleMessage, isExternalLink, toTree } from 'ele-admin-plus';
  import type { EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import {
    PlusOutlined,
    ColumnHeightOutlined,
    VerticalAlignMiddleOutlined
  } from '@/components/icons';
  import PermissionSearch from './components/permission-search.vue';
  import PermissionEdit from './components/permission-edit.vue';
  import { deletePermission, getPermissionList, updatePermission } from '@/api/system/permission';
  import type { Permission, SearchParam } from '@/api/system/permission/model';

  defineOptions({ name: 'SystemPermission' });

  /** 表格实例 */
  const tableRef = ref<InstanceType<typeof EleProTable> | null>(null);

  /** 表格列配置 */
  const columns = ref<Columns>([
    {
      prop: 'name',
      label: '权限名称',
      slot: 'title',
      minWidth: 160
    },
    {
      prop: 'path',
      label: '访问路径',
      minWidth: 160
    },
    {
      prop: 'component',
      label: '组件路径',
      minWidth: 160
    },
    {
      prop: 'type',
      label: '类型',
      width: 100,
      align: 'center',
      slot: 'type',
      formatter: (row) =>
        ['菜单', '按钮', '外链', '内嵌', '目录'][
          isExternalLink(row.path)
            ? 2
            : isExternalLink(row.component)
              ? 3
              : isDirectory(row)
                ? 4
                : row.type
        ]
    },
    {
      prop: 'sort',
      label: '排序',
      width: 100,
      align: 'center'
    },
    {
      prop: 'status',
      label: '状态',
      width: 100,
      align: 'center',
      slot: 'status'
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
  const current = ref<Permission | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 上级菜单id */
  const parentId = ref<number>();

  /** 表格数据源 */
  const datasource: DatasourceFunction = async ({ where }) => {
    const data = await getPermissionList({ ...where });
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
  const openEdit = (row?: Permission | null, id?: number) => {
    current.value = row ?? null;
    parentId.value = id;
    showEdit.value = true;
  };

  /**
   * 更新权限状态
   * @param id 权限ID
   * @param status 权限状态
   */
  const updateStatus = (id: number, status: number) => {
    updatePermission({ id, status }).then((message) => {
      EleMessage.success({ message: message, plain: true });
    }).catch((exception) => {
      EleMessage.error({ message: exception.message, plain: true });
    });
  };

  /** 删除单个 */
  const remove = (row: Permission) => {
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

      deletePermission(row.id)
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
  
  /** 展开全部 */
  const expandAll = () => {
    tableRef.value?.toggleRowExpansionAll?.(true);
  };

  /** 折叠全部 */
  const foldAll = () => {
    tableRef.value?.toggleRowExpansionAll?.(false);
  };

  /** 判断是否是目录 */
  const isDirectory = (d: Permission) => {
    return !!d.children?.length && !d.component;
  };

  /** 菜单编辑完成事件 */
  const handleMenuEditDone = () => {
    reload();
  };
</script>
