<template>
  <ele-modal
    form
    destroy-on-close
    :width="460"
    v-model="visible"
    :title="isUpdate ? '修改角色' : '添加角色'"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
    >
      <el-form-item label="角色名称" prop="name">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.name"
          placeholder="请输入角色名称"
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
  import { ref, reactive, watch } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import { useFormData } from '@/utils/use-form-data';
  import { addRole, updateRole } from '@/api/system/role';
  import type { Role } from '@/api/system/role/model';

  const props = defineProps<{
    /** 修改回显的数据 */
    data?: Role | null;
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
  const [form, resetFields, assignFields] = useFormData<Role>({
    id: void 0,
    name: ''
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    name: [
      {
        required: true,
        message: '请输入角色名称',
        type: 'string',
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
      const saveOrUpdate = isUpdate.value ? updateRole : addRole;
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
