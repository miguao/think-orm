import type { User } from '../../system/user/model';

/**
 * 登录参数
 */
export interface LoginParam {
  /** 账号 */
  email?: string;
  /** 密码 */
  password?: string;
  /** 是否记住密码 */
  remember?: boolean;
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
