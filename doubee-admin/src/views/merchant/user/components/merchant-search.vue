<template>
  <ele-card :body-style="{ paddingBottom: '2px' }">
    <el-form label-width="72px" @keyup.enter="search" @submit.prevent="">
      <el-row :gutter="8">
        <el-col :lg="6" :md="12" :sm="12" :xs="24">
          <el-form-item label="商户号">
            <el-input
              clearable
              v-model.trim="form['search-merchant_no']"
              placeholder="请输入商户号"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="6" :md="12" :sm="12" :xs="24">
          <el-form-item label="手机号码">
            <el-input
              clearable
              v-model.trim="form['search-phone']"
              placeholder="请输入手机号码"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="6" :md="12" :sm="12" :xs="24">
          <el-form-item label="邮箱号码">
            <el-input
              clearable
              v-model.trim="form['search-email']"
              placeholder="请输入邮箱号码"
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
  </ele-card>
</template>

<script lang="ts" setup>
  import { SearchParam } from '@/api/merchant/user/model';
  import { useFormData } from '@/utils/use-form-data';

  const emit = defineEmits<{
    (e: 'search', where?: SearchParam): void;
  }>();

  /** 表单数据 */
  const [form, resetFields] = useFormData<SearchParam>({
    'search-merchant_no': undefined,
    'search-phone': '',
    'search-email': ''
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
