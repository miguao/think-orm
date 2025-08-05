import type { PageParam } from '@/api';
import type { Permission } from '../../permission/model';
import { TreeKey } from 'element-plus';

export interface Role {
  id?: number;
  name?: string;
  creation_time?: string;
  status?: number;
  permissions?: Permission[] | TreeKey[]
}

export interface SearchParam extends PageParam {
  'search-name'?: string;
}

/**
 * 角色搜索条件
 */
export interface RoleParam extends PageParam {
  name?: string;
}
