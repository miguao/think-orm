<template>
  <ele-page class="payment-order-page">
    <order-search @search="reload" />
    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        v-model:selections="selections"
        :highlight-current-row="true"
        cache-key="paymentOrderTable"
        :bordered="false"
        :stripe="true"
        table-class="payment-order-table"
      >
        <template #toolbar>
          <div class="toolbar-actions">
            <ele-dropdown :items="batchActions" @command="handleBatchAction">
              <el-button type="primary" class="ele-btn-icon">
                批量操作
                <el-icon
                  :size="12"
                  style="vertical-align: -1px; margin-left: 4px"
                >
                  <ArrowDown />
                </el-icon>
              </el-button>
            </ele-dropdown>
          </div>
        </template>

        <template #trade_no="{ row }">
          <div class="order-cell">
            <p class="order-name">{{ row.trade_no }}</p>
            <p class="order-meta">{{ row.out_trade_no }}</p>
          </div>
        </template>

        <template #merchant="{ row }">
          [{{ row.merchant?.id }}]
          {{ row.merchant?.email ?? row.merchant?.phone }}
        </template>

        <template #channel="{ row }">
          [{{ row.channel?.plugin_identifier }}] {{ row.channel?.name }}
        </template>

        <template #bank="{ row }">
          <div style="display: flex; align-items: center; gap: 8px">
            <img
              v-if="row.bank?.icon"
              :src="UPLOAD_URL + row.bank.icon"
              :alt="row.bank?.name"
              style="width: 26px; height: 26px; border-radius: 8px"
            />

            <span>{{ row.bank?.name }}</span>
          </div>
        </template>

        <template #status="{ row }">
          <el-tag type="primary" v-if="row.status == 0">待支付</el-tag>
          <el-tag type="success" v-if="row.status == 1">已完成</el-tag>
          <el-tag type="info" v-if="row.status == 2">已关闭</el-tag>
          <el-tag type="danger" v-if="row.status == 3">已退款</el-tag>
        </template>

        <template #creation_time="{ row }">
          <div class="order-cell">
            <p class="order-name">{{ row.creation_time }}</p>
            <p class="order-meta">{{ row.creation_time }}</p>
          </div>
        </template>

        <template #action="{ row }">
          <el-link type="primary" underline="never">详情</el-link>
          <el-divider direction="vertical" />
          <ele-dropdown
            :items="[
              { title: '重新通知', command: 'password' },
              {
                title: '删除订单',
                command: 'delete',
                danger: true,
                divided: true
              }
            ]"
            style="display: inline"
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
        </template>
      </ele-pro-table>
    </ele-card>
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { ArrowDown } from '@/components/icons';
  import { type EleProTable, EleMessage } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import OrderSearch from './components/order-search.vue';
  import type { Bank, SearchParam } from '@/api/payment/bank/model';
  import { getOrderList } from '@/api/payment/order';
  import { UPLOAD_URL } from '@/config/setting';

  defineOptions({ name: 'PaymentOrder' });

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
      label: '系统订单号 / 商户订单号',
      width: 240,
      slot: 'trade_no'
    },
    {
      prop: 'subject',
      label: '商品名称',
      width: 220
    },
    {
      prop: 'merchant',
      label: '商户信息',
      width: 220,
      slot: 'merchant'
    },
    {
      prop: 'channel',
      label: '通道信息',
      slot: 'channel'
    },
    {
      prop: 'bank',
      label: '支付方式',
      width: 120,
      slot: 'bank'
    },
    {
      prop: 'amount',
      label: '订单金额',
      width: 120,
      align: 'center'
    },
    {
      prop: 'actual_amount',
      label: '实付金额',
      width: 120,
      align: 'center'
    },
    {
      prop: 'creation_time',
      label: '创建时间 / 完成时间',
      width: 180,
      slot: 'creation_time'
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
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);

  /** 表格数据源 */
  const datasource: DatasourceFunction = ({ pages, where, orders }) => {
    return getOrderList({ ...where, ...orders, ...pages });
  };

  /** 表格选中数据 */
  const selections = ref<Bank[]>([]);

  /** 当前编辑数据 */
  const current = ref<Bank | null>(null);

  /** 批量操作菜单项 */
  const batchActions = [
    { title: 'API退款', command: 'refund' },
    { title: '重新通知', command: 'notify' },
    { title: '删除订单', command: 'delete' }
  ];

  /** 批量操作处理 */
  const handleBatchAction = (command: string) => {
    if (selections.value.length === 0) {
      EleMessage.warning({ message: '请先选择要操作的数据', plain: true });
      return;
    }
    // TODO: 根据 command 执行相应的批量操作
    console.log('批量操作:', command, selections.value);
  };

  /** 搜索 */
  const reload = (where?: SearchParam) => {
    selections.value = [];
    tableRef.value?.reload?.({ page: 1, where });
  };
</script>

<style scoped>
  .order-cell {
    text-align: left;
  }

  .order-name {
    margin: 0;
    font-weight: 600;
  }

  .order-meta {
    margin: 2px 0 0;
    font-size: 12px;
    color: #8c8c8c;
  }

  :deep(.payment-order-table .el-table__row) {
    transition: transform 0.15s;
  }

  :deep(.payment-order-table .el-table__row:hover) {
    transform: translateX(3px);
  }
</style>
