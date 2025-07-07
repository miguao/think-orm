import request from '@/utils/request';
import { RegisterParam, RegisterResult } from './model';
import type { ApiResult } from '@/api';
import { setToken } from '@/utils/token-util';

/**
 * 邮箱注册
 * @param data 注册表单
 * @returns
 */
export async function emailRegister(data: RegisterParam) {
  const response = await request.post<ApiResult<RegisterResult>>(
    '/merchant/api/auth/emailRegister',
    data
  );
  if (response.data.code === 200) {
    setToken('Bearer ' + response.data.data?.token, true);
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 手机号注册
 * @param data 注册表单
 * @returns
 */
export async function phoneRegister(data: RegisterParam) {
  const response = await request.post<ApiResult<RegisterResult>>(
    '/merchant/api/auth/phoneRegister',
    data
  );
  if (response.data.code === 200) {
    setToken('Bearer ' + response.data.data?.token, true);
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 发送注册验证码
 * @param username 用户名
 * @returns
 */
export async function sendRegisterCode(username: string) {
  const response = await request.post<ApiResult<RegisterResult>>(
    '/merchant/api/auth/sendRegisterCode',
    { username: username }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
