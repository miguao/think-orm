<template>
  <ele-modal
    form
    destroy-on-close
    :width="460"
    v-model="visible"
    :title="isUpdate ? '修改银行' : '添加银行'"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
      @submit.prevent=""
    >
      <el-form-item label="银行图标" prop="icon">
        <ele-upload-list
          :limit="1"
          :drag="true"
          :item-style="{ width: '64px', height: '64px', margin: 0 }"
          :button-style="{ width: '64px', height: '64px', margin: 0 }"
          v-model="uploadIcon"
          @upload="onUpload"
          @remove="onRemove"
        />
      </el-form-item>

      <el-form-item label="银行名称" prop="name">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.name"
          placeholder="请输入银行名称"
        />
      </el-form-item>

      <el-form-item label="银行代码" prop="code">
        <el-input
          clearable
          :maxlength="20"
          v-model="form.code"
          placeholder="请输入银行代码"
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
  import { ref, reactive, watch } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import { useFormData } from '@/utils/use-form-data';
  import type { Bank } from '@/api/payment/bank/model';
  import { addBank, updateBank } from '@/api/payment/bank';
  import { UploadItem } from 'ele-admin-plus/es/ele-upload-list/types';
  import { UPLOAD_URL } from '@/config/setting';
  import { uploadFile } from '@/api/system/file';

  const props = defineProps<{
    data?: Bank | null;
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
  const [form, resetFields, assignFields] = useFormData<Bank>({
    id: void 0,
    icon: '',
    name: '',
    code: '',
    status: 0
  });

  /** 表单验证规则 */
  const rules = reactive<FormRules>({
    icon: [
      {
        required: true,
        message: '请上传图标',
        type: 'string',
        trigger: 'blur'
      }
    ],
    name: [
      {
        required: true,
        message: '请输入银行名称',
        type: 'string',
        trigger: 'blur'
      }
    ],
    code: [
      {
        required: true,
        message: '请输入银行代码',
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

  /** 上传图标 */
  const uploadIcon = ref<UploadItem[]>([]);

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
      const saveOrUpdate = isUpdate.value ? updateBank : addBank;
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

  /** 上传事件 */
  const onUpload = (uploadItem: UploadItem) => {
    if (!uploadItem.file) {
      return;
    }

    if (!uploadItem.file.type.startsWith('image')) {
      EleMessage.error('只能选择图片');
      return;
    }

    if (uploadItem.file.size / 1024 / 1024 > 2) {
      EleMessage.error('大小不能超过 2MB');
      return;
    }

    uploadIcon.value.push({ ...uploadItem });

    const item = uploadIcon.value.find((t) => t.key === uploadItem.key);
    if (!item) {
      return;
    }

    item.status = 'uploading';
    uploadFile(uploadItem.file, {
      onUploadProgress: (event: any) => {
        if (event.total != null) {
          item.progress = (event.loaded / event.total) * 100;
        }
      }
    })
      .then((response) => {
        item.status = 'done';
        item.url = UPLOAD_URL + response.path;
        form.icon = response.path;
        formRef.value?.clearValidate?.('images');
      })
      .catch((exception) => {
        item.status = 'exception';
        EleMessage.error(exception.message);
      });
  };

  /** 删除事件 */
  const onRemove = (item: UploadItem) => {
    form.icon = '';
    uploadIcon.value.splice(uploadIcon.value.indexOf(item), 1);
  };

  /** 监听弹窗打开 */
  watch(visible, () => {
    if (visible.value) {
      if (props.data) {
        uploadIcon.value = [
          {
            key: 1,
            name: 'icon',
            url: UPLOAD_URL + props.data.icon,
            status: 'done'
          }
        ];
        assignFields(props.data);
        isUpdate.value = true;
      } else {
        uploadIcon.value = [];
        resetFields();
        isUpdate.value = false;
      }
    }
  });
</script>

<style scoped>
  .avatar-uploader .avatar {
    width: 80px;
    height: 80px;
    display: block;
  }
  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 80px;
    height: 80px;
    text-align: center;
    line-height: 80px;
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
  }
  .avatar-uploader-icon:hover {
    border-color: #409eff;
  }
</style>
