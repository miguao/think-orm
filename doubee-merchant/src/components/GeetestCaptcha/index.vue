<template>
  <div class="geetest-captcha-wrapper">
    <!-- Loading 效果 -->
    <div v-if="isLoading" class="geetest-loading">
      <div class="loading-spinner"></div>
      <span class="loading-text">正在加载验证码...</span>
    </div>
    <!-- 验证码容器 -->
    <div
      :id="containerId"
      class="geetest-captcha-container"
      :class="{ 'is-hidden': isLoading }"
    ></div>
  </div>
</template>

<script lang="ts" setup>
  import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
  import { EleMessage } from 'ele-admin-plus';

  // 声明极验全局变量
  declare global {
    interface Window {
      initGeetest4: any;
    }
  }

  export interface GeetestResult {
    lot_number: string;
    captcha_output: string;
    pass_token: string;
    gen_time: string;
  }

  export interface GeetestCaptchaProps {
    /** 极验验证码ID */
    captchaId: string;
    /** 产品形式：bind（嵌入式）、float（浮动式）、popup（弹出式） */
    product?: 'bind' | 'float' | 'popup';
    /** 语言：zho（中文）、eng（英文）、jpn（日语）等 */
    language?: string;
    /** 验证类型：slide（滑动）、icon（点选）、phrase（短语） */
    riskType?: 'slide' | 'icon' | 'phrase';
    /** 是否需要重置 */
    reset?: boolean;
  }

  const props = withDefaults(defineProps<GeetestCaptchaProps>(), {
    product: 'float',
    language: 'zho',
    riskType: 'slide',
    reset: false
  });

  const emit = defineEmits<{
    /** 验证成功事件 */
    success: [result: GeetestResult];
    /** 验证失败事件 */
    error: [error: any];
    /** 验证准备就绪事件 */
    ready: [];
  }>();

  // 生成唯一的容器ID
  const containerId = ref(
    `geetest-captcha-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  );

  // 加载状态
  const isLoading = ref(false);

  // 极验验证码实例
  let captchaObj: any = null;
  // SDK 加载 Promise，用于避免重复加载
  let scriptLoadPromise: Promise<void> | null = null;

  /** 动态加载极验 SDK */
  const loadGeetestScript = (): Promise<void> => {
    // 如果已经加载完成，直接返回
    if (window.initGeetest4) {
      return Promise.resolve();
    }

    // 如果正在加载中，返回之前的 Promise
    if (scriptLoadPromise) {
      return scriptLoadPromise;
    }

    scriptLoadPromise = new Promise((resolve, reject) => {
      // 检查是否已经存在脚本标签
      const existingScript = document.querySelector(
        'script[src="https://static.geetest.com/v4/gt4.js"]'
      );

      if (existingScript) {
        // 如果脚本已存在，等待加载完成
        if (window.initGeetest4) {
          resolve();
          return;
        }
        existingScript.addEventListener('load', () => {
          resolve();
        });
        existingScript.addEventListener('error', () => {
          reject(new Error('极验 SDK 加载失败'));
        });
        return;
      }

      // 创建新的脚本标签
      const script = document.createElement('script');
      script.src = 'https://static.geetest.com/v4/gt4.js';
      script.async = true;

      script.onload = () => {
        resolve();
      };

      script.onerror = () => {
        scriptLoadPromise = null;
        reject(new Error('极验 SDK 加载失败'));
      };

      document.head.appendChild(script);
    });

    return scriptLoadPromise;
  };

  /** 初始化极验验证码 */
  const initCaptcha = async () => {
    if (!props.captchaId) {
      console.error('极验验证码：captchaId 不能为空');
      return;
    }

    try {
      // 开始加载，显示 loading
      isLoading.value = true;

      // 先加载 SDK
      await loadGeetestScript();

      // 确认 SDK 已加载
      if (!window.initGeetest4) {
        throw new Error('极验验证码SDK加载失败');
      }

      // 初始化验证码
      window.initGeetest4(
        {
          captchaId: props.captchaId,
          product: props.product,
          language: props.language,
          riskType: props.riskType
        },
        (captcha: any) => {
          captchaObj = captcha;

          // 验证成功的回调
          captcha.onSuccess(() => {
            const result = captcha.getValidate();
            emit('success', {
              lot_number: result.lot_number,
              captcha_output: result.captcha_output,
              pass_token: result.pass_token,
              gen_time: result.gen_time
            });
          });

          // 验证失败的回调
          captcha.onError((error: any) => {
            console.error('极验验证码错误:', error);
            EleMessage.error({
              message: '验证码加载失败，请刷新重试',
              plain: true
            });
            emit('error', error);
          });

          // 验证准备就绪，隐藏 loading
          captcha.onReady(() => {
            isLoading.value = false;
            emit('ready');
          });

          // 将验证码绑定到指定元素
          captcha.appendTo(`#${containerId.value}`);
        }
      );
    } catch (error) {
      console.error('极验验证码初始化失败:', error);
      isLoading.value = false;
      EleMessage.error({
        message: '验证码SDK加载失败，请检查网络连接',
        plain: true
      });
      emit('error', error);
    }
  };

  /** 重置验证码 */
  const resetCaptcha = () => {
    if (captchaObj) {
      try {
        captchaObj.reset();
      } catch (error) {
        console.error('重置验证码失败:', error);
      }
    }
  };

  /** 销毁验证码 */
  const destroyCaptcha = () => {
    if (captchaObj) {
      try {
        captchaObj.destroy();
        captchaObj = null;
      } catch (error) {
        console.error('销毁验证码失败:', error);
      }
    }
  };

  /** 获取验证结果 */
  const getValidate = (): GeetestResult | null => {
    if (captchaObj) {
      try {
        const result = captchaObj.getValidate();
        return {
          lot_number: result.lot_number,
          captcha_output: result.captcha_output,
          pass_token: result.pass_token,
          gen_time: result.gen_time
        };
      } catch (error) {
        console.error('获取验证结果失败:', error);
        return null;
      }
    }
    return null;
  };

  // 监听 reset 属性变化
  watch(
    () => props.reset,
    (newVal) => {
      if (newVal) {
        resetCaptcha();
      }
    }
  );

  // 组件挂载时初始化
  onMounted(() => {
    // 延迟初始化，确保DOM已渲染
    setTimeout(() => {
      initCaptcha();
    }, 100);
  });

  // 组件卸载前销毁
  onBeforeUnmount(() => {
    destroyCaptcha();
  });

  // 暴露方法给父组件
  defineExpose({
    reset: resetCaptcha,
    destroy: destroyCaptcha,
    getValidate
  });
</script>

<style lang="scss" scoped>
  .geetest-captcha-wrapper {
    width: 100%;
    position: relative;
    min-height: 40px;

    .geetest-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 0;
      background: #f5f7fa;
      border: 1px solid #e4e7ed;
      border-radius: 4px;
      transition: all 0.3s ease;

      .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #409eff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
      }

      .loading-text {
        font-size: 14px;
        color: #606266;
      }
    }

    .geetest-captcha-container {
      :deep(.geetest_holder) {
        width: 100% !important;
        height: 40px !important;
      }
    }
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }
</style>
