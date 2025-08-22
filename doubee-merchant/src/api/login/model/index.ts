/**
 * 登录表单参数
 */
export interface LoginParam {
  username?: string;
  password?: string;
  verification_code?: string;
  remember?: boolean;
}

/**
 * 登录响应结果
 */
export interface LoginResult {
  token?: string;
}
