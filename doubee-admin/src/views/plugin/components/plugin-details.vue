<template>
  <ele-modal v-model="visible" title="插件信息">
    <!-- <div class="detail">
      <div class="detail__head">
        <img :src="detailData?.icon" class="detail__logo" />
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
          ><el-icon><StarFilled /></el-icon> {{ detailData?.stars ?? 0 }}</span
        >
        <el-divider direction="vertical" />
        <span>安装量 {{ detailData?.installs ?? 0 }}</span>
        <el-divider direction="vertical" />
        <span>状态：{{ detailData?.enabled ? '已启用' : '未启用' }}</span>
      </div>
    </div> -->
  </ele-modal>
</template>

<script lang="ts" setup>
  import { PluginInfo } from '@/api/plugin/model';

  defineOptions({ name: 'PluginDetails' });

  /** 是否显示 */
  const visible = defineModel({ type: Boolean });

  /** 详情数据 */
  // const detailData = defineModel<PluginInfo>({ type: Object });
</script>
