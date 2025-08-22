import request from "@/utils/request";
import { Application, SearchParam } from "./model";
import { ApiResult } from "@/api";

/**
 * 获取应用列表
 * @param params 查询参数
 * @returns Promise<Application[]>
 */
export async function getApplicationList(params: SearchParam) {
    const response = await request.get<ApiResult<Application[]>>('/merchant/app/getAppList', { params });
    if (response.data.code === 200 && response.data.data) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 添加应用
 * @param data 应用数据
 * @returns Promise<string>
 */
export async function addApplication(data: Application) {
    const response = await request.post<ApiResult<unknown>>('/merchant/app/saveApp', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 更新应用
 * @param data 应用数据
 * @returns Promise<string>
 */
export async function updateApplication(data: Application) {
    const response = await request.put<ApiResult<unknown>>('/merchant/app/saveApp', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 删除应用
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteApplication(list: number[]) {
    const response = await request.delete<ApiResult<unknown>>('/merchant/app/deleteApp', { data: { list } });
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}