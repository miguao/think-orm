<template>
  <ele-modal v-model="visible" title="应用详情" width="800px">
    <div class="application-details">
      <ele-alert
        v-if="props.data?.status === 1"
        type="success"
        title="应用状态：运行中"
        style="margin-bottom: 15px"
        show-icon
      />

      <ele-alert
        v-else
        type="error"
        title="应用状态：已停止"
        style="margin-bottom: 15px"
        show-icon
      />

      <div class="info-item">
        <div class="info-label">应用名称</div>
        <div class="info-value">{{ props.data?.name }}</div>
      </div>

      <div class="info-item">
        <div class="info-label">应用模式</div>
        <div class="info-value">自研开发</div>
      </div>

      <div class="info-item">
        <div class="info-label">创建时间</div>
        <div class="info-value info-value-with-action">
          <span>{{ props.data?.creation_time }}</span>
          <el-button text type="danger" size="small" class="delete-btn">
            注销应用
          </el-button>
        </div>
      </div>

      <el-divider border-style="dashed" />

      <ele-tabs
        v-model="tabsActive"
        :items="[
          { name: 'development', label: 'API信息' },
          { name: 'payment', label: '渠道配置' },
          { name: 'advanced', label: '高级设置' }
        ]"
      >
        <template #development>
          <div style="padding: 20px 0">
            <div class="info-item">
              <div class="info-label">应用ID</div>
              <div class="info-value">
                <div class="secret-wrapper">
                  <el-input
                    v-if="props.data?.application_no"
                    :value="props.data.application_no"
                    readonly
                    class="secret-input"
                  >
                    <template #append>
                      <ele-tooltip
                        content="复制"
                        placement="top"
                        :showAfter="1000"
                      >
                        <el-button
                          :icon="CopyDocument"
                          @click="handleCopy(props.data.secret || '')"
                        />
                      </ele-tooltip>
                    </template>
                  </el-input>
                  <span v-else class="empty-text">-</span>
                </div>
              </div>
            </div>

            <div class="info-item">
              <div class="info-label">应用密钥</div>
              <div class="info-value">
                <div class="secret-wrapper">
                  <el-input
                    v-if="props.data?.secret"
                    :value="
                      showSecret ? props.data.secret : '******************'
                    "
                    readonly
                    class="secret-input"
                  >
                    <template #append>
                      <ele-tooltip
                        content="显示/隐藏"
                        placement="top"
                        :showAfter="1000"
                      >
                        <el-button
                          :icon="showSecret ? View : Hide"
                          @click="showSecret = !showSecret"
                        />
                      </ele-tooltip>

                      <el-divider direction="vertical" />

                      <ele-tooltip
                        content="重置密钥"
                        placement="top"
                        :showAfter="1000"
                      >
                        <el-button :icon="Refresh" />
                      </ele-tooltip>

                      <el-divider direction="vertical" />
                      <ele-tooltip
                        content="复制"
                        placement="top"
                        :showAfter="1000"
                      >
                        <el-button
                          :icon="CopyDocument"
                          @click="handleCopy(props.data.secret || '')"
                        />
                      </ele-tooltip>
                    </template>
                  </el-input>
                  <span v-else class="empty-text">-</span>
                </div>
              </div>
            </div>

            <div class="info-item">
              <div class="info-label">授权回调地址</div>
              <div class="info-value info-value-with-action">
                <span class="callback-url">未设置回调地址</span>
                <el-button
                  text
                  type="primary"
                  size="small"
                  class="setting-btn"
                  :icon="SettingOutlined"
                  @click="handleSettingCallback"
                >
                  设置
                </el-button>
              </div>
            </div>

            <div class="info-item">
              <div class="info-label">网关地址</div>
              <div class="info-value"
                >https://openapi.doubee.com/gateway.do</div
              >
            </div>
          </div>
        </template>

        <template #payment>
          <div style="padding: 20px 0">
            <ele-pro-table
              ref="tableRef"
              row-key="id"
              :columns="columns"
              :datasource="datasource"
              :show-overflow-tooltip="true"
              :highlight-current-row="true"
              cache-key="applicationPaymentTable"
            >
              <template #toolbar>
                <el-button
                  type="primary"
                  class="ele-btn-icon"
                  :icon="PlusOutlined"
                >
                  添加渠道
                </el-button>
              </template>
            </ele-pro-table>
          </div>
        </template>
      </ele-tabs>
    </div>
  </ele-modal>
</template>

<script lang="ts" setup>
  import { ref, watch } from 'vue';
  import { CopyDocument, View, Hide, Refresh } from '@element-plus/icons-vue';
  import { SettingOutlined, PlusOutlined } from '@/components/icons';
  import { copyText } from 'ele-admin-plus/es/utils/common';
  import { EleMessage } from 'ele-admin-plus';
  import { Application } from '@/api/application/model';
  import {
    Columns,
    DatasourceFunction
  } from 'ele-admin-plus/es/ele-pro-table/types';

  defineOptions({ name: 'ApplicationDetails' });

  /** 标签页选中 */
  const tabsActive = ref<string>('development');

  const props = defineProps<{
    data?: Application | null;
  }>();

  defineEmits<{
    (e: 'done'): void;
  }>();

  /** 弹窗是否打开 */
  const visible = defineModel({ type: Boolean });

  /** 表格列配置 */
  const columns = ref<Columns>([
    {
      label: '渠道名称',
      width: 220,
      slot: 'application',
      minWidth: 200
    },
    {
      label: '今日收款',
      width: 120,
      slot: 'today_revenue'
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
    return Promise.resolve({
      data: [],
      total: 0
    });
  };

  /** 是否显示密钥 */
  const showSecret = ref(false);

  /** 复制文本 */
  const handleCopy = async (text: string) => {
    try {
      await copyText(text);
      EleMessage.success({ message: '已复制到剪贴板', plain: true });
    } catch (error: any) {
      EleMessage.error({ message: error.message || '复制失败', plain: true });
    }
  };

  /** 设置回调地址 */
  const handleSettingCallback = () => {
    // TODO: 实现设置回调地址的逻辑
    EleMessage.info({ message: '设置回调地址功能待实现', plain: true });
  };

  // 监听弹窗关闭，重置状态
  watch(visible, (val) => {
    if (!val) {
      showSecret.value = false;
    }
  });
</script>

<style lang="scss" scoped>
  .application-details {
    .info-item {
      display: flex;
      margin-bottom: 12px;
      align-items: flex-start;

      &:last-child {
        margin-bottom: 0;
      }

      .info-label {
        width: 140px;
        flex-shrink: 0;
        font-size: 14px;
        color: #606266;
        font-weight: 500;
        line-height: 32px;
      }

      .info-value {
        flex: 1;
        font-size: 14px;
        color: #303133;
        line-height: 32px;
        word-break: break-all;

        &.info-value-with-action {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 8px;
        }

        .callback-url {
          flex: 1;
          word-break: break-all;
        }

        .setting-btn {
          flex-shrink: 0;
          padding: 0 8px;
          height: 28px;
          font-size: 13px;
        }

        .application-no {
          margin-right: 8px;
        }

        .copy-btn {
          padding: 0;
          height: auto;
          font-size: 12px;
          line-height: 1.5;
        }

        .secret-wrapper {
          .secret-input {
            :deep(.el-input__inner) {
              font-family:
                'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro',
                monospace;
              font-size: 13px;
            }
          }

          .empty-text {
            color: #909399;
          }
        }

        .key-wrapper {
          width: 100%;

          .key-content {
            background: #f5f7fa;
            border: 1px solid #e4e7ed;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 8px;
            max-height: 200px;
            overflow-y: auto;

            .key-text {
              margin: 0;
              font-family:
                'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro',
                monospace;
              font-size: 12px;
              line-height: 1.6;
              color: #303133;
              white-space: pre-wrap;
              word-break: break-all;
            }
          }

          .key-actions {
            display: flex;
            justify-content: flex-end;
          }
        }

        .empty-text {
          color: #909399;
        }
      }
    }
  }

  :deep(.el-divider--horizontal) {
    margin: 12px 0;
  }
</style>
