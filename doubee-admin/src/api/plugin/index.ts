import request from "@/utils/request";
import type { Plugin, SearchParam } from "./model";
import type { ApiResult, PageResult } from '@/api';

/**
 * 获取已安装的插件列表
 * @param params 查询参数
 * @returns Promise<Plugin[]>
 */
export async function getInstalledPlugins(params: SearchParam) {
    const response = await request.get<ApiResult<PageResult<Plugin>>>(
        '/plugin/getInstalledPlugins',
        { params }
    );
    if (response.data.code === 200) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}