<template>
  <ele-page class="merchant-user-page">
    <merchant-search @search="reload" />
    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        v-model:selections="selections"
        :highlight-current-row="true"
        cache-key="merchantUserTable"
        :bordered="false"
        :stripe="true"
        table-class="merchant-user-table"
      >
        <template #toolbar>
          <div class="toolbar-actions">
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
          </div>
        </template>
        <template #merchant_no="{ row }">
          <div class="user-cell">
            <p class="user-name">{{ row.merchant_no }}</p>
            <p class="user-meta">{{ row.email || '暂无邮箱' }}</p>
          </div>
        </template>

        <template #role="{ row }">
          <el-tag type="info" effect="dark">{{
            row.merchantGroup?.name
          }}</el-tag>
        </template>

        <template #status="{ row }">
          <el-switch
            size="small"
            :model-value="row.status === 1"
            @change="(checked: boolean) => editStatus(checked, row)"
          />
        </template>

        <template #action="{ row }">
          <div class="action-links">
            <el-link type="primary" underline="never" @click="openEdit(row)"
              >修改</el-link
            >
            <el-divider direction="vertical" />
            <ele-dropdown
              :items="[
                { title: '重置密码', command: 'password' },
                {
                  title: '删除用户',
                  command: 'delete',
                  danger: true,
                  divided: true
                }
              ]"
              style="display: inline"
              @command="(key) => dropClick(key, row)"
            >
              <el-link type="primary" underline="never">
                <span>更多</span>
                <el-icon
                  :size="12"
                  style="vertical-align: -1px; margin-left: 2px"
                >
                  <ArrowDown />
                </el-icon>
              </el-link>
            </ele-dropdown>
          </div>
        </template>
      </ele-pro-table>
    </ele-card>

    <!-- 编辑弹窗 -->
    <merchant-edit v-model="showEdit" :data="current" @done="reload" />
  </ele-page>
</template>
<script lang="ts" setup>
  import { ref } from 'vue';
  import { EleMessage, type EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { PlusOutlined, DeleteOutlined, ArrowDown } from '@/components/icons';
  import {
    deleteMerchant,
    getMerchantList,
    updateMerchant
  } from '@/api/merchant/user';
  import type { Merchant, SearchParam } from '@/api/merchant/user/model';
  import { ElMessageBox } from 'element-plus';
  import merchantSearch from './components/merchant-search.vue';
  import merchantEdit from './components/merchant-edit.vue';

  defineOptions({ name: 'MerchantUser' });

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
      prop: 'merchant_no',
      label: '商户 / 邮箱',
      width: 210,
      slot: 'merchant_no'
    },
    {
      prop: 'phone',
      label: '手机号码',
      width: 120,
      align: 'center'
    },
    {
      prop: 'email',
      label: '邮箱号码',
      width: 180,
      align: 'center'
    },
    {
      prop: 'role',
      label: '角色',
      width: 150,
      slot: 'role',
      align: 'center'
    },
    {
      prop: 'balance',
      label: '账户余额',
      width: 120,
      align: 'center'
    },
    {
      prop: 'freeze_balance',
      label: '冻结金额',
      width: 120,
      align: 'center'
    },
    {
      prop: 'login_ip',
      label: '登录IP',
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
      prop: 'creation_time',
      label: '注册时间',
      width: 180,
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

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getMerchantList({ ...where, ...orders, ...pages });
  };

  /** 表格选中数据 */
  const selections = ref<Merchant[]>([]);

  /** 当前编辑数据 */
  const current = ref<Merchant | null>(null);

  /** 是否显示编辑弹窗 */
  const showEdit = ref(false);

  /** 打开编辑弹窗 */
  const openEdit = (row?: Merchant) => {
    current.value = row ?? null;
    showEdit.value = true;
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };

  /** 删除用户 */
  const remove = (row?: Merchant) => {
    const rows = row == null ? selections.value : [row];
    if (!rows.length) {
      EleMessage.error({ message: '请至少选择一条数据', plain: true });
      return;
    }

    ElMessageBox.confirm(
      '确定要删除“' + rows.map((d) => d.merchant_no).join(', ') + '”吗?',
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

      deleteMerchant(ids)
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

  /** 修改用户状态 */
  const editStatus = (checked: boolean, row: Merchant) => {
    const status = checked ? 1 : 0;

    updateMerchant({ id: row.id, status: status })
      .then((message) => {
        row.status = status;
        EleMessage.success({ message: message, plain: true });
      })
      .catch((exception) => {
        EleMessage.error({ message: exception.message, plain: true });
      });
  };

  /** 下拉菜单点击事件 */
  const dropClick = (key: any, row: Merchant) => {
    switch (key) {
      case 'password':
        ElMessageBox.prompt(
          `请输入商户编号"${row.merchant_no}"的新密码：`,
          '重置密码',
          {
            inputPattern: /^[\S]{5,18}$/,
            inputErrorMessage: '密码必须为5-18位非空白字符',
            draggable: true
          }
        ).then(({ value }) => {
          const loading = EleMessage.loading({
            message: '请求中..',
            plain: true
          });

          updateMerchant({ id: row.id, password: value })
            .then((message) => {
              loading.close();
              EleMessage.success({ message: message, plain: true });
            })
            .catch((exception) => {
              loading.close();
              EleMessage.error({ message: exception.message, plain: true });
            });
        });
        break;
      case 'delete':
        remove(row);
        break;
    }
  };
</script>

<style scoped>
  .merchant-user-page :deep(.ele-page-body) {
    padding-top: 12px;
  }

  .toolbar-actions {
    display: flex;
    gap: 8px;
  }

  .user-cell {
    text-align: left;
  }

  .user-name {
    margin: 0;
    font-weight: 600;
  }

  .user-meta {
    margin: 2px 0 0;
    font-size: 12px;
    color: #8c8c8c;
  }

  :deep(.merchant-user-table .el-table__row) {
    transition: transform 0.15s;
  }

  :deep(.merchant-user-table .el-table__row:hover) {
    transform: translateX(3px);
  }

  .action-links {
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>
