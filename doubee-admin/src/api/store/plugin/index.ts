import type { ApiResult, PageResult } from '@/api';
import request from '@/utils/request';
import type { Plugin, SearchParam } from './model';

export async function getPluginList(params?: SearchParam) {
  const response = await request.get<ApiResult<PageResult<Plugin>>>('/plugin/list', {
    params
  });
  if (response.data.code === 200) {
    return response.data.data;
  }
  return Promise.reject(new Error(response.data.message));
}

export async function installPlugin(data: Partial<Plugin>) {
  const response = await request.post<ApiResult<unknown>>('/plugin/install', data);
  if (response.data.code === 200) {
    return response.data.message;
  }
  return Promise.reject(new Error(response.data.message));
}

export async function updatePlugin(data: Partial<Plugin>) {
  const response = await request.put<ApiResult<unknown>>('/plugin/update', data);
  if (response.data.code === 200) {
    return response.data.message;
  }
  return Promise.reject(new Error(response.data.message));
}

export async function togglePlugin(data: { id: number; enabled: boolean }) {
  const response = await request.put<ApiResult<unknown>>('/plugin/toggle', data);
  if (response.data.code === 200) {
    return response.data.message;
  }
  return Promise.reject(new Error(response.data.message));
}

export async function uninstallPlugin(id: number) {
  const response = await request.delete<ApiResult<unknown>>('/plugin/uninstall', {
    data: { id }
  });
  if (response.data.code === 200) {
    return response.data.message;
  }
  return Promise.reject(new Error(response.data.message));
}



