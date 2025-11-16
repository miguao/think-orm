import request from '@/utils/request';
import type { Bank, SearchParam } from './model';
import { ApiResult, PageResult } from '@/api';

/**
 * 获取银行列表
 * @param params 查询参数
 * @returns Promise<Bank[]>
 */
export async function getBankList(params: SearchParam) {
  const response = await request.get<ApiResult<PageResult<Bank>>>(
    '/payment/bank/getBankList',
    { params }
  );
  if (response.data.code === 200) {
    return response.data.data;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 添加银行
 * @param data 银行数据
 * @returns Promise<string>
 */
export async function addBank(data: Bank) {
  const response = await request.post<ApiResult<unknown>>(
    '/payment/bank/saveBank',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 更新银行
 * @param data 银行数据
 * @returns Promise<string>
 */
export async function updateBank(data: Bank) {
  const response = await request.put<ApiResult<unknown>>(
    '/payment/bank/saveBank',
    data
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}

/**
 * 删除银行
 * @param list 删除列表
 * @returns Promise<string>
 */
export async function deleteBank(list: number[]) {
  const response = await request.delete<ApiResult<unknown>>(
    '/payment/bank/deleteBank',
    { data: { list } }
  );
  if (response.data.code === 200) {
    return response.data.message;
  }

  return Promise.reject(new Error(response.data.message));
}
