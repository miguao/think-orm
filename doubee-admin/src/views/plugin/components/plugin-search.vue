<template>
  <ele-card
    :body-style="{ paddingTop: '12px', paddingBottom: '12px' }"
    class="filter-card"
  >
    <div class="filters">
      <div class="filters-left">
        <el-select class="filter-item" placeholder="全部分类" clearable>
          <el-option label="通用插件" value="general" />
          <el-option label="支付插件" value="payment" />
        </el-select>

        <el-select class="filter-item" placeholder="全部状态" clearable>
          <el-option label="未启用" value="disabled" />
          <el-option label="运行中" value="enabled" />
        </el-select>
      </div>
    </div>
  </ele-card>
</template>
<script lang="ts" setup></script>

<style lang="scss" scoped>
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

  .filter-item {
    width: 160px;
  }

  @media (max-width: 576px) {
    .filters {
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }

    .filters-left {
      width: 100%;
      gap: 8px;
      flex-wrap: wrap;
    }

    .filter-item {
      width: 100%;
    }
  }
</style>
