<template>
  <ele-page class="application">
    <application-search @search="reload" />

    <ele-card :body-style="{ paddingTop: '8px' }">
      <ele-pro-table
        ref="tableRef"
        row-key="id"
        :columns="columns"
        :datasource="datasource"
        :show-overflow-tooltip="true"
        :highlight-current-row="true"
        cache-key="merchantRoleTable"
      >
        <template #toolbar>
          <el-button
            type="primary"
            class="ele-btn-icon"
            :icon="PlusOutlined"
            @click="openCreateApplication"
          >
            创建应用
          </el-button>
        </template>

        <template #application="{ row }">
          <div class="application-name-cell">
            <div class="application-name">{{ row.name }}</div>
            <div class="application-no-wrapper">
              <span class="application-no">{{ row.application_no }}</span>
              <el-button
                v-if="row.application_no"
                text
                type="primary"
                size="small"
                class="copy-btn"
                :icon="CopyDocument"
                @click.stop="copyApplicationNo(row.application_no)"
              >
              </el-button>
            </div>
          </div>
        </template>

        <template #status="{ row }">
          <el-tag type="success" effect="dark" v-if="row.status === 1">
            运行中
          </el-tag>
          <el-tag type="danger" effect="dark" v-else> 已停止 </el-tag>
        </template>

        <template #action="{ row }">
          <el-link type="primary" underline="never" @click="openDetails(row)">
            详情
          </el-link>
        </template>
      </ele-pro-table>
    </ele-card>

    <!-- 创建应用 -->
    <application-create
      v-model="applicationCreateVisible"
      :data="applicationCreateData"
    />

    <!-- 应用详情 -->
    <application-details
      v-model="applicationDetailsVisible"
      :data="applicationDetailsData"
    />
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { CopyDocument } from '@element-plus/icons-vue';
  import { copyText } from 'ele-admin-plus/es/utils/common';
  import ApplicationSearch from './components/application-search.vue';
  import type { Application, SearchParam } from '@/api/application/model';
  import { EleMessage, type EleProTable } from 'ele-admin-plus';
  import type {
    DatasourceFunction,
    Columns
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { PlusOutlined } from '@/components/icons';
  import { getApplicationList } from '@/api/application';
  import ApplicationCreate from './components/application-create.vue';
  import ApplicationDetails from './components/application-details.vue';

  defineOptions({ name: 'Application' });

  /** 表格实例 */
  const tableRef = ref<InstanceType<typeof EleProTable> | null>(null);

  /** 表格列配置 */
  const columns = ref<Columns>([
    {
      label: '应用名称/ID',
      width: 220,
      slot: 'application',
      minWidth: 200
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

  const reload = (params: SearchParam) => {
    tableRef.value?.reload({ where: params });
  };

  /** 复制商户号 */
  const copyApplicationNo = async (merchantNo: number) => {
    try {
      await copyText(String(merchantNo));
      EleMessage.success({ message: '已复制到剪贴板', plain: true });
    } catch (error: any) {
      EleMessage.error({ message: error.message || '复制失败', plain: true });
    }
  };

  /** 创建弹窗是否打开 */
  const applicationCreateVisible = ref(false);

  /** 创建数据 */
  const applicationCreateData = ref<Application | null>(null);

  /** 打开创建应用 */
  const openCreateApplication = () => {
    applicationCreateVisible.value = true;
  };

  /** 详情弹窗是否打开 */
  const applicationDetailsVisible = ref(false);

  /** 详情数据 */
  const applicationDetailsData = ref<Application | null>(null);

  /** 打开详情 */
  const openDetails = (row: Application) => {
    applicationDetailsVisible.value = true;
    applicationDetailsData.value = row;
  };
</script>

<style lang="scss" scoped>
  .application {
    .application-name-cell {
      .application-name {
        font-size: 14px;
        font-weight: 500;
        color: #303133;
        margin-bottom: 4px;
        line-height: 1.5;
      }

      .application-no-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;

        .application-no {
          font-size: 12px;
          color: #909399;
          line-height: 1.5;
        }

        .copy-btn {
          padding: 0;
          height: auto;
          font-size: 12px;
          line-height: 1.5;

          :deep(.el-icon) {
            font-size: 12px;
            margin-right: 2px;
          }
        }
      }
    }
  }
</style>
