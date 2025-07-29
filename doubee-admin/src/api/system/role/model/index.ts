import type { PageParam } from '@/api';

export interface Role {
  id?: number;
  name?: string;
  creation_time?: string;
  status?: number;
}

export interface SearchParam extends PageParam {
  
}

/**
 * 角色搜索条件
 */
export interface RoleParam extends PageParam {
  /** 角色名称 */
  roleName?: string;
  /** 角色标识 */
  roleCode?: string;
  /** 备注 */
  comments?: string;
}
