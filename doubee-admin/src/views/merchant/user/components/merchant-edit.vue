<template>
  <ele-modal
    form
    destroy-on-close
    :width="680"
    v-model="visible"
    :title="isUpdate ? '修改商户' : '添加商户'"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
    >
      <el-row :gutter="16">
        <el-col :sm="12" :xs="24">
          <el-form-item label="邮箱号码" prop="email">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.email"
              placeholder="请输入邮箱号码"
            />
          </el-form-item>

          <el-form-item label="手机号码" prop="phone">
            <el-input
              clearable
              :maxlength="11"
              v-model="form.phone"
              placeholder="请输入手机号码"
            />
          </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="用户组" prop="group_id">
            <group-select v-model="form.group_id" />
          </el-form-item>

          <el-form-item v-if="!isUpdate" label="登录密码" prop="password">
            <el-input
              show-password
              type="password"
              :maxlength="20"
              v-model="form.password"
              placeholder="请输入登录密码"
            />
          </el-form-item>

          <el-form-item label="状态">
            <el-radio-group v-model="form.status">
              <el-radio :value="0" label="停用" />
              <el-radio :value="1" label="启用" />
            </el-radio-group>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer>
      <el-button @click="handleCancel">取消</el-button>
      <el-button type="primary" :loading="loading" @click="save">
        保存
      </el-button>
    </template>
  </ele-modal>
</template>

<script lang="ts" setup>
  import { ref, reactive, watch } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import { useFormData } from '@/utils/use-form-data';
  import groupSelect from '../../group/components/group-select.vue';
  import type { Merchant } from '@/api/merchant/user/model';
  import { addMerchant, updateMerchant } from '@/api/merchant/user';

  const props = defineProps<{
    data?: Merchant | null;
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
  const [form, resetFields, assignFields] = useFormData<Merchant>({
    id: void 0,
    group_id: undefined,
    merchant_no: undefined,
    email: '',
    phone: '',
    password: '',
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    role_id: [
      {
        required: true,
        message: '请选择用户组',
        type: 'number',
        trigger: 'blur'
      }
    ],
    password: [
      {
        required: true,
        message: '请输入登录密码',
        type: 'string',
        trigger: 'blur'
      },
      {
        type: 'string',
        trigger: 'blur',
        validator: (_rule: any, value: string, callback: any) => {
          if (isUpdate.value || /^[\S]{5,18}$/.test(value)) {
            return callback();
          }
          callback(new Error('密码必须为5-18位非空白字符'));
        }
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

      const saveOrUpdate = isUpdate.value ? updateMerchant : addMerchant;
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
        assignFields({ ...props.data, password: '' });
        isUpdate.value = true;
      } else {
        resetFields();
        isUpdate.value = false;
      }
    }
  });
</script>
