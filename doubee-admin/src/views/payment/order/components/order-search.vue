<template>
  <ele-card :body-style="{ paddingBottom: '2px' }">
    <el-form
      @keyup.enter.prevent="search"
      @submit.prevent=""
    >
      <el-row :gutter="8">
        <el-col :lg="6" :md="8" :sm="12" :xs="24">
          <el-form-item label="系统订单号">
            <el-input
              clearable
              v-model.trim="form['search-trade_no']"
              placeholder="请输入系统订单号"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="6" :md="8" :sm="12" :xs="24">
          <el-form-item label="商户订单号">
            <el-input
              clearable
              v-model.trim="form['search-out_trade_no']"
              placeholder="请输入商户订单号"
            />
          </el-form-item>
        </el-col>

        <el-col :lg="12" :md="8" :sm="24" :xs="24">
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
  import type { SearchParam } from '@/api/payment/bank/model';
  import { useFormData } from '@/utils/use-form-data';

  const emit = defineEmits<{
    (e: 'search', where?: SearchParam): void;
  }>();

  /** 表单数据 */
  const [form, resetFields] = useFormData<SearchParam>({
    'search-name': '',
    'search-code': ''
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
</script>
