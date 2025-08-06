import request from '@/utils/request';
import type { ApiResult, PageResult } from '@/api';
import type { DictionaryData, SearchParam } from './model';

/**
 * 获取字典数据列表
 * @param params 查询参数
 * @returns Promise<DictionaryData[]>
 */
export async function getDataList(params: SearchParam) {
  const response = await request.get<ApiResult<PageResult<DictionaryData>>>(
    '/dictionary/data/getDataList',
    { params }
  );
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加字典数据
 * @param data 字典数据
 * @returns Promise<string>
 */
export async function addDictionaryData(data: DictionaryData) {
  const response = await request.post<ApiResult<unknown>>(
    '/dictionary/data/saveData',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新字典数据
 * @param data 字典数据
 * @returns Promise<string>
 */
export async function updateDictionaryData(data: DictionaryData) {
  const response = await request.put<ApiResult<unknown>>(
    '/dictionary/data/saveData',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除字典数据
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteData(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>(
    '/dictionary/data/deleteData',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
