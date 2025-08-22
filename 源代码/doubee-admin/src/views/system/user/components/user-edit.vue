<template>
  <ele-modal
    form
    destroy-on-close
    :width="680"
    v-model="visible"
    :title="isUpdate ? '修改用户' : '添加用户'"
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
          <el-form-item label="所属机构" prop="institution_id">
            <institution-select v-model="form.institution_id" />
          </el-form-item>

          <el-form-item label="登录邮箱" prop="email">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.email"
              placeholder="请输入登录邮箱"
              :disabled="isUpdate"
            />
          </el-form-item>

          <el-form-item label="用户昵称" prop="nickname">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.nickname"
              placeholder="请输入用户昵称"
            />
          </el-form-item>
        </el-col>
        <el-col :sm="12" :xs="24">
          <el-form-item label="角色" prop="roles">
            <role-select v-model="form.roles" />
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
  import { EleMessage, emailReg } from 'ele-admin-plus';
  import { useFormData } from '@/utils/use-form-data';
  import RoleSelect from '@/views/system/role/components/role-select.vue';
  import InstitutionSelect from '@/views/system/institution/components/institution-select.vue';
  import { addUser, updateUser } from '@/api/system/user';
  import type { User } from '@/api/system/user/model';

  const props = defineProps<{
    /** 修改回显的数据 */
    data?: User | null;
    /** 添加时机构id */
    institutionId?: number;
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
  const [form, resetFields, assignFields] = useFormData<User>({
    id: void 0,
    institution_id: void 0,
    email: '',
    nickname: '',
    roles: [],
    password: '',
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    institution_id: [
      {
        required: true,
        message: '请选择所属机构',
        type: 'number',
        trigger: 'change'
      }
    ],
    email: [
      {
        required: true,
        message: '请输入登录邮箱',
        type: 'string',
        trigger: 'blur'
      },
      {
        pattern: emailReg,
        message: '邮箱格式不正确',
        type: 'string',
        trigger: 'blur'
      }
    ],
    roles: [
      {
        required: true,
        message: '请选择角色',
        type: 'array',
        trigger: 'change'
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

      const saveOrUpdate = isUpdate.value ? updateUser : addUser;
      const payload = {
        ...form,
        roles: (form.roles ?? []).map((role: any) =>
          typeof role === 'object' ? role.id : role
        )
      };
      saveOrUpdate(payload)
        .then((message) => {
          loading.value = false;
          EleMessage.success({ message: message, plain: true });
          handleCancel();
          emit('done');
        })
        .catch((e) => {
          loading.value = false;
          EleMessage.error({ message: e.message, plain: true });
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
        form.institution_id = props.institutionId;
        isUpdate.value = false;
      }
    }
  });
</script>
