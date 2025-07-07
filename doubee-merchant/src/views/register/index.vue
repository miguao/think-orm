<template>
  <div class="register-wrapper">
    <div class="register-main">
      <ele-card shadow="always" class="register-card">
        <div class="register-cover">
          <h1 class="register-title">{{ APP_NAME }}</h1>
          <h4 class="register-subtitle">高效收款，智慧经营，从注册开始。</h4>
        </div>

        <div class="register-body">
          <ele-text type="heading" style="font-size: 24px">
            注册即刻，收款无忧
          </ele-text>

          <div style="margin-bottom: 10px; font-size: 14px; color: #888"
            >已有账号？<router-link to="/login" class="router-link"
              >点击登录</router-link
            >，开始使用商户助手。
          </div>

          <ele-segmented
            v-model="tabActive"
            :items="[
              { label: '邮箱注册', value: 1 },
              { label: '手机注册', value: 2 }
            ]"
            style="margin-bottom: 18px"
          />

          <el-form
            ref="formRef"
            size="large"
            :model="form"
            :rules="rules"
            @keyup.enter="onSubmit"
            @submit.prevent=""
          >
            <!-- 邮箱注册 -->
            <el-form-item prop="email" v-if="tabActive == 1">
              <el-input
                clearable
                v-model="form.email"
                placeholder="请输入邮箱号码"
                :prefix-icon="UserOutlined"
              />
            </el-form-item>

            <!-- 手机注册 -->
            <el-form-item prop="phone" v-if="tabActive == 2">
              <el-input
                clearable
                v-model="form.phone"
                placeholder="请输入手机号码"
                :prefix-icon="UserOutlined"
              />
            </el-form-item>

            <el-form-item prop="password">
              <el-input
                show-password
                v-model="form.password"
                placeholder="请输入密码"
                :prefix-icon="LockOutlined"
              />
            </el-form-item>

            <el-form-item prop="confirm_password">
              <el-input
                show-password
                v-model="form.confirm_password"
                placeholder="请再次输入密码"
                :prefix-icon="LockOutlined"
              />
            </el-form-item>

            <!-- 邮箱注册 -->
            <el-form-item prop="verification_code" v-if="tabActive == 1">
              <el-row :gutter="10" style="width: 100%">
                <el-col :span="16">
                  <el-input
                    clearable
                    v-model="form.verification_code"
                    placeholder="请输入验证码"
                    :prefix-icon="LockOutlined"
                  />
                </el-col>
                <el-col :span="8">
                  <el-button
                    size="large"
                    type="primary"
                    :disabled="codeCountdown > 0 || !isValidEmail(form.email)"
                    @click="onSendVerificationCode"
                    style="width: 100%"
                  >
                    {{
                      codeCountdown > 0
                        ? `${codeCountdown}s后重试`
                        : '获取验证码'
                    }}
                  </el-button>
                </el-col>
              </el-row>
            </el-form-item>

            <!-- 手机注册 -->
            <el-form-item prop="verification_code" v-if="tabActive == 2">
              <el-row :gutter="10" style="width: 100%">
                <el-col :span="16">
                  <el-input
                    clearable
                    v-model="form.verification_code"
                    placeholder="请输入验证码"
                    :prefix-icon="LockOutlined"
                  />
                </el-col>
                <el-col :span="8">
                  <el-button
                    size="large"
                    type="primary"
                    :disabled="codeCountdown > 0 || !isValidPhone(form.phone)"
                    @click="onSendVerificationCode"
                    style="width: 100%"
                  >
                    {{
                      codeCountdown > 0
                        ? `${codeCountdown}s后重试`
                        : '获取验证码'
                    }}
                  </el-button>
                </el-col>
              </el-row>
            </el-form-item>

            <el-form-item>
              <div class="form-item-extra">
                <el-checkbox v-model="form.remember">
                  我已阅读并同意《<b
                    class="router-link"
                    @click="onOpenUserAgreementDialog"
                    >用户协议</b
                  >》和《<b
                    class="router-link"
                    @click="onOpenPrivacyPolicyDialog"
                    >隐私政策</b
                  >》
                </el-checkbox>
              </div>
            </el-form-item>
            <el-form-item>
              <el-button
                size="large"
                type="primary"
                :loading="loading"
                style="width: 100%"
                @click="onSubmit"
              >
                立即注册
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
  import { ref, reactive, unref } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { UserOutlined, LockOutlined } from '@/components/icons';
  import PageFooter from '@/layout/components/page-footer.vue';
  import {
    emailRegister,
    phoneRegister,
    sendRegisterCode
  } from '@/api/register';
  import { usePageTab } from '@/utils/use-page-tab';
  import { useRouter } from 'vue-router';
  import { EleMessage } from 'ele-admin-plus/es';

  /**
   * 应用名称
   */
  const APP_NAME = import.meta.env.VITE_APP_NAME;

  /**
   * 页签选中(默认：邮箱注册)
   */
  const tabActive = ref(1);

  /**
   * 表单数据
   */
  const form = reactive({
    email: '',
    phone: '',
    password: '',
    confirm_password: '',
    verification_code: '',
    remember: true
  });

  /**
   * 表单实例
   */
  const formRef = ref<FormInstance | null>(null);

  /**
   * 验证规则
   */
  const rules = reactive<FormRules>({
    email: [
      { required: true, message: '请输入邮箱号码', trigger: 'blur' },
      { validator: validateEmail, trigger: 'blur' }
    ],
    phone: [
      { required: true, message: '请输入手机号码', trigger: 'blur' },
      { validator: validatePhone, trigger: 'blur' }
    ],
    password: [
      {
        required: true,
        message: '请输入登录密码',
        type: 'string',
        trigger: 'blur'
      }
    ],
    confirm_password: [
      {
        required: true,
        message: '请再次输入登录密码',
        type: 'string',
        trigger: 'blur'
      },
      {
        validator: validateConfirmPassword(() => form.password),
        trigger: 'blur'
      }
    ],
    verification_code: [
      {
        required: true,
        message: '请输入验证码',
        type: 'string',
        trigger: 'blur'
      }
    ]
  });

  /**
   * 验证码倒计时（单位：秒）
   */
  const codeCountdown = ref(0);
  let timer: any = null; // 倒计时定时器，用于清除计时任务

  /**
   * 发送验证码
   */
  const onSendVerificationCode = () => {
    const messageLoading = EleMessage.loading({
      message: '请求中..',
      plain: true
    });

    let username = '';
    /**
     * 邮箱注册
     */
    if (tabActive.value === 1) {
      username = form.email;
    }

    /**
     * 手机注册
     */
    if (tabActive.value === 2) {
      username = form.phone;
    }

    sendRegisterCode(username)
      .then((message) => {
        EleMessage.success(message);
        messageLoading.close();

        codeCountdown.value = 180; // 启动倒计时（180秒）
        clearInterval(timer);

        timer = setInterval(() => {
          codeCountdown.value--;
          if (codeCountdown.value <= 0) {
            clearInterval(timer);
          }
        }, 1000);
      })
      .catch((exception) => {
        messageLoading.close();
        EleMessage.error(exception.message);
      });
  };

  /**
   * 加载状态
   */
  const loading = ref(false);

  /**
   * 当前路由
   */
  const { currentRoute } = useRouter();
  const { goHomeRoute, cleanPageTabs } = usePageTab();

  /**
   * 统一注册
   */
  const onSubmit = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;

      /**
       * 邮箱注册
       */
      if (tabActive.value === 1) {
        emailRegister(form)
          .then((message) => {
            EleMessage.success(message);
            cleanPageTabs();
            goHome();
          })
          .catch((exception) => {
            loading.value = false;
            EleMessage.error(exception.message);
          });
      }

      /**
       * 手机注册
       */
      if (tabActive.value === 2) {
        phoneRegister(form)
          .then((message) => {
            EleMessage.success(message);
            cleanPageTabs();
            goHome();
          })
          .catch((exception) => {
            loading.value = false;
            EleMessage.error(exception.message);
          });
      }
    });
  };

  /**
   * 跳转到首页
   */
  const goHome = () => {
    const { query } = unref(currentRoute);
    goHomeRoute(query.from as string);
  };

  /**
   * 用户协议
   */
  const onOpenUserAgreementDialog = () => {
    EleMessage.info('用户协议');
  };

  /**
   * 隐私政策
   */
  const onOpenPrivacyPolicyDialog = () => {
    EleMessage.info('隐私政策');
  };

  /**
   * 验证是否为邮箱号码（助手函数）
   * @param value 数据值
   */
  function isValidEmail(value: string): boolean {
    const emailRegex = /^[\w.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(value);
  }

  /**
   * 验证是否为中国大陆手机号码（助手函数）
   * @param value 数据值
   */
  function isValidPhone(value: string): boolean {
    const phoneRegex = /^1[3-9]\d{9}$/;
    return phoneRegex.test(value);
  }

  /**
   * 验证是否为手机号码（助手函数）
   * @param _rule 验证规则
   * @param value 数据值
   * @param callback 回调函数
   */
  function validatePhone(
    _rule: any,
    value: string,
    callback: (error?: Error) => void
  ): void {
    if (!value) {
      callback(new Error('请输入手机号码'));
    } else if (!isValidPhone(value)) {
      callback(new Error('请输入正确的手机号码格式'));
    } else {
      callback();
    }
  }

  /**
   * 是否为邮箱号码（助手函数）
   * @param _rule 验证规则
   * @param value 数据值
   * @param callback 回调函数
   */
  function validateEmail(
    _rule: any,
    value: string,
    callback: (error?: Error) => void
  ): void {
    if (!value) {
      callback(new Error('请输入邮箱号码'));
    } else if (!isValidEmail(value)) {
      callback(new Error('请输入正确的邮箱格式'));
    } else {
      callback();
    }
  }

  /**
   * 验证密码是否一致（助手函数）
   * @param getPassword 返回当前密码数据值
   */
  function validateConfirmPassword(getPassword: () => string) {
    return function (
      _rule: any,
      value: string,
      callback: (error?: Error) => void
    ): void {
      if (!value) {
        callback(new Error('请再次输入登录密码'));
      } else if (value !== getPassword()) {
        callback(new Error('两次输入的密码不一致'));
      } else {
        callback();
      }
    };
  }
</script>

<style lang="scss" scoped>
  .register-wrapper {
    min-height: 100vh;
    min-height: 100dvh;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    background-image: url('@/assets/register-bg.png');
    background-repeat: no-repeat;
    background-size: 100% 100%;

    .register-main {
      flex: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      padding: 20px;
    }

    .register-card {
      width: 920px;
      max-width: 100%;
      overflow: hidden;

      :deep(.ele-card-body) {
        display: flex;
        padding: 0;
      }
    }
  }

  .register-cover {
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

  .register-body {
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

  .register-title {
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

  .register-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    margin: 0;
    font-weight: normal;
    letter-spacing: 4px;
  }

  /* 小屏幕适应 */
  @media screen and (max-width: 680px) {
    .register-wrapper {
      background: #fff;

      .register-main {
        padding: 0;
        display: block;
      }

      .register-card {
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

    .register-cover {
      padding: 20px 12px 100px 12px;
      background-size: auto 100px;
    }

    .register-body {
      width: 100%;
    }
  }
</style>

<style lang="scss" scoped>
  .form-item-extra {
    display: flex;
    justify-content: space-between;
    width: 100%;
  }

  .router-link {
    color: #1677ff;
    text-decoration: none;
  }
</style>

<style lang="scss">
  html.dark .register-wrapper {
    background: #000;
  }
</style>
