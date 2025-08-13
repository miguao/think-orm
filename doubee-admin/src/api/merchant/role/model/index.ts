import type { PageParam } from '@/api';

export interface Role {
    id?: number;
    name?: string;
    creation_time?: string;
    status?: number;
}

export interface SearchParam extends PageParam {
    'search-name'?: string;
}