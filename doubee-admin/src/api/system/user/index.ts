import request from '@/utils/request';
import type { ApiResult, PageResult } from '@/api';
import type { SearchParam, User } from './model';

/**
 * 获取用户列表
 * @param params 查询参数
 * @returns Promise<User[]>
 */
export async function getUserList(params: SearchParam) {
  const response = await request.get<ApiResult<PageResult<User>>>(
    '/user/getUserList',
    { params }
  );
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
  const response = await request.post<ApiResult<unknown>>(
    '/user/saveUser',
    data
  );
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
  const response = await request.put<ApiResult<unknown>>(
    '/user/saveUser',
    data
  );
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
  const response = await request.delete<ApiResult<unknown>>(
    '/user/deleteUser',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 导入用户
 * @param file 导入文件
 * @returns Promise<string>
 */
export async function importUser(file: File) {
  const formData = new FormData();
  formData.append('file', file);

  const response = await request.post<ApiResult<unknown>>(
    '/user/importUser',
    formData
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
