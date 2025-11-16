import { PageParam } from '@/api';

export interface Platform {
  id?: number;
  key?: string;
  icon?: string;
  name?: string;
  form?: string;
  options?: string;
  creation_time?: string;
  status?: number;
}

export interface SearchParam extends PageParam {
  'search-name'?: string;
}
