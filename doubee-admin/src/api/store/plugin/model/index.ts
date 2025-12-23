import type { PageParam } from '@/api';

export interface Plugin extends Record<string, any> {
  id?: number;
  name: string;
  version: string;
  vendor?: string;
  category?: string;
  description?: string;
  longDescription?: string;
  notice?: string;
  logo?: string;
  enabled?: boolean;
  official?: boolean;
  recommended?: boolean;
  paid?: boolean;
  stars?: number;
  installs?: number;
  updatedAt?: string | number | Date;
}

export interface SearchParam extends PageParam {
  keyword?: string;
  category?: string;
  status?: 'enabled' | 'disabled';
  order?:
    | 'updated_desc'
    | 'installs_desc'
    | 'stars_desc'
    | 'name_asc';
}


