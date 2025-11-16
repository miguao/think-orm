import { PageParam } from '@/api';

export interface Institution {
  id?: number;
  parent_id?: number;
  name?: string;
  full_name?: string;
  code?: string;
  type?: number;
  sort?: number;
  creation_time?: string;
  status?: number;
  children?: Institution[];
}

export interface SearchParam extends PageParam {
  'search-name'?: string;
  'search-full_name'?: string;
  'equal-type'?: number;
}
