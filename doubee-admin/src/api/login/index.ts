import request from '@/utils/request';
import { setToken } from '@/utils/token-util';
import type { ApiResult } from '@/api';
import type { LoginParam, LoginResult } from './model';

/**
 * 用户登录
 * @param data 登录参数
 * @returns 登录结果
 */
export async function login(data: LoginParam) {
  const res = await request.post<ApiResult<LoginResult>>('/auth/login', data);
  if (res.data.code === 200) {
    setToken('Bearer ' + res.data.data?.token, data.remember);
    return res.data.message;
  }
  return Promise.reject(new Error(res.data.message));
}
