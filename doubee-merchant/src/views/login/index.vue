<template>
  <div class="login-wrapper">
    <div class="login-main">
      <ele-card shadow="always" class="login-card">
        <div class="login-cover">
          <h1 class="login-title">{{ PROJECT_NAME }}</h1>
          <h4 class="login-subtitle">畅享高效、便捷的支付服务，从登录开始</h4>
        </div>
        <div class="login-body">
          <ele-text type="heading" style="font-size: 24px">
            轻松收款，从登录开始
          </ele-text>

          <div style="margin-bottom: 12px; font-size: 14px; color: #888">
            没有账号？
            <router-link to="/register" class="router-link">
              立即注册
            </router-link>
            ，快速开启商户助手服务。
          </div>
          <ele-segmented
            v-model="tabActive"
            :items="[
              { label: '账密登录', value: 'passwordLogin' },
              { label: '验证码登录', value: 'verificationCodeLogin' }
            ]"
            style="margin-bottom: 18px"
            @change="handleTabChange"
          />
          <el-form
            v-if="tabActive === 'passwordLogin'"
            ref="formRef"
            size="large"
            :model="form"
            :rules="rules"
            @keyup.enter="submit"
            @submit.prevent=""
          >
            <el-form-item prop="username">
              <el-input
                clearable
                v-model="form.username"
                placeholder="手机号码 / 邮箱号码"
                :prefix-icon="UserOutlined"
              />
            </el-form-item>
            <el-form-item prop="password">
              <el-input
                show-password
                v-model="form.password"
                placeholder="登录密码"
                :prefix-icon="LockOutlined"
              />
            </el-form-item>

            <el-form-item prop="captcha">
              <GeetestCaptcha
                ref="captchaRef"
                :captcha-id="captchaId"
                product="popup"
                @success="handleCaptchaSuccess"
              />
            </el-form-item>
            <el-form-item>
              <div class="form-item-extra">
                <el-checkbox v-model="form.remember"> 记住密码 </el-checkbox>
                <router-link to="/forget" class="router-link">
                  忘记密码？
                </router-link>
              </div>
            </el-form-item>
            <el-form-item>
              <el-button
                size="large"
                type="primary"
                :loading="loading"
                style="width: 100%"
                @click="submit"
              >
                立即登录
              </el-button>
            </el-form-item>
          </el-form>
        </div>
      </ele-card>
    </div>
    <PageFooter style="padding-top: 0" />
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive, computed } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { EleMessage } from 'ele-admin-plus';
  import { UserOutlined, LockOutlined } from '@/components/icons';
  import PageFooter from '@/layout/components/page-footer.vue';
  import { useLogin } from '@/utils/use-login';
  import GeetestCaptcha from '@/components/GeetestCaptcha/index.vue';
  import type { GeetestResult } from '@/components/GeetestCaptcha/index.vue';

  const PROJECT_NAME = import.meta.env.VITE_APP_NAME;

  const { login, checkLogin } = useLogin();

  // 极验验证码ID（从环境变量或配置文件获取）
  const captchaId =
    import.meta.env.VITE_GEETEST_CAPTCHA_ID ||
    '54088bb07d2df3c46b79f80300b0abbe';

  // 极验组件引用
  const captchaRef = ref<InstanceType<typeof GeetestCaptcha> | null>(null);

  /** 页签选中 */
  const tabActive = ref<'passwordLogin' | 'verificationCodeLogin'>(
    'passwordLogin'
  );

  /** 表单 */
  const formRef = ref<FormInstance | null>(null);

  /** 加载状态 */
  const loading = ref(false);

  /** 表单数据 */
  const form = reactive({
    username: '',
    password: '',
    remember: true,
    lot_number: '',
    captcha_output: '',
    pass_token: '',
    gen_time: ''
  });

  /** 表单验证规则 */
  const rules = computed<FormRules>(() => {
    return {
      username: [
        {
          required: true,
          message: '请输入用户名',
          type: 'string',
          trigger: 'blur'
        }
      ],
      password: [
        {
          required: true,
          message: '请输入登录密码',
          type: 'string',
          trigger: 'blur'
        }
      ],
      captcha: [
        {
          validator: (_rule: any, _value: any, callback: any) => {
            if (!form.lot_number) {
              callback(new Error('请完成验证码验证'));
            } else {
              callback();
            }
          },
          trigger: 'change'
        }
      ]
    };
  });

  /** 极验验证成功回调 */
  const handleCaptchaSuccess = (result: GeetestResult) => {
    form.lot_number = result.lot_number;
    form.captcha_output = result.captcha_output;
    form.pass_token = result.pass_token;
    form.gen_time = result.gen_time;

    // 手动触发表单验证
    formRef.value?.validateField('captcha');
  };

  /** 提交 */
  const submit = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;

      const loginType = tabActive.value;
      login(loginType, form).catch((exception: Error) => {
        loading.value = false;
        EleMessage.error({ message: exception.message, plain: true });

        captchaRef.value?.reset();
        form.lot_number = '';
        form.captcha_output = '';
        form.pass_token = '';
        form.gen_time = '';
      });
    });
  };

  /** 选项卡切换事件 */
  const handleTabChange = (_active: number) => {};

  /** 如果已登录直接进入首页 */
  checkLogin().catch(() => {});
</script>

<style lang="scss" scoped>
  .login-wrapper {
    min-height: 100vh;
    min-height: 100dvh;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    background-image: url('@/assets/login-bg.png');
    background-repeat: no-repeat;
    background-size: 100% 100%;

    .login-main {
      flex: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      padding: 20px;
    }

    .login-card {
      width: 920px;
      max-width: 100%;
      overflow: hidden;

      :deep(.ele-card-body) {
        display: flex;
        padding: 0;
        height: 480px;
      }
    }
  }

  .login-cover {
    flex: 1;
    padding: 32px 8px;
    box-sizing: border-box;
    background-color: #1681fd;
    background-image: url('@/assets/login-img.png');
    background-repeat: no-repeat;
    background-position: bottom;
    background-size: contain;
    text-align: center;
  }

  .login-body {
    width: 400px;
    flex-shrink: 0;
    padding: 32px 48px 0 48px;
    box-sizing: border-box;

    :deep(.el-checkbox) {
      height: auto;

      .el-checkbox__label {
        color: inherit;
      }
    }

    :deep(.el-input__prefix-inner > .el-icon) {
      margin-right: 12px;
      transform: scale(1.16);
    }
  }

  /* 标题 */
  .login-title {
    color: rgba(255, 255, 255, 0.98);
    font-size: 28px;
    margin: 0 0 6px 0;
    font-weight: normal;
    letter-spacing: 1.2px;
    font-family:
      -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue',
      Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
      'Segoe UI Symbol', 'Noto Color Emoji';
  }

  .login-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    margin: 0;
    font-weight: normal;
    letter-spacing: 4px;
  }

  /* 小屏幕适应 */
  @media screen and (max-width: 680px) {
    .login-wrapper {
      background: #fff;

      .login-main {
        padding: 0;
        display: block;
      }

      .login-card {
        width: 100%;
        background: none;
        box-shadow: none;
        border-radius: 0;

        :deep(.ele-card-body) {
          display: block;
          height: auto;
        }
      }
    }

    .login-cover {
      padding: 20px 12px 100px 12px;
      background-size: auto 100px;
    }

    .login-body {
      width: 100%;
    }
  }

  .router-link {
    color: #1677ff;
    text-decoration: none;
  }

  .form-item-extra {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  :deep(.geetest-captcha-wrapper) {
    height: 40px;

    .geetest-captcha-container {
      height: 40px;
    }
  }
</style>
