<template>
  <ele-modal
    form
    destroy-on-close
    :width="850"
    v-model="visible"
    :title="isUpdate ? '修改配置字段' : '添加配置字段'"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
      label-position="top"
    >
      <el-row :gutter="16">
        <el-col :sm="12" :xs="24">
          <el-form-item label="配置标题" prop="title">
            <el-input
              clearable
              :maxlength="50"
              v-model="form.title"
              placeholder="请输入配置标题"
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
          <el-form-item label="组件类型" prop="component_type">
            <dict-data
              type="select"
              code="component_type"
              v-model="form.component_type"
              placeholder="请选择组件类型"
            />
          </el-form-item>
        </el-col>

        <el-col
          :sm="12"
          :xs="24"
          v-if="
            form.component_type === 'checkbox' ||
            form.component_type === 'radio' ||
            form.component_type === 'select'
          "
        >
          <el-form-item label="数据模式" prop="data_mode">
            <el-radio-group v-model="form.data_mode">
              <el-radio :value="0" border>字典模式</el-radio>
              <el-radio :value="1" border>自定义模式</el-radio>
            </el-radio-group>
          </el-form-item>
        </el-col>

        <!-- 字典模式 -->
        <el-col
          :sm="24"
          :xs="24"
          v-if="
            form.data_mode === 0 &&
            (form.component_type === 'checkbox' ||
              form.component_type === 'radio' ||
              form.component_type === 'select')
          "
        >
          <el-form-item label="字典键名" prop="dict_key">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.dict_key"
              placeholder="请输入字典键名"
            />
          </el-form-item>
        </el-col>

        <!-- 自定义模式 -->
        <el-col :sm="24" :xs="24" v-if="form.data_mode === 1">
          <el-form-item label="组件数据" prop="component_data"> </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="是否必填" prop="required">
            <el-radio-group v-model="form.required">
              <el-radio :value="0" border>否</el-radio>
              <el-radio :value="1" border>是</el-radio>
            </el-radio-group>
          </el-form-item>
        </el-col>

        <el-col :sm="24" :xs="24">
          <el-form-item label="正则表达式" prop="pattern">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.pattern"
              placeholder="请输入正则表达式"
            />
          </el-form-item>
        </el-col>

        <el-col :sm="24" :xs="24" v-if="form.required === 1">
          <el-form-item label="错误提示信息" prop="error_message">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.error_message"
              placeholder="请输入错误提示信息"
            />
          </el-form-item>
        </el-col>

        <el-col :sm="12" :xs="24">
          <el-form-item label="排序" prop="sort">
            <el-input-number
              type="radio"
              :min="0"
              :max="9999"
              v-model="form.sort"
              placeholder="请输入排序号，数字越大越靠前"
              controls-position="right"
              class="ele-fluid"
            />
          </el-form-item>
        </el-col>

        <!-- 备注 -->
        <el-col :sm="12" :xs="24">
          <el-form-item label="备注信息" prop="remark">
            <el-input
              clearable
              :maxlength="20"
              v-model="form.remark"
              placeholder="请输入备注信息"
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
  import { Config, ConfigField } from '@/api/system/config/model';
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
  const [form, resetFields, assignFields] = useFormData<ConfigField>({
    id: void 0,
    config_id: 0,
    key: '',
    title: '',
    value: '',
    component_type: '',
    component_data: '',
    data_mode: 0,
    dict_key: '',
    required: 0,
    pattern: '',
    error_message: '',
    sort: void 0,
    remark: ''
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    title: [
      {
        required: true,
        message: '请输入配置标题',
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
    ],
    component_type: [
      {
        required: true,
        message: '请选择组件类型',
        type: 'string',
        trigger: 'blur'
      }
    ],
    data_mode: [
      {
        required: true,
        message: '请选择数据模式',
        type: 'number',
        trigger: 'blur'
      }
    ],
    dict_key: [
      {
        required: true,
        message: '请输入字典键名',
        type: 'string',
        trigger: 'blur'
      }
    ],
    required: [
      {
        required: true,
        message: '请选择是否必填',
        type: 'number',
        trigger: 'blur'
      }
    ],
    error_message: [
      {
        required: true,
        message: '请输入错误提示信息',
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
