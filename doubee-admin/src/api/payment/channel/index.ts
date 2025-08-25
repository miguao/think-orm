import request from "@/utils/request";
import type { Channel, SearchParam } from "./model";
import { ApiResult, PageResult } from "@/api";

/**
 * 获取通道列表
 * @param params 查询参数
 * @returns Promise<Channel[]>
 */
export async function getChannelList(params: SearchParam) {
    const response = await request.get<ApiResult<PageResult<Channel>>>('/payment/channel/getChannelList', { params });
    if (response.data.code === 200) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 添加通道
 * @param data 通道数据
 * @returns Promise<string>
 */
export async function addChannel(data: Channel) {
    const response = await request.post<ApiResult<unknown>>('/payment/channel/saveChannel', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 更新通道
 * @param data 通道数据
 * @returns Promise<string>
 */
export async function updateChannel(data: Channel) {
    const response = await request.put<ApiResult<unknown>>('/payment/channel/saveChannel', data);
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}

/**
 * 删除通道
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteChannel(list: number[]) {
    const response = await request.delete<ApiResult<unknown>>('/payment/channel/deleteChannel', { data: { list } });
    if (response.data.code === 200) {
        return response.data.message;
    }

    return Promise.reject(new Error(response.data.message));
}