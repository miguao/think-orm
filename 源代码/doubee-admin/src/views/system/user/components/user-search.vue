<template>
  <el-form label-width="72px" @keyup.enter="search" @submit.prevent="">
    <el-row :gutter="8">
      <el-col :lg="6" :md="12" :sm="12" :xs="24">
        <el-form-item label="登录邮箱">
          <el-input
            clearable
            v-model.trim="form['search-email']"
            placeholder="请输入登录邮箱"
          />
        </el-form-item>
      </el-col>

      <el-col :lg="6" :md="12" :sm="12" :xs="24">
        <el-form-item label="用户昵称">
          <el-input
            clearable
            v-model.trim="form['search-nickname']"
            placeholder="请输入用户昵称"
          />
        </el-form-item>
      </el-col>

      <el-col :lg="6" :md="12" :sm="12" :xs="24">
        <el-form-item label-width="16px">
          <el-button type="primary" @click="search">查询</el-button>
          <el-button @click="reset">重置</el-button>
        </el-form-item>
      </el-col>
    </el-row>
  </el-form>
</template>

<script lang="ts" setup>
  import { useFormData } from '@/utils/use-form-data';
  import type { SearchParam } from '@/api/system/user/model';

  const emit = defineEmits<{
    (e: 'search', where?: SearchParam): void;
  }>();

  /** 表单数据 */
  const [form, resetFields] = useFormData<SearchParam>({
    'search-email': '',
    'search-nickname': ''
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
