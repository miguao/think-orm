import request from '@/utils/request';
import type { ApiResult } from '@/api';
import { Config, SearchParam } from './model';

/**
 * 获取配置列表
 * @param params 查询参数
 * @returns Promise<Config[]>
 */
export async function getConfigList(params?: SearchParam) {
  const response = await request.get<ApiResult<Config[]>>(
    '/config/getConfigList',
    {
      params
    }
  );
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加配置
 * @param data 配置数据
 * @returns Promise<string>
 */
export async function addConfig(data: Config) {
  const response = await request.post<ApiResult<unknown>>(
    '/config/saveConfig',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新配置
 * @param data 配置数据
 * @returns Promise<string>
 */
export async function updateConfig(data: Config) {
  const response = await request.put<ApiResult<unknown>>(
    '/config/saveConfig',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除配置
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteConfig(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>(
    '/config/deleteConfig',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
