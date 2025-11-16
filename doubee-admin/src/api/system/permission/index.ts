import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { Permission, SearchParam } from './model';

/**
 * 获取权限列表
 * @param params 查询参数
 * @returns Promise<Permission[]>
 */
export async function getPermissionList(params: SearchParam) {
  const response = await request.get<ApiResult<Permission[]>>(
    '/user/permission/getPermissionList',
    { params }
  );
  if (response.data.code === 200 && response.data.data) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加权限
 * @param data 权限数据
 * @returns Promise<string>
 */
export async function addPermission(data: Permission) {
  const response = await request.post<ApiResult<Permission[]>>(
    '/user/permission/savePermission',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新权限
 * @param data 权限数据
 * @returns Promise<string>
 */
export async function updatePermission(data: Permission) {
  const response = await request.put<ApiResult<Permission[]>>(
    '/user/permission/savePermission',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除权限
 * @param id 权限ID
 * @returns Promise<string>
 */
export async function deletePermission(id?: number) {
  const response = await request.delete<ApiResult<unknown>>(
    '/user/permission/deletePermission',
    { data: { list: id } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
