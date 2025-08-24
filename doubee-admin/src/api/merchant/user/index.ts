import { ApiResult, PageResult } from "@/api";
import request from "@/utils/request";
import type { Merchant, SearchParam } from "./model";

/**
 * 获取商户列表
 * @param params 查询参数
 * @returns Promise<Merchant[]>
 */
export async function getMerchantList(params: SearchParam) {
    const response = await request.get<ApiResult<PageResult<Merchant>>>('/merchant/getMerchantList', { params });
    if (response.data.code === 200) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 添加商户
 * @param data 商户数据
 * @returns Promise<string>
 */
export async function addMerchant(data: Merchant) {
    const response = await request.post<ApiResult<unknown>>('/merchant/saveMerchant', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 更新商户
 * @param data 商户数据
 * @returns Promise<string>
 */
export async function updateMerchant(data: Merchant) {
    const response = await request.put<ApiResult<unknown>>('/merchant/saveMerchant', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 删除商户
 * @param list 删除列表 
 * @returns Promise<string>
 */
export async function deleteMerchant(list: number[]) {
    const response = await request.delete<ApiResult<unknown>>('/merchant/deleteMerchant', { data: { list } });
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}