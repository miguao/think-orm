<template>
  <ele-modal
    form
    destroy-on-close
    :width="660"
    v-model="visible"
    :title="isUpdate ? '修改配置' : '添加配置'"
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
          <el-form-item label="配置名称" prop="name">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.name"
              placeholder="请输入配置名称"
            />
          </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="配置键" prop="key">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.key"
              placeholder="请输入配置键"
            />
          </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="是否公开" prop="public">
            <el-radio-group v-model="form.public">
              <el-radio :value="0" size="large">私有</el-radio>
              <el-radio :value="1" size="large">公开</el-radio>
            </el-radio-group>
          </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="状态" prop="status">
            <dict-data
              type="radio"
              code="general_status"
              v-model="form.status"
              placeholder="请选择状态"
            />
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
  import { addConfig, updateConfig } from '@/api/system/config';
  import { Config } from '@/api/system/config/model';
  import { useFormData } from '@/utils/use-form-data';
  import { EleMessage } from 'ele-admin-plus';
  import type { FormInstance, FormRules } from 'element-plus';
  import { reactive, ref, watch } from 'vue';

  const props = defineProps<{
    data?: Config | null;
    configId?: number;
  }>();

  const emit = defineEmits<{
    (e: 'done'): void;
  }>();

  /** 弹窗是否打开 */
  const visible = defineModel({ type: Boolean });

  /** 是否是修改 */
  const isUpdate = ref(false);

  /** 表单实例 */
  const formRef = ref<FormInstance | null>(null);

  /** 表单数据 */
  const [form, resetFields, assignFields] = useFormData<Config>({
    id: void 0,
    name: '',
    key: '',
    public: 0,
    status: 1
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    name: [
      {
        required: true,
        message: '请输入配置名称',
        type: 'string',
        trigger: 'blur'
      }
    ],
    key: [
      {
        required: true,
        message: '请输入配置键',
        type: 'string',
        trigger: 'blur'
      }
    ]
  });

  /** 关闭弹窗 */
  const handleCancel = () => {
    visible.value = false;
  };

  /** 提交状态 */
  const loading = ref(false);

  /** 保存编辑 */
  const save = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;
      const saveOrUpdate = isUpdate.value ? updateConfig : addConfig;

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
