import request from '@/utils/request';
import { Institution, SearchParam } from './model';
import { ApiResult } from '@/api';

/**
 * 获取机构列表
 * @param params 查询参数
 * @returns Promise<Institution[]>
 */
export async function getInstitutionList(params: SearchParam) {
    const response = await request.get<ApiResult<Institution[]>>(
        '/institution/getInstitutionList',
        { params }
    );
    if (response.data.code === 200 && response.data.data) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 添加机构
 * @param data 机构数据
 * @returns Promise<string>
 */
export async function addInstitution(data: Institution) {
    const response = await request.post<ApiResult<unknown>>(
        '/institution/saveInstitution',
        data
    );
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 更新机构
 * @param data 机构数据
 * @returns Promise<string>
 */
export async function updateInstitution(data: Institution) {
    const response = await request.put<ApiResult<unknown>>(
        '/institution/saveInstitution',
        data
    );
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 删除机构
 * @param id 机构ID
 * @returns Promise<string>
 */
export async function deleteInstitution(id?: number) {
    const response = await request.delete<ApiResult<unknown>>(
        'institution/deleteInstitution',
        { data: { list: [id] } }
    );
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}


