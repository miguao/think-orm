<template>
  <div class="login-wrapper">
    <div class="login-main">
      <ele-card shadow="always" class="login-card">
        <div class="login-cover">
          <h1 class="login-title">{{ APP_NAME }}</h1>
          <h4 class="login-subtitle">畅享高效、便捷的支付服务，从登录开始</h4>
        </div>

        <div class="login-body">
          <ele-text type="heading" style="font-size: 24px">
            轻松收款，从登录开始
          </ele-text>

          <div style="margin-bottom: 10px; font-size: 14px; color: #888"
            >没有账号？<router-link to="/register" class="router-link"
              >立即注册</router-link
            >，快速开启商户助手服务。
          </div>

          <ele-segmented
            v-model="tabActive"
            :items="[
              { label: '账密登录', value: 1 },
              { label: '验证码登录', value: 2 }
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
            <el-form-item prop="username">
              <el-input
                clearable
                v-model="form.username"
                placeholder="请输入手机号或邮箱账号"
                :prefix-icon="UserOutlined"
              />
            </el-form-item>

            <el-form-item prop="password" v-if="tabActive == 1">
              <el-input
                show-password
                v-model="form.password"
                placeholder="请输入密码"
                :prefix-icon="LockOutlined"
              />
            </el-form-item>

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
                    :disabled="
                      codeCountdown > 0 || !isValidUsername(form.username)
                    "
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
                <el-checkbox v-model="form.remember"> 记住密码 </el-checkbox>
                <router-link to="/forget" class="router-link"
                  >忘记密码？</router-link
                >
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
  import { ref, reactive, unref } from 'vue';
  import type { FormInstance, FormRules } from 'element-plus';
  import { UserOutlined, LockOutlined } from '@/components/icons';
  import PageFooter from '@/layout/components/page-footer.vue';
  import {
    passwordLogin,
    verificationCodeLogin,
    sendLoginVerificationCode
  } from '@/api/login';
  import { usePageTab } from '@/utils/use-page-tab';
  import { useRouter } from 'vue-router';
  import { EleMessage } from 'ele-admin-plus/es';

  /**
   * 应用名称
   */
  const APP_NAME = import.meta.env.VITE_APP_NAME;

  /**
   * 页签选中(默认：账密登录)
   */
  const tabActive = ref(1);

  /**
   * 表单数据
   */
  const form = reactive({
    username: '',
    password: '',
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
    username: [
      {
        required: true,
        message: '请输入手机号或者邮箱账号',
        type: 'string',
        trigger: 'blur'
      },
      {
        validator: (_rule, value, callback) => {
          if (!value) {
            callback(new Error('请输入手机号或者邮箱账号'));
          } else if (!isValidUsername(value)) {
            callback(new Error('请输入正确的手机号或邮箱格式'));
          } else {
            callback();
          }
        },
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

    sendLoginVerificationCode(form.username)
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
   * 统一登录
   */
  const onSubmit = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;

      /**
       * 账密登录
       */
      if (tabActive.value === 1) {
        passwordLogin(form)
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
       * 验证码登录
       */
      if (tabActive.value === 2) {
        verificationCodeLogin(form)
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
   * 判断是否为手机/邮箱号码
   * @param value 用户名
   */
  function isValidUsername(value: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^1[3-9]\d{9}$/;
    return emailRegex.test(value) || phoneRegex.test(value);
  }
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
  html.dark .login-wrapper {
    background: #000;
  }
</style>
