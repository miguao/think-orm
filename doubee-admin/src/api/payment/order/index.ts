
import request from '@/utils/request';
import { ApiResult, PageResult } from '@/api';
import type { Order, SearchParam } from './model';

/**
 * 获取订单列表
 * @param params 查询参数
 * @returns Promise<Order[]>
 */
export async function getOrderList(params: SearchParam) {
    const response = await request.get<ApiResult<PageResult<Order>>>(
        '/payment/order/getOrderList',
        { params }
    );
    if (response.data.code === 200) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}