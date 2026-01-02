<template>
  <ele-page class="plugin-page">
    <div class="page-hero">
      <div class="page-hero__text">
        <h2 class="title">插件管理</h2>
        <p class="subtitle"
          >为系统按需扩展能力，灵活启用与配置官方与第三方插件。</p
        >
      </div>
      <div class="page-hero__actions">
        <el-input
          v-model="keyword"
          placeholder="搜索插件名称 / 作者 / 描述"
          clearable
          class="hero-search"
          :prefix-icon="SearchOutlined"
          @keyup.enter="applyFilter"
        />
      </div>
    </div>

    <!-- 筛选行 -->
    <ele-card
      :body-style="{ paddingTop: '12px', paddingBottom: '12px' }"
      class="filter-card"
    >
      <div class="filters">
        <div class="filters-left">
          <el-select
            v-model="category"
            placeholder="全部分类"
            clearable
            class="filter-item"
            @change="applyFilter"
          >
            <el-option
              v-for="c in categories"
              :key="c.value"
              :label="c.label"
              :value="c.value"
            />
          </el-select>
          <el-select
            v-model="status"
            placeholder="全部状态"
            clearable
            class="filter-item"
            @change="applyFilter"
          >
            <el-option label="未启用" value="disabled" />
            <el-option label="运行中" value="enabled" />
          </el-select>
        </div>
      </div>
    </ele-card>

    <!-- 内容 -->
    <div class="plugin-grid">
      <transition-group name="fade-list" tag="div" class="plugin-grid__wrap">
        <ele-card
          v-for="item in pagedList"
          :key="item.id"
          class="plugin-card"
          :body-style="{ padding: '16px' }"
          shadow="hover"
        >
          <div class="plugin-card__header">
            <div class="plugin-card__logo">
              <img :src="item.logo || defaultLogo" alt="" />
            </div>
            <div class="plugin-card__title">
              <div class="name">
                {{ item.name }}
                <el-tag size="small" type="info" effect="dark" class="ver"
                  >v{{ item.version }}</el-tag
                >
              </div>
              <div class="meta">
                <span class="vendor">{{ item.vendor || '官方' }}</span>
                <el-divider direction="vertical" />
                <span class="category">{{ item.category || '其他' }}</span>
              </div>
            </div>
            <div class="plugin-card__switch">
              <el-switch
                :model-value="item.enabled"
                @change="(checked: boolean) => togglePlugin(item, checked)"
                inline-prompt
              />
            </div>
          </div>
          <div class="plugin-card__desc">
            <ele-ellipsis :line="2">{{
              item.description || '——'
            }}</ele-ellipsis>
          </div>
          <div class="plugin-card__badges">
            <el-tag
              v-if="item.official"
              size="small"
              type="success"
              effect="light"
              >官方</el-tag
            >
            <el-tag
              v-if="item.recommended"
              size="small"
              type="warning"
              effect="light"
              >推荐</el-tag
            >
            <el-tag v-if="item.paid" size="small" type="danger" effect="light"
              >付费</el-tag
            >
          </div>
          <div class="plugin-card__footer">
            <div class="stats">
              <el-icon><StarFilled /></el-icon>
              <span>{{ item.stars ?? 0 }}</span>
              <el-divider direction="vertical" />
              <span>安装量 {{ item.installs ?? 0 }}</span>
              <el-divider direction="vertical" />
              <span class="update-time"
                >更新：{{ formatTime(item.updatedAt) }}</span
              >
            </div>
            <div class="actions">
              <el-button size="small" @click="openDetail(item)">详情</el-button>
              <el-button size="small" type="primary" @click="openConfig(item)"
                >配置</el-button
              >
              <el-dropdown @command="(cmd: string) => moreAction(cmd, item)">
                <el-button size="small" text>更多</el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="reinstall"
                      >重新安装</el-dropdown-item
                    >
                    <el-dropdown-item
                      command="disable"
                      :disabled="!item.enabled"
                      >停用</el-dropdown-item
                    >
                    <el-dropdown-item command="uninstall" divided
                      >卸载</el-dropdown-item
                    >
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </div>
        </ele-card>
      </transition-group>

      <div v-if="!filteredList.length" class="empty-holder">
        <ele-empty
          title="未找到符合条件的插件"
          description="试试调整筛选条件，或前往插件市场发现更多"
        />
      </div>
      <div v-else class="pagination-holder">
        <el-pagination
          v-model:current-page="page"
          v-model:page-size="pageSize"
          :total="filteredList.length"
          :page-sizes="[8, 12, 16, 24, 36]"
          :layout="
            isMobile
              ? 'prev, pager, next'
              : 'total, sizes, prev, pager, next, jumper'
          "
          background
          small
          @size-change="onPageSizeChange"
        />
      </div>
    </div>

    <!-- 详情抽屉 -->
    <ele-modal v-model="showDetail" title="插件信息">
      <template #default>
        <div class="detail">
          <div class="detail__head">
            <img :src="detailData?.logo || defaultLogo" class="detail__logo" />
            <div>
              <h3 class="detail__name">
                {{ detailData?.name }}
                <el-tag size="small" type="info" effect="dark" class="ver"
                  >v{{ detailData?.version }}</el-tag
                >
              </h3>
              <p class="detail__meta">
                作者：{{ detailData?.vendor || '官方' }}
                <el-divider direction="vertical" />
                分类：{{ detailData?.category || '其他' }}
                <el-divider direction="vertical" />
                安装时间：{{ formatTime(detailData?.updatedAt) }}
              </p>
            </div>
          </div>

          <el-alert
            v-if="detailData?.notice"
            :title="detailData?.notice"
            type="info"
            :closable="false"
            style="margin-bottom: 12px"
          />

          <div class="detail__desc">{{
            detailData?.longDescription || detailData?.description
          }}</div>
          <el-divider />
          <div class="detail__stats">
            <span
              ><el-icon><StarFilled /></el-icon>
              {{ detailData?.stars ?? 0 }}</span
            >
            <el-divider direction="vertical" />
            <span>安装量 {{ detailData?.installs ?? 0 }}</span>
            <el-divider direction="vertical" />
            <span>状态：{{ detailData?.enabled ? '已启用' : '未启用' }}</span>
          </div>
        </div>
      </template>
    </ele-modal>
  </ele-page>
</template>

<script lang="ts" setup>
  import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
  import { EleMessage } from 'ele-admin-plus';
  import type {
    Columns,
    DatasourceFunction
  } from 'ele-admin-plus/es/ele-pro-table/types';
  import { SearchOutlined, StarFilled } from '@/components/icons';

  defineOptions({ name: 'Plugin' });

  type PluginItem = {
    id: number;
    name: string;
    version: string;
    vendor?: string;
    category?: string;
    description?: string;
    longDescription?: string;
    notice?: string;
    logo?: string;
    enabled: boolean;
    official?: boolean;
    recommended?: boolean;
    paid?: boolean;
    stars?: number;
    installs?: number;
    updatedAt?: string | number | Date;
  };

  const defaultLogo = 'https://dummyimage.com/80x80/efefef/aaa.png&text=PLG';

  // 顶部状态
  const keyword = ref<string>('');
  const category = ref<string>();
  const status = ref<string>();
  const order = ref<string>('updated_desc');

  const categories = [
    { label: '通用插件', value: 'general' },
    { label: '支付插件', value: 'payment' },
    { label: '进件插件', value: ' application' }
  ];

  // 断点（移动端）
  const isMobile = ref(false);
  const handleResize = () => {
    isMobile.value = window.innerWidth <= 576;
  };
  onMounted(() => {
    handleResize();
    window.addEventListener('resize', handleResize);
  });
  onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
  });

  // 模拟数据
  const list = ref<PluginItem[]>([
    {
      id: 1,
      name: '微信支付',
      version: '1.0.0',
      vendor: '官方',
      category: '支付插件',
      description:
        '微信支付是腾讯公司的支付业务品牌，微信支付商户平台支持线下场所、公众号、小程序、PC网站、APP、企业微信等经营场景快速接入微信支付。微信支付全面打通O2O生活消费领域，提供专业的互联网+行业解决方案，微信支付支持微信红包和微信理财通，是移动支付的首选。',
      longDescription:
        '插件提供对微信支付的全面适配，涵盖 JSAPI、Native、H5 等场景，并内置签名、回调校验、账单对账等能力。',
      logo: 'https://doubee.nanoa.cn/plugin/WeChatPay/Icon.ico',
      enabled: true,
      official: true,
      recommended: true,
      stars: 4.9,
      installs: 12413,
      updatedAt: Date.now() - 1000 * 60 * 60 * 20
    },
    {
      id: 2,
      name: '支付宝',
      version: '1.0.0',
      vendor: '官方',
      category: '支付插件',
      description:
        '支付宝，全球领先的独立第三方支付平台，致力于为广大用户提供安全快速的电子支付/网上支付/安全支付/手机支付体验，及转账收款/水电煤缴费/信用卡还款/AA收款等生活服务应用。',
      logo: 'https://doubee.nanoa.cn/plugin/AliPay/Icon.ico',
      enabled: false,
      official: true,
      recommended: true,
      stars: 4.5,
      installs: 5820,
      updatedAt: Date.now() - 1000 * 60 * 60 * 72,
      notice: '升级到 v1.1.0 后需重新配置模板ID。'
    },
    {
      id: 3,
      name: 'DEMO插件',
      version: '1.0.0',
      vendor: '官方',
      category: '通用插件',
      description:
        '这是一个官方演示插件，开发者可参考此插件进行二次开发与调试。',
      logo: 'https://doubee.nanoa.cn/plugin/Demo/Icon.ico',
      paid: false,
      enabled: false,
      official: false,
      recommended: false,
      stars: 4.2,
      installs: 1311,
      updatedAt: Date.now() - 1000 * 60 * 60 * 200
    }
  ]);

  const formatTime = (time?: string | number | Date) => {
    if (!time) return '-';
    const d = new Date(time);
    const pad = (n: number) => (n < 10 ? '0' + n : '' + n);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(
      d.getHours()
    )}:${pad(d.getMinutes())}`;
  };

  const applyFilter = () => {
    // 纯前端筛选演示；接入接口时传入 where / order 参数
    page.value = 1;
  };

  const filteredList = computed(() => {
    let data = [...list.value];
    if (keyword.value) {
      const kw = keyword.value.toLowerCase();
      data = data.filter(
        (d) =>
          d.name.toLowerCase().includes(kw) ||
          (d.vendor || '').toLowerCase().includes(kw) ||
          (d.description || '').toLowerCase().includes(kw)
      );
    }
    if (category.value) {
      data = data.filter((d) => d.category === category.value);
    }
    if (status.value) {
      data = data.filter((d) =>
        status.value === 'enabled' ? d.enabled : !d.enabled
      );
    }
    switch (order.value) {
      case 'updated_desc':
        data.sort(
          (a, b) =>
            new Date(b.updatedAt || 0).getTime() -
            new Date(a.updatedAt || 0).getTime()
        );
        break;
      case 'installs_desc':
        data.sort((a, b) => (b.installs || 0) - (a.installs || 0));
        break;
      case 'stars_desc':
        data.sort((a, b) => (b.stars || 0) - (a.stars || 0));
        break;
      case 'name_asc':
        data.sort((a, b) => a.name.localeCompare(b.name));
        break;
    }
    return data;
  });

  // 分页（卡片视图）
  const page = ref<number>(1);
  const pageSize = ref<number>(12);
  const pagedList = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    return filteredList.value.slice(start, start + pageSize.value);
  });
  const onPageSizeChange = () => {
    page.value = 1;
  };

  const reload = () => {
    EleMessage.success({ message: '已刷新', plain: true });
  };

  // 表格
  const tableColumns = ref<Columns>([
    { prop: 'logo', label: 'Logo', width: 70, align: 'center', slot: 'logo' },
    { prop: 'name', label: '名称', minWidth: 160 },
    { prop: 'vendor', label: '作者', width: 120, align: 'center' },
    { prop: 'version', label: '版本', width: 100, align: 'center' },
    { prop: 'category', label: '分类', width: 120, align: 'center' },
    { prop: 'stars', label: '评分', width: 100, align: 'center' },
    { prop: 'installs', label: '安装量', width: 120, align: 'center' },
    { prop: 'updatedAt', label: '更新', width: 180, align: 'center' },
    {
      prop: 'enabled',
      label: '状态',
      width: 100,
      align: 'center',
      slot: 'enabled'
    },
    {
      columnKey: 'action',
      label: '操作',
      width: 180,
      align: 'center',
      fixed: 'right',
      slot: 'action',
      hideInPrint: true,
      hideInExport: true
    }
  ]);
  const tableDatasource: DatasourceFunction = async (params?: any) => {
    const current = params?.page ?? params?.current ?? params?.currentPage ?? 1;
    const size = params?.size ?? params?.pageSize ?? params?.limit ?? 10;
    const start = (current - 1) * size;
    const tableList = filteredList.value.slice(start, start + size);
    return {
      list: tableList,
      count: filteredList.value.length
    };
  };

  // 详情与配置
  const showDetail = ref(false);
  const detailData = ref<PluginItem | null>(null);
  const openDetail = (row: PluginItem) => {
    detailData.value = row;
    showDetail.value = true;
  };

  const showConfig = ref(false);
  const configData = ref<PluginItem | null>(null);
  const configForm = ref({
    enabled: true,
    displayName: '',
    remark: ''
  });
  const openConfig = (row: PluginItem) => {
    configData.value = row;
    configForm.value.enabled = row.enabled;
    configForm.value.displayName = row.name;
    configForm.value.remark = '';
    showConfig.value = true;
  };
  const saveConfig = () => {
    EleMessage.success({ message: '已保存配置（示例）', plain: true });
    showConfig.value = false;
  };

  // 上传
  const showUpload = ref(false);
  const openUpload = () => (showUpload.value = true);
  const onUploadChange = () => {};
  const confirmUpload = () => {
    EleMessage.success({ message: '开始安装（示例）', plain: true });
    showUpload.value = false;
  };

  // 市场
  const openMarket = () => {
    EleMessage.info({ message: '即将跳转插件市场（示例）', plain: true });
  };

  const togglePlugin = (row: PluginItem, checked: boolean) => {
    row.enabled = checked;
    EleMessage.success({ message: checked ? '已启用' : '已停用', plain: true });
  };
  const moreAction = (cmd: string, row: PluginItem) => {
    switch (cmd) {
      case 'reinstall':
        EleMessage.success({ message: '已触发重新安装（示例）', plain: true });
        break;
      case 'disable':
        if (row.enabled) togglePlugin(row, false);
        break;
      case 'uninstall':
        list.value = list.value.filter((d) => d.id !== row.id);
        EleMessage.success({ message: '已卸载（示例）', plain: true });
        break;
    }
  };
</script>

<style scoped>
  .plugin-page :deep(.ele-page-body) {
    padding-top: 12px;
  }

  .page-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .page-hero__text .title {
    margin: 0;
    font-weight: 700;
    letter-spacing: 0.2px;
  }
  .page-hero__text .subtitle {
    margin: 6px 0 0;
    color: var(--el-text-color-secondary);
    font-size: 13px;
  }
  .page-hero__actions {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  .hero-search {
    width: 280px;
  }
  @media (max-width: 576px) {
    .page-hero {
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }
    .page-hero__actions {
      flex-wrap: wrap;
      gap: 8px;
    }
    .page-hero__actions > .el-button {
      flex: 1 1 auto;
    }
    .hero-search {
      width: 100%;
    }
  }

  .filter-card {
    margin-top: 12px;
  }
  .filters {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px 12px;
  }
  .filters-left {
    display: flex;
    gap: 8px;
    align-items: center;
  }
  .filters-right {
    display: flex;
    gap: 8px;
    align-items: center;
  }
  .filter-item {
    width: 160px;
  }
  @media (max-width: 576px) {
    .filters {
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }
    .filters-left,
    .filters-right {
      width: 100%;
      gap: 8px;
      flex-wrap: wrap;
    }
    .filter-item {
      width: 100%;
    }
  }

  .plugin-grid {
    margin-top: 12px;
  }
  .plugin-grid__wrap {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 12px;
  }
  @media (max-width: 1680px) {
    .plugin-grid__wrap {
      grid-template-columns: repeat(12, 1fr);
    }
  }
  @media (max-width: 1280px) {
    .plugin-grid__wrap {
      grid-template-columns: repeat(8, 1fr);
    }
  }
  @media (max-width: 992px) {
    .plugin-grid__wrap {
      grid-template-columns: repeat(6, 1fr);
    }
  }
  @media (max-width: 768px) {
    .plugin-grid__wrap {
      grid-template-columns: repeat(4, 1fr);
    }
  }
  @media (max-width: 576px) {
    .plugin-grid__wrap {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .plugin-card {
    grid-column: span 4;
    transition:
      transform 0.18s ease,
      box-shadow 0.18s ease;
  }
  .plugin-card:hover {
    transform: translateY(-2px);
  }
  @media (max-width: 768px) {
    .plugin-card {
      grid-column: span 4;
    }
  }
  /* 手机端一行一张卡片，保证可读性 */
  @media (max-width: 576px) {
    .plugin-card {
      grid-column: span 2;
    }
  }
  .plugin-card__header {
    display: grid;
    grid-template-columns: 56px 1fr auto;
    gap: 12px;
    align-items: center;
  }
  .plugin-card__logo {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--el-fill-color-light);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .plugin-card__logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .plugin-card__title .name {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .plugin-card__title .meta {
    margin-top: 4px;
    color: var(--el-text-color-secondary);
    font-size: 12px;
  }
  .ver {
    vertical-align: middle;
  }
  .plugin-card__desc {
    margin-top: 10px;
    color: var(--el-text-color-regular);
    min-height: 40px;
  }
  .plugin-card__badges {
    margin-top: 10px;
    display: flex;
    gap: 8px;
  }
  .plugin-card__footer {
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .stats {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--el-text-color-secondary);
    font-size: 12px;
  }
  .actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .table-logo {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--el-fill-color-light);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .table-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .empty-holder {
    padding: 48px 0;
  }
  .pagination-holder {
    display: flex;
    justify-content: flex-end;
    padding-top: 12px;
  }
  @media (max-width: 576px) {
    .pagination-holder {
      justify-content: center;
      padding-top: 8px;
    }
  }

  /* 动画 */
  .fade-list-move,
  .fade-list-enter-active,
  .fade-list-leave-active {
    transition: all 0.22s ease;
  }
  .fade-list-enter-from,
  .fade-list-leave-to {
    opacity: 0;
    transform: translateY(6px);
  }

  /* 详情 */
  .detail__head {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 12px;
    align-items: center;
    margin-bottom: 16px;
  }
  .detail__logo {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    background: var(--el-fill-color-light);
    object-fit: cover;
  }
  .detail__name {
    margin: 0 0 4px;
  }
  .detail__meta {
    margin: 0;
    color: var(--el-text-color-secondary);
    font-size: 12px;
  }
  .detail__desc {
    white-space: pre-wrap;
    line-height: 1.7;
  }
  .detail__stats {
    color: var(--el-text-color-secondary);
    display: flex;
    gap: 8px;
    align-items: center;
  }
  .drawer-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  .upload-area {
    width: 100%;
  }
</style>
