import request from '@/utils/request';
import { setToken } from '@/utils/token-util';
import type { ApiResult } from '@/api';
import type { LoginParam, LoginResult } from './model';

/**
 * 账密登录
 * @param data 登录表单
 * @returns
 */
export async function passwordLogin(data: LoginParam) {
  const response = await request.post<ApiResult<LoginResult>>(
    '/merchant/api/auth/passwordLogin',
    data
  );
  if (response.data.code === 200) {
    setToken('Bearer ' + response.data.data?.token, true);
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 验证码登录
 * @param data 登录表单
 * @returns
 */
export async function verificationCodeLogin(data: LoginParam) {
  const response = await request.post<ApiResult<LoginResult>>(
    '/merchant/api/auth/verificationCodeLogin',
    data
  );
  if (response.data.code === 200) {
    setToken('Bearer ' + response.data.data?.token, true);
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 发送登录验证码
 * @param username 用户名
 * @returns
 */
export async function sendLoginVerificationCode(username: string) {
  const response = await request.post<ApiResult<LoginResult>>(
    '/merchant/api/auth/sendLoginVerificationCode',
    { username: username }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
