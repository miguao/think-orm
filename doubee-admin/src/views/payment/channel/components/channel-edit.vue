<template>
  <ele-modal
    form
    destroy-on-close
    :width="460"
    v-model="visible"
    :title="isUpdate ? '修改通道' : '添加通道'"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
    >
      <el-form-item label="通道名称" prop="name">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.name"
          placeholder="请输入通道名称"
        />
      </el-form-item>

      <el-form-item label="选择银行" prop="type">
        <dict-data
          code="payment_bank,code,name"
          v-model="form.type"
          placeholder="请选择银行"
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
  </ele-modal>
</template>
<script lang="ts" setup>
  import { addChannel, updateChannel } from '@/api/payment/channel';
  import { Channel } from '@/api/payment/channel/model';
  import { useFormData } from '@/utils/use-form-data';
  import { FormInstance, FormRules } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import { reactive, ref, watch } from 'vue';

  const props = defineProps<{
    data?: Channel | null;
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
  const [form, resetFields, assignFields] = useFormData<Channel>({
    id: void 0,
    name: '',
    type: undefined,
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({});

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
      const saveOrUpdate = isUpdate.value ? updateChannel : addChannel;
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
