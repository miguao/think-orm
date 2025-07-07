/**
 * 找回密码表单参数
 */
export interface ForgetParam {
  username?: string;
  password?: string;
  confirm_password?: string;
  verification_code?: string;
}
