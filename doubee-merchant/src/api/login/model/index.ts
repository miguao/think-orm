import type { User } from '../../system/user/model';

/**
 * 登录参数
 */
export interface LoginParam {
  /** 账号 */
  username?: string;
  /** 密码 */
  password?: string;
  /** 租户id */
  tenantId?: number;
  /** 是否记住密码 */
  remember?: boolean;
  /** 极验验证码 lot_number */
  lot_number?: string;
  /** 极验验证码 captcha_output */
  captcha_output?: string;
  /** 极验验证码 pass_token */
  pass_token?: string;
  /** 极验验证码 gen_time */
  gen_time?: string;
}

/**
 * 登录返回结果
 */
export interface LoginResult {
  /** token */
  token?: string;
  /** 用户信息 */
  user?: User;
}

/**
 * 图形验证码返回结果
 */
export interface CaptchaResult {
  /** 图形验证码base64数据 */
  base64: string;
  /** 验证码文本 */
  text: string;
}
