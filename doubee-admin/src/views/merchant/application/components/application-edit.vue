<template>
  <ele-drawer v-model="visible" :title="isUpdate ? '修改应用' : '添加应用'">
    <el-form
      ref="formRef"
      label-position="top"
      :model="form"
      :rules="rules"
      @submit.prevent=""
    >
      <el-form-item label="选择商户" prop="merchant_id">
        <dict-data
          code="merchant,id,merchant_no"
          v-model="form.merchant_id"
          placeholder="请选择商户号"
          clearable
          filterable
        />
      </el-form-item>

      <el-form-item label="应用名称" prop="name">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.name"
          placeholder="请输入应用名称"
        />
      </el-form-item>

      <el-form-item label="签名方式" prop="sign_type" v-if="isUpdate">
        <dict-data
          type="radio"
          code="application_sign_type"
          v-model="form.sign_type"
          placeholder="请选择签名方式"
          :disabled="isUpdate"
        />
      </el-form-item>

      <el-form-item
        label="应用密钥"
        prop="secret"
        v-if="isUpdate && form.sign_type === 0"
      >
        <el-input v-model="form.secret" placeholder="请输入应用密钥" disabled />
      </el-form-item>

      <el-form-item
        label="应用私钥"
        prop="private_key"
        v-if="isUpdate && form.sign_type === 1"
      >
        <el-input
          v-model="form.private_key"
          :rows="10"
          type="textarea"
          disabled
        />
      </el-form-item>

      <el-form-item
        label="应用公钥"
        prop="public_key"
        v-if="isUpdate && form.sign_type === 1"
      >
        <el-input
          v-model="form.public_key"
          :rows="10"
          type="textarea"
          disabled
        />
      </el-form-item>

      <el-form-item label="状态" prop="status">
        <dict-data
          type="radio"
          code="general_status"
          v-model="form.status"
          placeholder="请选择状态"
        />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="handleCancel">取消</el-button>
      <el-button type="primary" :loading="loading" @click="save">
        保存
      </el-button>
    </template>
  </ele-drawer>
</template>
<script lang="ts" setup>
  import {
    addApplication,
    updateApplication
  } from '@/api/merchant/application';
  import type { Application } from '@/api/merchant/application/model';
  import { useFormData } from '@/utils/use-form-data';
  import { FormInstance, FormRules } from 'element-plus';
  import { reactive, ref, watch } from 'vue';
  import { EleMessage } from 'ele-admin-plus';

  const props = defineProps<{
    data?: Application | null;
  }>();

  const emit = defineEmits<{
    (e: 'done'): void;
  }>();

  /** 弹窗是否打开 */
  const visible = defineModel({ type: Boolean });

  /** 是否是修改 */
  const isUpdate = ref(false);

  /** 提交状态 */
  const loading = ref(false);

  /** 表单实例 */
  const formRef = ref<FormInstance | null>(null);

  /** 表单数据 */
  const [form, resetFields, assignFields] = useFormData<Application>({
    id: void 0,
    merchant_id: undefined,
    name: '',
    secret: '',
    private_key: '',
    public_key: '',
    sign_type: 0,
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    merchant_id: [
      {
        required: true,
        message: '请选择商户',
        type: 'number',
        trigger: 'blur'
      }
    ],
    name: [
      {
        required: true,
        message: '请输入应用名称',
        type: 'string',
        trigger: 'blur'
      }
    ],
    sign_type: [
      {
        required: true,
        message: '请选择签名方式',
        type: 'number',
        trigger: 'blur'
      }
    ],
    secret: [
      {
        required: true,
        message: '请输入商户密钥',
        type: 'string',
        trigger: 'blur'
      }
    ],
    private_key: [
      {
        required: true,
        message: '请输入应用私钥',
        type: 'string',
        trigger: 'blur'
      }
    ],
    public_key: [
      {
        required: true,
        message: '请输入应用公钥',
        type: 'string',
        trigger: 'blur'
      }
    ],
    status: [
      {
        required: true,
        message: '请选择状态',
        type: 'number',
        trigger: 'blur'
      }
    ]
  });

  /** 关闭弹窗 */
  const handleCancel = () => {
    visible.value = false;
  };

  /** 保存编辑 */
  const save = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;
      const saveOrUpdate = isUpdate.value ? updateApplication : addApplication;
      saveOrUpdate(form)
        .then((message) => {
          loading.value = false;
          EleMessage.success({ message: message, plain: true });
          handleCancel();
          emit('done');
        })
        .catch((exception) => {
          loading.value = false;
          EleMessage.error({ message: exception.message, plain: true });
        });
    });
  };

  /** 监听弹窗打开 */
  watch(visible, () => {
    if (visible.value) {
      if (props.data) {
        assignFields(props.data);
        isUpdate.value = true;
      } else {
        resetFields();
        isUpdate.value = false;
      }
    }
  });
</script>
