import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { Group, SearchParam } from './model';
import { Permission } from '../permission/model';

/**
 * 获取用户组列表
 * @param params 查询参数
 * @returns Promise<Group[]>
 */
export async function getGroupList(params: SearchParam) {
  const response = await request.get<ApiResult<Group[]>>(
    '/merchant/group/getGroupList',
    { params }
  );
  if (response.data.code === 200 && response.data.data) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加用户组
 * @param data 用户组数据
 * @returns Promise<string>
 */
export async function addGroup(data: Group) {
  const response = await request.post<ApiResult<unknown>>(
    '/merchant/group/saveGroup',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新用户组
 * @param data 用户组数据
 * @returns Promise<string>
 */
export async function updateGroup(data: Group) {
  const response = await request.put<ApiResult<unknown>>(
    '/merchant/group/saveGroup',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除用户组
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteGroup(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>(
    '/merchant/group/deleteGroup',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 获取用户组拥有权限
 * @param groupId 用户组ID
 * @returns Promise<string>
 */
export async function getPermissionsByGroupId(groupId?: number) {
  const res = await request.get<ApiResult<Permission[]>>(
    '/merchant/group/getPermissionsByGroupId',
    { params: { group_id: groupId } }
  );
  if (res.data.code === 200) {
    return res.data.data;
  }

  return Promise.reject(new Error(res.data.message));
}
