import { PageParam } from "@/api";

export interface Application {
    id?: number;
    merchant_id?: number;
    application_no?: number;
    name?: string;
    secret?: string;
    private_key?: string;
    public_key?: string;
    sign_type?: number;
    creation_time?: string;
    status?: number;
}

export interface SearchParam extends PageParam {
    'search-application_no'?: number;
    'search-name'?: string;
}