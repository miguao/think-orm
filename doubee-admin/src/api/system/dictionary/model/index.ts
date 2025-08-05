import { PageParam } from '@/api';

export interface Dictionary {
  id?: number;
  name?: string;
  code?: string;
  sort?: number;
  remark?: string;
  creation_time?: string;
}

export interface SearchParam extends PageParam {
}