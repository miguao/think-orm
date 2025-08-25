import { PageParam } from "@/api";

export interface Bank {
    id?: number;
    icon?: string;
    name?: string;
    code?: string;
    creation_time?: string;
    status?: number;
}

export interface SearchParam extends PageParam {
    'search-name'?: string;
    'search-code'?: string;
}