import { PageParam } from "@/api";

export interface Channel {
    id?: number;
    name?: string;
    status?: number;
}

export interface SearchParam extends PageParam {
    'search-name'?: string;
    'search-status'?: number;
}