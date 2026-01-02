import request from '@/utils/request';
import { Application, SearchParam } from './model';
import type { ApiResult } from '@/api';

/**
 * 获取应用列表
 * @param params 查询参数
 * @returns Promise<ApiResult<Application[]>>
 */
export async function getApplicationList(params: SearchParam) {
    const response = await request.get<ApiResult<Application[]>>(
        '/application/getApplicationList',
        { params }
    );
    if (response.data.code === 200 && response.data.data) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 创建应用
 * @param data 应用信息
 * @returns Promise<string>
 */
export async function createApplication(data: Application) {
    const response = await request.post<ApiResult<string>>('/application/createApplication', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}