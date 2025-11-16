<template>
  <div class="monitor-screen">
    <div class="screen-header">
      <div class="header-left">
        <p class="header-sub">Doubee 聚合支付 · 运行中心</p>
        <h1>实时监控大屏</h1>
      </div>
      <div class="header-center">
        <div class="clock-time">{{ clock.time }}</div>
        <p class="clock-date">{{ clock.date }}</p>
      </div>
      <div class="header-right">
        <div class="header-chip" v-for="item in headerChips" :key="item.label">
          <span>{{ item.label }}</span>
          <strong>{{ item.value }}</strong>
        </div>
      </div>
    </div>

    <div class="metric-strip">
      <div class="metric-card" v-for="item in metricCards" :key="item.label">
        <div class="metric-card__label">{{ item.label }}</div>
        <div class="metric-card__value">
          {{ item.value }}
          <small>{{ item.unit }}</small>
        </div>
        <div class="metric-card__foot">
          <span>{{ item.sub }}</span>
          <span :class="item.trend >= 0 ? 'up' : 'down'">
            {{ item.trend >= 0 ? '+' : '' }}{{ item.trend }}%
          </span>
        </div>
        <el-progress :percentage="item.percent" :show-text="false" />
      </div>
    </div>

    <div class="screen-grid">
      <div class="grid-column">
        <section class="panel">
          <div class="panel-title">流量总览</div>
          <div class="flow-overview">
            <div
              class="flow-item"
              v-for="item in flowOverview"
              :key="item.label"
            >
              <div class="flow-meta">
                <p class="flow-label">{{ item.label }}</p>
                <el-tag size="small" effect="dark">{{ item.status }}</el-tag>
              </div>
              <p class="flow-value">{{ item.value }}</p>
              <p class="flow-desc">{{ item.desc }}</p>
              <el-progress :percentage="item.percent" :show-text="false" />
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-title">实时告警</div>
          <el-timeline>
            <el-timeline-item
              v-for="item in alertList"
              :key="item.id"
              :type="item.type"
              :timestamp="item.time"
            >
              <div class="alert-item">
                <p class="alert-title">{{ item.title }}</p>
                <p class="alert-desc">{{ item.desc }}</p>
              </div>
            </el-timeline-item>
          </el-timeline>
        </section>

        <section class="panel">
          <div class="panel-title">待处理任务</div>
          <div class="task-list">
            <div class="task-item" v-for="item in taskList" :key="item.id">
              <div>
                <p class="task-title">{{ item.title }}</p>
                <p class="task-desc">{{ item.desc }}</p>
              </div>
              <div class="task-foot">
                <el-tag size="small" :type="item.type">{{
                  item.priority
                }}</el-tag>
                <span>{{ item.deadline }}</span>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="grid-column grid-center">
        <section class="panel panel-map">
          <div class="panel-title">交易热力图</div>
          <map-card />
        </section>
        <section class="panel">
          <div class="panel-title">近 10 分钟交易</div>
          <el-table
            :data="transactionPulse"
            size="small"
            border
            class="dark-table"
            :header-cell-style="{
              background: 'rgba(255,255,255,0.04)',
              color: '#cfd5ff'
            }"
          >
            <el-table-column prop="order" label="批次号" min-width="140" />
            <el-table-column prop="channel" label="渠道" min-width="100" />
            <el-table-column prop="tps" label="TPS 峰值" min-width="90" />
            <el-table-column prop="amount" label="金额" min-width="120" />
            <el-table-column prop="status" label="状态" min-width="90">
              <template #default="{ row }">
                <el-tag size="small" :type="row.status">{{
                  row.statusText
                }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="time" label="落地时间" min-width="120" />
          </el-table>
        </section>
      </div>

      <div class="grid-column">
        <section class="panel">
          <div class="panel-title">渠道健康度</div>
          <div
            class="channel-card"
            v-for="item in channelHealth"
            :key="item.name"
          >
            <div class="channel-head">
              <p class="channel-name">{{ item.name }}</p>
              <el-tag size="small" :type="item.type">{{ item.status }}</el-tag>
            </div>
            <p class="channel-desc"
              >延迟 {{ item.delay }} · QPS {{ item.qps }}</p
            >
            <el-progress
              :percentage="item.percent"
              :stroke-width="8"
              :status="item.progress"
              :show-text="false"
            />
            <p class="channel-trend">{{ item.trend }}</p>
          </div>
        </section>

        <section class="panel">
          <div class="panel-title">队列积压</div>
          <div class="queue-list">
            <div class="queue-item" v-for="item in queueStats" :key="item.name">
              <div>
                <p class="queue-name">{{ item.name }}</p>
                <p class="queue-desc">阈值 {{ item.limit }}</p>
              </div>
              <div class="queue-progress">
                <el-progress
                  :percentage="item.value"
                  :status="item.status"
                  :show-text="false"
                />
                <span>{{ item.value }}%</span>
              </div>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-title">操作日志</div>
          <div class="ops-list">
            <div class="ops-item" v-for="item in opsLogs" :key="item.id">
              <div>
                <p class="ops-title">{{ item.title }}</p>
                <p class="ops-desc">{{ item.role }} · {{ item.user }}</p>
              </div>
              <span class="ops-time">{{ item.time }}</span>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { ref, onMounted, onBeforeUnmount } from 'vue';
  import dayjs from 'dayjs';
  import MapCard from './components/map-card.vue';

  defineOptions({ name: 'DashboardMonitor' });

  interface MetricCard {
    label: string;
    value: string;
    unit: string;
    sub: string;
    trend: number;
    percent: number;
  }

  interface FlowItem {
    label: string;
    value: string;
    desc: string;
    percent: number;
    status: string;
  }

  interface AlertItem {
    id: number;
    title: string;
    desc: string;
    time: string;
    type: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  }

  interface TaskItem {
    id: number;
    title: string;
    desc: string;
    priority: string;
    deadline: string;
    type: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  }

  interface TransactionPulse {
    order: string;
    channel: string;
    tps: string;
    amount: string;
    status: 'success' | 'warning' | 'danger';
    statusText: string;
    time: string;
  }

  interface ChannelHealth {
    name: string;
    status: string;
    type: 'primary' | 'success' | 'warning';
    delay: string;
    qps: string;
    percent: number;
    progress: 'success' | 'warning' | 'exception';
    trend: string;
  }

  interface QueueStat {
    name: string;
    limit: string;
    value: number;
    status: 'success' | 'warning' | 'exception';
  }

  interface OpsLog {
    id: number;
    title: string;
    role: string;
    user: string;
    time: string;
  }

  const clock = ref({ time: '--:--:--', date: '--/--/--' });
  let timer: number | null = null;

  const headerChips = [
    { label: '并发会话', value: '18,236' },
    { label: '今日交易笔数', value: '3,284,992' },
    { label: '自动化任务', value: '126' }
  ];

  const metricCards: MetricCard[] = [
    {
      label: '实时交易额',
      value: '982,318,234',
      unit: '元',
      sub: '同比 +8.2%',
      trend: 8.2,
      percent: 72
    },
    {
      label: '支付成功率',
      value: '99.82',
      unit: '%',
      sub: '波动 0.02%',
      trend: 0.2,
      percent: 90
    },
    {
      label: '接口错误率',
      value: '0.018',
      unit: '%',
      sub: '低于阈值',
      trend: -0.6,
      percent: 18
    },
    {
      label: '清算到账',
      value: '1.8',
      unit: '小时',
      sub: '近 7 日平均',
      trend: -6.3,
      percent: 64
    }
  ];

  const flowOverview: FlowItem[] = [
    {
      label: '支付宝直连',
      value: '1.92k TPS',
      desc: '成功率 99.92%',
      percent: 82,
      status: '稳定'
    },
    {
      label: '微信直连',
      value: '1.34k TPS',
      desc: '成功率 99.85%',
      percent: 74,
      status: '稳定'
    },
    {
      label: '银联全渠道',
      value: '0.88k TPS',
      desc: '成功率 99.12%',
      percent: 62,
      status: '观察'
    },
    {
      label: '快捷/云闪付',
      value: '0.51k TPS',
      desc: '成功率 99.48%',
      percent: 48,
      status: '稳定'
    }
  ];

  const alertList: AlertItem[] = [
    {
      id: 1,
      title: '银联主链路抖动',
      desc: '延迟 540ms，已切换备链路',
      time: '12:08:21',
      type: 'danger'
    },
    {
      id: 2,
      title: '风控策略热更新',
      desc: '版本 v3.2 发布成功，观察 15 分钟',
      time: '12:05:10',
      type: 'primary'
    },
    {
      id: 3,
      title: '账务出款排队',
      desc: '批次 20250115-34 排队 12 分钟',
      time: '11:58:44',
      type: 'warning'
    },
    {
      id: 4,
      title: '跨境沙箱推送成功',
      desc: '4 个渠道通路恢复',
      time: '11:48:03',
      type: 'success'
    }
  ];

  const taskList: TaskItem[] = [
    {
      id: 1,
      title: '复核批量调账',
      desc: '风控拦截的 3 笔代付待确认',
      priority: '高优',
      deadline: '12:30 截止',
      type: 'danger'
    },
    {
      id: 2,
      title: '联机渠道巡检',
      desc: '补充支付宝限流策略说明',
      priority: '中优',
      deadline: '13:00',
      type: 'warning'
    },
    {
      id: 3,
      title: '生成清算日报',
      desc: '提交财务共享中心',
      priority: '常规',
      deadline: '14:00',
      type: 'info'
    }
  ];

  const transactionPulse: TransactionPulse[] = [
    {
      order: 'AO202501151201',
      channel: '支付宝',
      tps: '2.1k',
      amount: '32.8M',
      status: 'success',
      statusText: '稳定',
      time: '12:08:05'
    },
    {
      order: 'WX202501151158',
      channel: '微信',
      tps: '1.6k',
      amount: '24.6M',
      status: 'success',
      statusText: '稳定',
      time: '12:06:41'
    },
    {
      order: 'UP202501151140',
      channel: '银联',
      tps: '0.8k',
      amount: '12.1M',
      status: 'warning',
      statusText: '波动',
      time: '12:04:13'
    },
    {
      order: 'QD202501151132',
      channel: '快捷',
      tps: '0.4k',
      amount: '6.9M',
      status: 'success',
      statusText: '稳定',
      time: '12:01:02'
    },
    {
      order: 'FX202501151125',
      channel: '分账',
      tps: '0.3k',
      amount: '4.2M',
      status: 'danger',
      statusText: '告警',
      time: '11:58:27'
    }
  ];

  const channelHealth: ChannelHealth[] = [
    {
      name: '支付宝直连',
      status: '正常',
      type: 'success',
      delay: '120ms',
      qps: '1.2k',
      percent: 96,
      progress: 'success',
      trend: '+0.3% QoS'
    },
    {
      name: '微信直连',
      status: '正常',
      type: 'success',
      delay: '140ms',
      qps: '0.9k',
      percent: 94,
      progress: 'success',
      trend: '+0.1% QoS'
    },
    {
      name: '银联全渠道',
      status: '轻微波动',
      type: 'warning',
      delay: '420ms',
      qps: '0.6k',
      percent: 78,
      progress: 'warning',
      trend: '-0.8% QoS'
    },
    {
      name: '快捷支付',
      status: '观察',
      type: 'primary',
      delay: '210ms',
      qps: '0.4k',
      percent: 84,
      progress: 'success',
      trend: '+0.5% QoS'
    }
  ];

  const queueStats: QueueStat[] = [
    { name: '账务核心', limit: '5k', value: 42, status: 'success' },
    { name: '清算出款', limit: '3k', value: 64, status: 'warning' },
    { name: '风控事件', limit: '8k', value: 28, status: 'success' },
    { name: '通知推送', limit: '10k', value: 12, status: 'success' }
  ];

  const opsLogs: OpsLog[] = [
    {
      id: 1,
      title: '下发风控策略 v3.21',
      role: '风控',
      user: '张敏',
      time: '11:58'
    },
    {
      id: 2,
      title: '调高银联限流阈值',
      role: '运维',
      user: '李越',
      time: '11:46'
    },
    {
      id: 3,
      title: '发布账务服务 v2.14.5',
      role: '技术',
      user: '王沐森',
      time: '11:32'
    },
    {
      id: 4,
      title: '审核商户额度上调',
      role: '运营',
      user: '黄心语',
      time: '11:18'
    }
  ];

  const updateClock = () => {
    const now = dayjs();
    clock.value = {
      time: now.format('HH:mm:ss'),
      date: now.format('YYYY/MM/DD dddd')
    };
  };

  onMounted(() => {
    updateClock();
    timer = window.setInterval(updateClock, 1000);
  });

  onBeforeUnmount(() => {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  });
</script>

<style lang="scss" scoped>
  .monitor-screen {
    position: relative;
    padding: 16px;
    min-height: 100vh;
    color: #dfe9ff;
    box-sizing: border-box;
    background: transparent;
    z-index: 0;

    &::before {
      content: '';
      position: fixed;
      inset: 0;
      background: radial-gradient(
        circle at top,
        #12204b 0%,
        #050916 55%,
        #040714 100%
      );
      z-index: -1;
      pointer-events: none;
    }
  }

  .screen-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 12px 20px;
    border: 1px solid rgba(103, 132, 255, 0.3);
    border-radius: 12px;
    background: rgba(12, 20, 46, 0.7);
    box-shadow: 0 10px 30px rgba(4, 10, 32, 0.7);
  }

  .header-left h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 600;
  }

  .header-sub {
    margin: 0;
    font-size: 14px;
    color: #97a3d8;
  }

  .header-center {
    text-align: center;

    .clock-time {
      font-size: 34px;
      font-weight: 700;
      letter-spacing: 2px;
    }

    .clock-date {
      margin-top: 4px;
      color: #8aa1ff;
      font-size: 13px;
    }
  }

  .header-right {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .header-chip {
    min-width: 120px;
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid rgba(103, 132, 255, 0.4);
    background: rgba(30, 42, 84, 0.6);
    text-align: right;

    span {
      display: block;
      font-size: 12px;
      color: #7c8bba;
    }

    strong {
      font-size: 16px;
      font-weight: 600;
    }
  }

  .metric-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
  }

  .metric-card {
    padding: 18px;
    border-radius: 12px;
    border: 1px solid rgba(58, 116, 255, 0.3);
    background: rgba(12, 20, 46, 0.85);
    box-shadow: inset 0 0 20px rgba(53, 93, 255, 0.12);

    &__label {
      font-size: 13px;
      color: #8aa1ff;
    }

    &__value {
      font-size: 26px;
      font-weight: 600;
      margin: 6px 0 4px;

      small {
        font-size: 12px;
        margin-left: 4px;
        color: #8aa1ff;
      }
    }

    &__foot {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin-bottom: 6px;

      .up {
        color: #35d58a;
      }

      .down {
        color: #ff7875;
      }
    }

    :deep(.el-progress-bar__outer) {
      background: rgba(255, 255, 255, 0.1);
    }
  }

  .screen-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr;
    gap: 16px;
  }

  .panel {
    border-radius: 14px;
    border: 1px solid rgba(84, 120, 255, 0.35);
    padding: 16px;
    background: rgba(6, 13, 34, 0.9);
    box-shadow: 0 12px 30px rgba(4, 7, 20, 0.65);
    margin-bottom: 16px;
  }

  .panel-title {
    margin-bottom: 14px;
    font-weight: 600;
    font-size: 16px;
    color: #dfe9ff;
    position: relative;
    padding-left: 10px;

    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 4px;
      width: 3px;
      height: 16px;
      background: linear-gradient(180deg, #4c86ff, #2fc4ff);
      border-radius: 2px;
    }
  }

  .flow-item {
    padding: 12px 0;
    border-bottom: 1px dashed rgba(173, 189, 255, 0.2);

    &:last-child {
      border-bottom: none;
    }
  }

  .flow-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .flow-label {
    font-weight: 600;
  }

  .flow-value {
    font-size: 20px;
    font-weight: 600;
    margin: 4px 0;
  }

  .flow-desc {
    font-size: 12px;
    color: #7f92c7;
    margin-bottom: 6px;
  }

  .alert-item {
    .alert-title {
      font-weight: 600;
    }

    .alert-desc {
      font-size: 12px;
      color: #96a6de;
    }
  }

  :deep(.el-timeline-item__node--normal) {
    background: #4c86ff;
  }

  .task-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .task-item {
    border: 1px solid rgba(84, 120, 255, 0.2);
    border-radius: 10px;
    padding: 12px;
    background: rgba(18, 25, 55, 0.6);
  }

  .task-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 12px;
    color: #90a3db;
  }

  .panel-map {
    :deep(.ele-card) {
      background: transparent;
      border: none;
      color: #fff;
    }

    :deep(.ele-card-header) {
      display: none;
    }

    :deep(.ele-card-body) {
      padding: 0;
    }
  }

  .dark-table {
    :deep(.el-table) {
      background: transparent;
      color: #e0e5ff;
    }

    :deep(.el-table tr) {
      background: transparent !important;
    }

    :deep(.el-table__row) {
      transition: background 0.2s;
    }

    :deep(.el-table__row:hover) {
      background: rgba(76, 134, 255, 0.08) !important;
    }

    :deep(.el-table__cell) {
      border-color: rgba(255, 255, 255, 0.05) !important;
    }
  }

  .channel-card {
    border: 1px solid rgba(103, 132, 255, 0.25);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
    background: rgba(16, 25, 52, 0.7);
  }

  .channel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .channel-name {
    font-weight: 600;
  }

  .channel-desc {
    font-size: 12px;
    color: #8aa1ff;
    margin: 6px 0;
  }

  .channel-trend {
    font-size: 12px;
    color: #4c86ff;
    margin-top: 6px;
  }

  .queue-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed rgba(173, 189, 255, 0.2);

    &:last-child {
      border-bottom: none;
    }
  }

  .queue-progress {
    width: 150px;
    display: flex;
    align-items: center;
    gap: 8px;

    span {
      font-size: 12px;
    }
  }

  .ops-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .ops-item {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed rgba(173, 189, 255, 0.2);
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
    color: #8aa1ff;
  }

  .ops-time {
    font-size: 12px;
    color: #90a3db;
  }

  @media screen and (max-width: 1200px) {
    .metric-strip {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .screen-grid {
      grid-template-columns: 1fr;
    }
  }

  @media screen and (max-width: 768px) {
    .monitor-screen {
      padding: 12px;
      overflow-x: hidden;
    }

    .screen-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
      padding: 12px;
    }

    .header-left h1 {
      font-size: 20px;
    }

    .header-right {
      width: 100%;
      justify-content: flex-start;
    }

    .header-chip {
      flex: 1 1 calc(50% - 8px);
      min-width: auto;
    }

    .metric-strip {
      grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .screen-grid {
      gap: 12px;
      grid-template-columns: 1fr;
    }

    .panel {
      padding: 12px;
      width: 100%;
    }

    .dark-table {
      width: 100%;
      overflow-x: auto;
    }

    .panel :deep(.el-row) {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    .panel :deep(.el-col) {
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .task-item,
    .channel-card {
      padding: 10px;
    }
  }
</style>
