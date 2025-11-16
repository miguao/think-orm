<template>
  <ele-page class="console-page">
    <el-row :gutter="16" class="console-row">
      <el-col :lg="16" :md="16" :sm="24" :xs="24">
        <ele-card :body-style="{ padding: 0 }" class="hero-card" shadow="hover">
          <div class="hero-content">
            <div class="hero-info">
              <div class="hero-title">
                {{ greeting }}，{{ userName }}
                <el-tag size="small" effect="dark" class="hero-tag">平台管理员</el-tag>
              </div>
              <p class="hero-desc">
                聚合支付平台实时 TPS {{ heroStats.tps }}，渠道在线率 {{ heroStats.channelOnline }}，自动巡检正常。
              </p>
              <div class="hero-actions">
                <el-button type="primary" round>发布应急预案</el-button>
                <el-button round>推送运维公告</el-button>
                <el-link type="primary" :icon="ArrowRightOutlined" :underline="false">查看运行日志</el-link>
              </div>
              <div class="hero-meta">
                <div class="meta-item" v-for="item in heroCards" :key="item.label">
                  <span class="meta-label">{{ item.label }}</span>
                  <p class="meta-value">{{ item.value }}</p>
                  <span class="meta-trend" :class="item.trend >= 0 ? 'up' : 'down'">
                    {{ item.trend >= 0 ? '+' : '' }}{{ item.trend }}%
                  </span>
                </div>
              </div>
            </div>
            <div class="hero-balance">
              <p class="hero-balance__title">清算账户余额</p>
              <p class="hero-balance__amount">¥ {{ balance }}</p>
              <el-progress :percentage="74" :stroke-width="10" :show-text="false" />
              <div class="hero-balance__tips">
                <div>
                  <p class="tips-label">待结算批次</p>
                  <p class="tips-value">21 个</p>
                </div>
                <div>
                  <p class="tips-label">告警未处理</p>
                  <p class="tips-value warning">5 条</p>
                </div>
              </div>
              <el-divider />
              <div class="hero-balance__foot">
                <p>近 1 小时分账成功率 99.81%</p>
                <el-link type="primary" :underline="false" :icon="ArrowRightOutlined">进入清算中心</el-link>
              </div>
            </div>
          </div>
        </ele-card>
      </el-col>
      <el-col :lg="8" :md="8" :sm="24" :xs="24">
        <ele-card header="实时告警" class="alert-card" shadow="hover">
          <el-timeline>
            <el-timeline-item
              v-for="item in alertList"
              :key="item.id"
              :type="item.type"
              :timestamp="item.time"
              hollow
            >
              <div class="alert-item">
                <p class="alert-title">{{ item.title }}</p>
                <p class="alert-desc">{{ item.desc }}</p>
              </div>
            </el-timeline-item>
          </el-timeline>
        </ele-card>
      </el-col>
    </el-row>

    <el-row :gutter="16" class="console-row">
      <el-col :lg="10" :md="24" :sm="24" :xs="24">
        <ele-card header="核心指标" class="metric-card" shadow="never">
          <el-row :gutter="12">
            <el-col v-for="item in metricCards" :key="item.title" :md="12" :sm="12" :xs="12">
              <div class="metric-item">
                <p class="metric-label">
                  {{ item.title }}
                  <el-tag size="small" effect="plain">{{ item.tag }}</el-tag>
                </p>
                <p class="metric-value">{{ item.value }}</p>
                <p class="metric-sub">{{ item.sub }}</p>
              </div>
            </el-col>
          </el-row>
        </ele-card>
      </el-col>
      <el-col :lg="8" :md="12" :sm="24" :xs="24">
        <ele-card header="快捷操作" class="shortcut-card" shadow="never">
          <div class="shortcut-list">
            <div class="shortcut-item" v-for="item in shortcuts" :key="item.title">
              <div class="shortcut-icon" :style="{ color: item.color }">
                <component :is="item.icon" />
              </div>
              <div>
                <p class="shortcut-title">{{ item.title }}</p>
                <p class="shortcut-desc">{{ item.desc }}</p>
              </div>
            </div>
          </div>
        </ele-card>
      </el-col>
      <el-col :lg="6" :md="12" :sm="24" :xs="24">
        <ele-card header="渠道健康度" class="channel-card" shadow="never">
          <div class="channel-item" v-for="item in channelHealth" :key="item.name">
            <div class="channel-head">
              <p>{{ item.name }}</p>
              <el-tag size="small" :type="item.type">{{ item.status }}</el-tag>
            </div>
            <p class="channel-desc">延迟 {{ item.delay }} · QPS {{ item.qps }}</p>
            <el-progress :percentage="item.percent" :stroke-width="8" :status="item.progress" :show-text="false" />
          </div>
        </ele-card>
      </el-col>
    </el-row>

    <el-row :gutter="16" class="console-row">
      <el-col :lg="14" :md="14" :sm="24" :xs="24">
        <ele-card header="实时交易监控" shadow="never">
          <el-table
            :data="transactionList"
            size="small"
            border
            class="console-table"
            :header-cell-style="{ background: '#f7f8fa' }"
          >
            <el-table-column prop="order" label="流水号" min-width="140" />
            <el-table-column prop="merchant" label="商户名称" min-width="140" />
            <el-table-column prop="channel" label="渠道" min-width="100" />
            <el-table-column prop="amount" label="金额 (元)" min-width="120">
              <template #default="{ row }">¥ {{ row.amount }}</template>
            </el-table-column>
            <el-table-column prop="status" label="状态" min-width="90">
              <template #default="{ row }">
                <el-tag size="small" :type="row.status === '成功' ? 'success' : row.status === '波动' ? 'warning' : 'info'">
                  {{ row.status }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="time" label="时间" min-width="120" />
          </el-table>
        </ele-card>
      </el-col>
      <el-col :lg="10" :md="10" :sm="24" :xs="24">
        <ele-card header="审批 / 工单" shadow="never">
          <div class="task-list">
            <div class="task-item" v-for="item in taskList" :key="item.id">
              <div>
                <p class="task-title">{{ item.title }}</p>
                <p class="task-desc">{{ item.desc }}</p>
              </div>
              <div class="task-foot">
                <el-tag size="small" :type="item.type">{{ item.priority }}</el-tag>
                <span>{{ item.deadline }}</span>
              </div>
            </div>
          </div>
        </ele-card>
        <ele-card header="操作日志" shadow="never" style="margin-top: 16px">
          <div class="ops-list">
            <div class="ops-item" v-for="item in opsLogs" :key="item.id">
              <div>
                <p class="ops-title">{{ item.title }}</p>
                <p class="ops-desc">{{ item.role }} · {{ item.user }}</p>
              </div>
              <span class="ops-time">{{ item.time }}</span>
            </div>
          </div>
        </ele-card>
      </el-col>
    </el-row>
  </ele-page>
</template>

<script lang="ts" setup>
  import { computed } from 'vue';
  import type { Component } from 'vue';
  import {
    ArrowRightOutlined,
    UploadOutlined,
    SettingOutlined,
    FileOutlined,
    FundOutlined
  } from '@/components/icons';

  defineOptions({ name: 'DashboardConsole' });

  const userName = '周行健';
  const balance = '12,140,918.00';

  const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return '上午好';
    if (hour < 14) return '中午好';
    if (hour < 19) return '下午好';
    return '晚上好';
  });

  const heroStats = {
    tps: '5.2k',
    channelOnline: '99.94%'
  };

  const heroCards = [
    { label: '今日交易额', value: '¥ 982,318,234', trend: 7.4 },
    { label: '成功率', value: '99.82%', trend: 0.9 },
    { label: '接口调用量', value: '3,294,833', trend: 5.6 },
    { label: '平均到账时间', value: '1.8h', trend: -6.3 }
  ];

  interface MetricCard {
    title: string;
    value: string;
    sub: string;
    tag: string;
  }

  const metricCards: MetricCard[] = [
    { title: '支付成功率', value: '99.82%', sub: '实时监控', tag: '实时' },
    { title: '退款占比', value: '0.42%', sub: '较昨日 -0.3%', tag: '当日' },
    { title: '风控拦截', value: '64 笔', sub: '近 1 小时', tag: '实时' },
    { title: '接口错误率', value: '0.018%', sub: '低于阈值', tag: '实时' }
  ];

  interface ShortcutItem {
    title: string;
    desc: string;
    icon: Component;
    color: string;
  }

  const shortcuts: ShortcutItem[] = [
    { title: '发起日结', desc: '生成今日清算', icon: FundOutlined, color: '#2f54eb' },
    { title: '导入批量结算', desc: '上传 Excel 批次', icon: UploadOutlined, color: '#13c2c2' },
    { title: '调账申请', desc: '人工快速调账', icon: FileOutlined, color: '#fa541c' },
    { title: '风控策略', desc: '启停通道策略', icon: SettingOutlined, color: '#722ed1' }
  ];

  interface ChannelHealthItem {
    name: string;
    status: string;
    type: 'success' | 'warning' | 'primary';
    delay: string;
    qps: string;
    percent: number;
    progress: 'success' | 'warning' | 'exception';
  }

  const channelHealth: ChannelHealthItem[] = [
    { name: '支付宝直连', status: '正常', type: 'success', delay: '120ms', qps: '1.2k', percent: 95, progress: 'success' },
    { name: '微信直连', status: '正常', type: 'success', delay: '140ms', qps: '0.9k', percent: 93, progress: 'success' },
    { name: '银联全渠道', status: '观察', type: 'warning', delay: '420ms', qps: '0.6k', percent: 78, progress: 'warning' },
    { name: '快捷支付', status: '轻微波动', type: 'primary', delay: '210ms', qps: '0.4k', percent: 84, progress: 'success' }
  ];

  interface AlertItem {
    id: number;
    title: string;
    desc: string;
    time: string;
    type: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  }

  const alertList: AlertItem[] = [
    { id: 1, title: '银联链路延迟 460ms', desc: '已自动切换备通道', time: '12:08', type: 'danger' },
    { id: 2, title: '风控策略热更新', desc: '版本 v3.2 发布，观察 15 分钟', time: '11:56', type: 'primary' },
    { id: 3, title: '账务出款排队', desc: '批次 20250115-34 等待 12 分钟', time: '11:42', type: 'warning' },
    { id: 4, title: '跨境沙箱推送成功', desc: '4 个海外渠道恢复', time: '11:20', type: 'success' }
  ];

  interface TransactionItem {
    order: string;
    merchant: string;
    channel: string;
    amount: string;
    status: string;
    time: string;
  }

  const transactionList: TransactionItem[] = [
    { order: 'PO20250115001', merchant: '杭州融信集团', channel: '支付宝', amount: '18,920.00', status: '成功', time: '12:10:32' },
    { order: 'PO20250115024', merchant: '上海恒瑞医疗', channel: '微信', amount: '6,380.10', status: '成功', time: '12:08:07' },
    { order: 'PO20250115039', merchant: '广州城投科技', channel: '银联', amount: '26,110.54', status: '波动', time: '12:05:24' },
    { order: 'PO20250115062', merchant: '北京同城生活', channel: '支付宝', amount: '9,800.00', status: '处理中', time: '11:58:18' },
    { order: 'PO20250115088', merchant: '深圳金服物流', channel: '快捷', amount: '4,928.77', status: '成功', time: '11:55:04' }
  ];

  interface TaskItem {
    id: number;
    title: string;
    desc: string;
    priority: string;
    deadline: string;
    type: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  }

  const taskList: TaskItem[] = [
    { id: 1, title: '复核批量调账', desc: '风控拦截代付待确认', priority: '高优', deadline: '12:30', type: 'danger' },
    { id: 2, title: '联机渠道巡检', desc: '补充支付宝限流说明', priority: '中优', deadline: '13:00', type: 'warning' },
    { id: 3, title: '生成清算日报', desc: '提交财务共享中心', priority: '常规', deadline: '14:00', type: 'info' }
  ];

  interface OpsLog {
    id: number;
    title: string;
    role: string;
    user: string;
    time: string;
  }

  const opsLogs: OpsLog[] = [
    { id: 1, title: '下发风控策略 v3.21', role: '风控', user: '张敏', time: '11:58' },
    { id: 2, title: '调高银联限流阈值', role: '运维', user: '李越', time: '11:46' },
    { id: 3, title: '发布账务服务 v2.14.5', role: '技术', user: '王沐森', time: '11:32' },
    { id: 4, title: '审核商户额度上调', role: '运营', user: '黄心语', time: '11:18' }
  ];
</script>

<style lang="scss" scoped>
  .console-page {
    padding-bottom: 16px;
  }

  .console-row {
    margin-bottom: 16px;
  }

  .hero-card {
    border: none;
    background: linear-gradient(135deg, #f5f8ff 0%, #ffffff 65%);
  }

  .hero-content {
    display: flex;
    gap: 24px;
    padding: 32px;
    flex-wrap: wrap;
  }

  .hero-info {
    flex: 1;
    min-width: 320px;
  }

  .hero-title {
    font-size: 22px;
    font-weight: 600;
    color: #1f1f1f;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .hero-tag {
    border: none;
    background: rgba(22, 119, 255, 0.15);
    color: #1677ff;
  }

  .hero-desc {
    margin: 8px 0 16px;
    color: #595959;
  }

  .hero-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }

  .hero-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 14px;
  }

  .meta-item {
    background: #fff;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 6px 18px rgba(15, 74, 168, 0.1);
  }

  .meta-label {
    color: #8c8c8c;
  }

  .meta-value {
    font-size: 20px;
    font-weight: 600;
    color: #1f1f1f;
    margin: 4px 0;
  }

  .meta-trend {
    font-size: 13px;

    &.up {
      color: #52c41a;
    }

    &.down {
      color: #f5222d;
    }
  }

  .hero-balance {
    width: 280px;
    border-radius: 16px;
    padding: 24px;
    background: #fff;
    box-shadow: 0 20px 45px rgba(15, 74, 168, 0.12);
  }

  .hero-balance__title {
    color: #8c8c8c;
  }

  .hero-balance__amount {
    font-size: 28px;
    font-weight: 600;
    margin: 12px 0 20px;
  }

  .hero-balance__tips {
    display: flex;
    justify-content: space-between;
    margin: 14px 0;

    .tips-label {
      color: #8c8c8c;
      margin-bottom: 4px;
    }

    .tips-value {
      font-weight: 600;

      &.warning {
        color: #fa541c;
      }
    }
  }

  .hero-balance__foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #8c8c8c;
  }

  .alert-card {
    height: 100%;
  }

  .alert-item {
    .alert-title {
      font-weight: 600;
    }

    .alert-desc {
      font-size: 12px;
      color: #8c8c8c;
    }
  }

  .metric-card {
    min-height: 100%;
  }

  .metric-item {
    border: 1px solid #f0f0f0;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 12px;
  }

  .metric-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #8c8c8c;
  }

  .metric-value {
    font-size: 20px;
    font-weight: 600;
    margin: 4px 0;
  }

  .shortcut-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .shortcut-item {
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    gap: 12px;
    align-items: center;
    transition: transform 0.2s;

    &:hover {
      transform: translateY(-3px);
    }
  }

  .shortcut-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f7f9ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .channel-item {
    padding-bottom: 12px;
    margin-bottom: 12px;
    border-bottom: 1px dashed #f0f0f0;

    &:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
  }

  .channel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .channel-desc {
    color: #8c8c8c;
    font-size: 12px;
    margin: 6px 0 10px;
  }

  .task-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .task-item {
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    padding: 12px;
    background: #f9fbff;
  }

  .task-title {
    font-weight: 600;
    margin-bottom: 4px;
  }

  .task-desc {
    color: #8c8c8c;
  }

  .task-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 12px;
    color: #8c8c8c;
  }

  .ops-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .ops-item {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed #f0f0f0;
    padding-bottom: 8px;

    &:last-child {
      border-bottom: none;
    }
  }

  .ops-title {
    font-weight: 600;
  }

  .ops-desc {
    font-size: 12px;
    color: #8c8c8c;
  }

  .ops-time {
    font-size: 12px;
    color: #8c8c8c;
  }

  @media screen and (max-width: 768px) {
    .hero-content {
      padding: 24px;
    }

    .hero-balance {
      width: 100%;
    }

    .hero-actions {
      flex-wrap: wrap;
    }
  }
</style>

