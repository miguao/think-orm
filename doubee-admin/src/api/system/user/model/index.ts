import type { PageParam } from '@/api';
import type { Role } from '../../role/model';
import type { Permission } from '../../permission/model';

export interface User {
  id?: number;
  email?: string;
  password?: string;
  nickname?: string;
  avatar?: string;
  phone?: string;
  status?: number;
  roles?: Role[];
  menus?: Permission[];
  creation_time?: string;
}

/**
 * 用户搜索条件
 */
export interface UserParam extends PageParam {
  /** 账号 */
  username?: string;
  /** 昵称 */
  nickname?: string;
  /** 性别(字典) */
  sex?: string;
  /** 手机号 */
  phone?: string;
  /** 状态 */
  status?: number;
  /** 机构id */
  organizationId?: number;
  /** 性别名称 */
  sexName?: string;
  /** 机构名称 */
  organizationName?: string;
  /** 邮箱 */
  email?: string;
  /** 创建时间开始时间 */
  createTimeStart?: string;
  /** 创建时间截止时间 */
  createTimeEnd?: string;
}
