<template>
  <ele-modal
    form
    destroy-on-close
    :width="460"
    v-model="visible"
    title="创建应用"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
    >
      <el-form-item label="应用名称" prop="name">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.name"
          placeholder="请输入应用名称"
        />
      </el-form-item>

      <el-form-item label="应用模式" prop="mode">
        <el-select v-model="form.mode" placeholder="请选择应用模式">
          <el-option label="自研开发" :value="0" />
          <el-option label="委托授权代开发" :value="1" />
        </el-select>
      </el-form-item>

      <!-- <el-form-item label="状态" prop="status">
        <dict-data
          type="radio"
          code="general_status"
          v-model="form.status"
          placeholder="请选择状态"
        />
      </el-form-item> -->
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
  import type { Application } from '@/api/application/model';
  import { createApplication } from '@/api/application';

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
    name: '',
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    name: [
      {
        required: true,
        message: '请输入应用名称',
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
      createApplication(form)
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
