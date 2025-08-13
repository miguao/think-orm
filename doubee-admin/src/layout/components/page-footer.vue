<template>
  <ele-text type="placeholder" class="ele-footer">
    <div style="margin-top: 8px">
      Copyright © 2025 成都纳米信息科技有限公司
    </div>
  </ele-text>
</template>

<script lang="ts" setup></script>

<style lang="scss" scoped>
  @use 'element-plus/theme-chalk/src/mixins/function.scss' as *;

  .ele-footer {
    padding: 16px 0;
    text-align: center;
    #{getCssVarName('text-color', 'regular')}: getCssVar(
      'text-color',
      'placeholder'
    );
  }
</style>
