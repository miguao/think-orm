import type { PageParam } from '@/api';
import { Permission } from '../../permission/model';
import { TreeKey } from 'element-plus';

export interface Group {
  id?: number;
  name?: string;
  creation_time?: string;
  status?: number;
  permissions?: Permission[] | TreeKey[];
}

export interface SearchParam extends PageParam {
  'search-name'?: string;
}
