import { PageParam } from '@/api';

export interface Order {
    id?: number;
    creation_time?: string;
    status?: number;
}

export interface SearchParam extends PageParam {
    'search-name'?: string;
    'search-code'?: string;
}
