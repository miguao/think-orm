import request from '@/utils/request';
import type { ApiResult } from '@/api';
import type { Dictionary, SearchParam } from './model';
import { DictionaryData } from '../dictionary-data/model';

/**
 * 根据字典编码获取字典数据
 * @returns Promise<Dictionary[]>
 */
export async function getDictionaryByCode(code: string) {
  const response = await request.get<ApiResult<DictionaryData[]>>('/dictionary/getDictionaryByCode', {
    params: { code }
  });
  if (response.data.code === 200 && response.data.data) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 获取所有字典列表
 * @param params 查询参数
 * @returns Promise<Dictionary[]>
 */
export async function getAllDictionaryList(params?: SearchParam) {
  const response = await request.get<ApiResult<Dictionary[]>>('/dictionary/getAllDictionaryList', {
    params
  });
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加字典
 * @param data 字典数据
 * @returns Promise<string>
 */
export async function addDictionary(data: Dictionary) {
  const response = await request.post<ApiResult<unknown>>(
    '/dictionary/saveDictionary',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新字典
 * @param data 字典数据
 * @returns Promise<string>
 */
export async function updateDictionary(data: Dictionary) {
  const response = await request.put<ApiResult<unknown>>('/dictionary/saveDictionary', data);
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除字典
 * @param id 字典ID
 * @returns Promise<string>
 */
export async function deleteDictionary(id?: number) {
  const response = await request.delete<ApiResult<unknown>>(
    '/dictionary/deleteDictionary', { data: { list: [id] } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
