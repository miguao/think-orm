import request from '@/utils/request';
import type { Platform, SearchParam } from './model';
import { ApiResult, PageResult } from '@/api';

/**
 * 获取平台列表
 * @param params 查询参数
 * @returns Promise<Platform[]>
 */
export async function getPlatformList(params: SearchParam) {
  const response = await request.get<ApiResult<PageResult<Platform>>>(
    '/payment/platform/getPlatformList',
    { params }
  );
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加平台
 * @param data 平台数据
 * @returns Promise<string>
 */
export async function addPlatform(data: Platform) {
  const response = await request.post<ApiResult<unknown>>(
    '/payment/platform/savePlatform',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新平台
 * @param data 平台数据
 * @returns Promise<string>
 */
export async function updatePlatform(data: Platform) {
  const response = await request.put<ApiResult<unknown>>(
    '/payment/platform/savePlatform',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除平台
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deletePlatform(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>(
    '/payment/platform/deletePlatform',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
