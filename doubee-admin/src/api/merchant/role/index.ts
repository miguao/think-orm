import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { Role, SearchParam } from './model';

/**
 * 获取角色列表
 * @param params 查询参数
 * @returns Promise<Role[]>
 */
export async function getRoleList(params: SearchParam) {
    const response = await request.get<ApiResult<Role[]>>('/merchant/role/getRoleList', { params });
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
    const response = await request.post<ApiResult<unknown>>('/merchant/role/saveRole', data);
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
    const response = await request.put<ApiResult<unknown>>('/merchant/role/saveRole', data);
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
    const response = await request.delete<ApiResult<unknown>>('/merchant/role/deleteRole', { data: { list } });
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}