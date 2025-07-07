import request from '@/utils/request';
import type { ApiResult } from '@/api';
import { ForgetParam } from './model';

/**
 * 找回密码
 * @param data 找回密码表单数据
 * @returns
 */
export async function resetPassword(data: ForgetParam) {
  const response = await request.post<ApiResult<ForgetParam>>(
    '/merchant/api/auth/resetPassword',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 发送找回验证码
 * @param username 用户名
 * @returns
 */
export async function sendResetPasswordCode(username: string) {
  const response = await request.post<ApiResult<ForgetParam>>(
    '/merchant/api/auth/sendResetPasswordCode',
    { username: username }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
