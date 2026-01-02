import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { UpdatePasswordParam, Merchant } from './model';

/**
 * 获取当前登录用户的个人信息/菜单/权限/角色
 * @param toRoute 路由守卫中要进入的路由
 * @returns Promise<Merchant> 
 */
export async function getUserInfo(toRoute: any): Promise<Merchant> {
  const response = await request.get<ApiResult<Merchant>>('/personal/account/getMeInfo', {
    toRoute
  } as any);
  if (response.data.code === 200 && response.data.data) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 修改当前登录用户的密码
 */
export async function updatePassword(
  data: UpdatePasswordParam
): Promise<string> {
  const res = await request.put<ApiResult<unknown>>('/auth/password', data);
  if (res.data.code === 0) {
    return res.data.message ?? '修改成功';
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 修改当前登录用户的个人信息
 */
export async function updateUserInfo(data: Merchant): Promise<Merchant> {
  const res = await request.put<ApiResult<Merchant>>('/auth/user', data);
  if (res.data.code === 0 && res.data.data) {
    return res.data.data;
  }
  return Promise.reject(new Error(res.data.message));
}
