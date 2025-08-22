<template>
  <ele-drawer
    form
    v-model="visible"
    :title="'管理配置: ' + props.data?.name + '(' + props.data?.key + ')'"
    :append-to-body="true"
    :size="600"
    style="max-width: 100%"
    :destroy-on-close="true"
    :show-close="false"
  >
    <template #default>
      <el-space>
        <el-button type="primary" class="ele-btn-icon" :icon="Setting">
          字段配置
        </el-button>
      </el-space>
      <el-divider border-style="dashed" content-position="left">
        配置数据
      </el-divider>

      <el-form
        ref="formRef"
        label-position="top"
        :model="form"
        :rules="rules"
        :show-message="false"
      >
        <el-row :gutter="16">
          <el-col
            :sm="24"
            :xs="24"
            v-for="item in props.data?.configData"
            :key="item.id"
          >
            <el-form-item
              :label="item.title"
              v-if="item.component_type !== 'tips'"
              :prop="item.key"
              :style="item.remark ? 'margin-bottom: 0' : ''"
            >
              <!-- Input 输入框 -->
              <el-input
                v-if="item.component_type === 'input'"
                clearable
                v-model="form[item.key]"
                :placeholder="'请输入' + item.title"
              />

              
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </template>

    <template #footer>
      <div style="flex: auto">
        <el-button>取消</el-button>
        <el-button type="primary">保存配置</el-button>
      </div>
    </template>
  </ele-drawer>
</template>
<script setup lang="ts">
  import type { Config } from '@/api/system/config/model';
  import { useFormData } from '@/utils/use-form-data';
  import { Setting } from '@element-plus/icons-vue';
  import type { FormInstance } from 'element-plus';
  import { reactive, ref, watch } from 'vue';

  const props = defineProps<{
    data?: Config | null;
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
    id: undefined,
    name: '',
    key: ''
  });

  // 表单验证规则
  const rules = reactive({});

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
