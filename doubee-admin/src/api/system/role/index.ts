import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { Role, SearchParam } from './model';
import type { Permission } from '../permission/model';

/**
 * 获取角色列表
 * @param params 查询参数
 * @returns Promise<Role[]>
 */
export async function getRoleList(params: SearchParam) {
  const response = await request.get<ApiResult<Role[]>>('/user/role/getRoleList', { params });
  if (response.data.code === 200 && response.data.data) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加角色
 * @param data 角色数据
 * @returns Promise<string>
 */
export async function addRole(data: Role) {
  const response = await request.post<ApiResult<unknown>>('/user/role/saveRole', data);
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新角色
 * @param data 角色数据
 * @returns Promise<string>
 */
export async function updateRole(data: Role) {
  const response = await request.put<ApiResult<unknown>>('/user/role/saveRole', data);
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除角色
 * @param list 删除列表 
 * @returns Promise<string>
 */
export async function deleteRole(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>('/user/role/deleteRole', { data: { list } });
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 查询角色列表
 */
export async function listRoles(params?: SearchParam) {
  const res = await request.get<ApiResult<Role[]>>('/system/role', {
    params
  });
  if (res.data.code === 0 && res.data.data) {
    return res.data.data;
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 批量删除角色
 */
export async function removeRoles(data: (number | undefined)[]) {
  const res = await request.delete<ApiResult<unknown>>('/system/role/batch', {
    data
  });
  if (res.data.code === 0) {
    return res.data.message;
  }
  return Promise.reject(new Error(res.data.message));
}

/**
 * 获取角色拥有权限
 * @param roleId 角色ID
 * @returns Promise<string>
 */
export async function getPermissionsByRoleId(roleId?: number) {
  const res = await request.get<ApiResult<Permission[]>>(
    '/user/role/getPermissionsByRoleId', { params: { role_id: roleId } }
  );
  if (res.data.code === 200) {
    return res.data.data;
  }

  return Promise.reject(new Error(res.data.message));
}

/**
 * 修改角色菜单
 */
export async function updateRoleMenus(roleId?: number, data?: number[]) {
  const res = await request.put<ApiResult<unknown>>(
    '/system/role-menu/' + roleId,
    data
  );
  if (res.data.code === 0) {
    return res.data.message;
  }
  return Promise.reject(new Error(res.data.message));
}
