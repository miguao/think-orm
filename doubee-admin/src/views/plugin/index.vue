<template>
  <ele-page class="plugin-page">
    <div class="page-hero">
      <div class="page-hero__text">
        <h2 class="title">插件管理</h2>
        <p class="subtitle">
          为系统提供按需扩展能力，通过灵活启用与配置官方插件及第三方插件，实现功能模块的动态拓展与生态能力的持续增强，从而满足不同业务场景下的个性化需求。
        </p>
      </div>

      <div class="page-hero__actions">
        <el-input
          class="hero-search"
          placeholder="搜索插件名称 / 作者 / 描述"
          clearable
          :prefix-icon="SearchOutlined"
        />
      </div>
    </div>

    <!-- 搜索组件 -->
    <plugin-search />

    <!-- 表格组件 -->
    <div class="plugin-grid">
      <transition-group name="fade-list" tag="div" class="plugin-grid__wrap">
        <ele-card
          class="plugin-card"
          v-for="item in datasource"
          :key="item.id"
          :body-style="{ padding: '16px' }"
          shadow="hover"
        >
          <div class="plugin-card__header">
            <div class="plugin-card__logo">
              <img :src="item.icon" alt="logo" />
            </div>

            <div class="plugin-card__title">
              <div class="name">
                {{ item.info.name }}
                <el-tag size="small" type="info" effect="dark" class="ver"
                  >v{{ item.info.version }}</el-tag
                >
              </div>

              <div class="meta">
                <span> 官方 </span>
                <el-divider direction="vertical" />
                <span v-if="item.info.type === 'general'"> 通用插件 </span>
                <span v-if="item.info.type === 'payment'"> 支付插件 </span>
              </div>
            </div>

            <div class="plugin-card__switch">
              <el-switch inline-prompt />
            </div>
          </div>

          <div class="plugin-card__desc">
            <ele-ellipsis :line="2">{{ item.info.description }}</ele-ellipsis>
          </div>

          <div class="plugin-card__badges">
            <el-tag size="small" type="success" effect="light">官方</el-tag>
            <el-tag size="small" type="warning" effect="light">推荐</el-tag>
          </div>

          <div class="plugin-card__footer">
            <div class="stats">
              <span>安装时间：2025-11-26 10:00:00</span>
            </div>

            <div class="actions">
              <el-button size="small" @click="openDetail(item.info)"
                >详情</el-button
              >
              <el-button size="small" v-if="item.info.type === 'general'">
                配置
              </el-button>

              <ele-dropdown
                style="margin-left: 12px"
                :items="[
                  { title: '重新安装', command: 'reinstall' },
                  { title: '卸载', command: 'uninstall' }
                ]"
              >
                <el-button size="small">更多</el-button>
              </ele-dropdown>
            </div>
          </div>
        </ele-card>
      </transition-group>

      <!-- 插件详情 -->
      <plugin-details v-model="showDetail" />
    </div>
  </ele-page>
</template>

<script lang="ts" setup>
  import SearchOutlined from 'ele-admin-plus/es/icons/SearchOutlined.vue';
  import PluginSearch from './components/plugin-search.vue';
  import { getInstalledPlugins } from '@/api/plugin';
  import type { PluginInfo } from '@/api/plugin/model';
  import PluginDetails from './components/plugin-details.vue';
  import { onMounted, ref } from 'vue';

  defineOptions({ name: 'Plugin' });

  /** 数据源 */
  const datasource = ref<any[]>([]);

  /** 是否显示详情 */
  const showDetail = ref<boolean>(false);

  /** 详情数据 */
  const detailData = ref<PluginInfo | null>(null);

  /** 打开详情 */
  const openDetail = (info: PluginInfo) => {
    showDetail.value = true;
    detailData.value = info;
  };

  const UPLOAD_URL = import.meta.env.VITE_UPLOAD_URL;

  onMounted(() => {
    getInstalledPlugins({ page: 1, limit: 15 }).then((response) => {
      datasource.value = (response?.list ?? []).map((item) => ({
        ...item,
        icon: UPLOAD_URL + '/plugin/' + item.identifier + '/Icon.ico'
      }));
    });
  });
</script>

<style lang="scss" scoped>
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
</style>

<style lang="scss" scoped>
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
  }
</style>
