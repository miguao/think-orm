<template>
  <div class="forget-wrapper">
    <div class="forget-main">
      <ele-card shadow="always" class="forget-card">
        <div class="forget-cover">
          <h1 class="forget-title">{{ APP_NAME }}</h1>
          <h4 class="forget-subtitle">安全快捷，助您轻松找回密码。</h4>
        </div>

        <div class="forget-body">
          <ele-text type="heading" style="font-size: 24px">
            新密码，新开始
          </ele-text>

          <div style="margin-bottom: 10px; font-size: 14px; color: #888"
            >密码想起来了？<router-link to="/login" class="router-link"
              >赶紧登录吧，Let's Go</router-link
            >
          </div>

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

            <el-form-item prop="verification_code">
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
              <el-button
                size="large"
                type="primary"
                :loading="loading"
                style="width: 100%"
                @click="onSubmit"
              >
                重置密码
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
  import { usePageTab } from '@/utils/use-page-tab';
  import { useRouter } from 'vue-router';
  import { EleMessage } from 'ele-admin-plus/es';
  import { resetPassword, sendResetPasswordCode } from '@/api/forget';

  /**
   * 应用名称
   */
  const APP_NAME = import.meta.env.VITE_APP_NAME;

  /**
   * 表单数据
   */
  const form = reactive({
    username: '',
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

    sendResetPasswordCode(form.username)
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
  const { goHomeRoute } = usePageTab();

  /**
   * 统一找回
   */
  const onSubmit = () => {
    formRef.value?.validate?.((valid) => {
      if (!valid) {
        return;
      }

      loading.value = true;

      resetPassword(form)
        .then((message) => {
          EleMessage.success(message);
          goHome();
        })
        .catch((exception) => {
          loading.value = false;
          EleMessage.error(exception.message);
        });
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
  .forget-wrapper {
    min-height: 100vh;
    min-height: 100dvh;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    background-image: url('@/assets/login-bg.png');
    background-repeat: no-repeat;
    background-size: 100% 100%;

    .forget-main {
      flex: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      padding: 20px;
    }

    .forget-card {
      width: 920px;
      max-width: 100%;
      overflow: hidden;

      :deep(.ele-card-body) {
        display: flex;
        padding: 0;
      }
    }
  }

  .forget-cover {
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

  .forget-body {
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

  .forget-title {
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

  .forget-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    margin: 0;
    font-weight: normal;
    letter-spacing: 4px;
  }

  /* 小屏幕适应 */
  @media screen and (max-width: 680px) {
    .forget-wrapper {
      background: #fff;

      .forget-main {
        padding: 0;
        display: block;
      }

      .forget-card {
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

    .forget-cover {
      padding: 20px 12px 100px 12px;
      background-size: auto 100px;
    }

    .forget-body {
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
  html.dark .forget-wrapper {
    background: #000;
  }
</style>
