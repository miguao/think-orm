<template>
  <ele-card :body-style="{ paddingBottom: '2px' }">
    <el-form label-width="84px" @keyup.enter="search" @submit.prevent="">
      <el-row :gutter="8">
        <el-col :lg="8" :md="8" :sm="12" :xs="24">
          <el-form-item label="配置名称">
            <el-input
              clearable
              v-model.trim="form['search-name']"
              placeholder="请输入配置名称"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="8" :md="8" :sm="12" :xs="24">
          <el-form-item label="配置键">
            <el-input
              clearable
              v-model.trim="form['search-key']"
              placeholder="请输入配置键"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="8" :md="8" :sm="12" :xs="24">
          <el-form-item label-width="16px">
            <el-button type="primary" @click="search">查询</el-button>
            <el-button @click="reset">重置</el-button>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
  </ele-card>
</template>

<script lang="ts" setup>
  import type { SearchParam } from '@/api/system/config/model';
  import { useFormData } from '@/utils/use-form-data';

  const emit = defineEmits<{
    (e: 'search', where?: SearchParam): void;
  }>();

  /** 表单数据 */
  const [form, resetFields] = useFormData<SearchParam>({
    'search-name': '',
    'search-key': ''
  });

  /** 搜索 */
  const search = () => {
    emit('search', { ...form });
  };

  /**  重置 */
  const reset = () => {
    resetFields();
    search();
  };

  defineExpose({ resetFields });
</script>
