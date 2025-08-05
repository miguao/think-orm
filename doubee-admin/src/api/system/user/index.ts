import request from '@/utils/request';
import type { ApiResult, PageResult } from '@/api';
import type { SearchParam, User } from './model';

/**
 * 获取用户列表
 * @param params 查询参数
 * @returns Promise<User[]>
 */
export async function getUserList(params: SearchParam) {
  const response = await request.get<ApiResult<PageResult<User>>>('/user/getUserList', { params });
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加用户
 * @param data 用户数据
 * @returns Promise<string>
 */
export async function addUser(data: User) {
  const response = await request.post<ApiResult<unknown>>('/user/saveUser', data);
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新用户
 * @param data 用户数据
 * @returns Promise<string>
 */
export async function updateUser(data: User) {
  const response = await request.put<ApiResult<unknown>>('/user/saveUser', data);
  if (response.data.code === 200) {
    return response.data.message;
  }
  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除用户
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteUser(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>('/user/deleteUser', { data: { list } });
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 导入用户
 */
export async function importUsers(file: File) {
  const formData = new FormData();
  formData.append('file', file);
  const res = await request.post<ApiResult<unknown>>(
    '/system/user/import',
    formData
  );
  if (res.data.code === 0) {
    return res.data.message;
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 检查用户是否存在
 */
export async function checkExistence(
  field: string,
  value: string,
  id?: number
) {
  const res = await request.get<ApiResult<unknown>>('/system/user/existence', {
    params: { field, value, id }
  });
  if (res.data.code === 0) {
    return res.data.message;
  }
  return Promise.reject(new Error(res.data.message));
}
