/**
 * 注册表单参数
 */
export interface RegisterParam {
  email?: string;
  phone?: string;
  password?: string;
  confirm_password?: string;
  verification_code?: string;
}

/**
 * 注册响应结果
 */
export interface RegisterResult {
  token?: string;
}
