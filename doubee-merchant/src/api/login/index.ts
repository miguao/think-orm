import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { LoginParam, LoginResult, CaptchaResult } from './model';

/**
 * 账密登录
 * @param data 账密登录参数
 * @returns Promise<ApiResult<LoginResult>>
 */
export async function passwordLogin(data: LoginParam) {
  const response = await request.post<ApiResult<LoginResult>>(
    '/auth/passwordLogin',
    data
  );
  if (response.data.code === 200) {
    return response.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 登录
 */
export async function login(data: LoginParam) {
  const res = await request.post<ApiResult<LoginResult>>('/login', data);
  if (res.data.code === 0) {
    return res.data;
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 获取验证码
 */
export async function getCaptcha() {
  const res = await request.get<ApiResult<CaptchaResult>>('/captcha');
  if (res.data.code === 0 && res.data.data) {
    return res.data.data;
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 退出登录
 */
export async function logout() {
  const response = await request.post<ApiResult<unknown>>('/personal/account/logout');
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
