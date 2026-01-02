import { PageParam } from "@/api";

export interface Application {
    id?: number;
    merchant_id?: number;
    application_no?: number;
    name?: string;
    secret?: string | null;
    mode?: 0 | 1;
    private_key?: string | null;
    public_key?: string | null;
    sign_type?: string;
    creation_time?: string;
    status?: 0 | 1;
    today_revenue?: number | string | null;
    yesterday_revenue?: number | string | null;
    total_revenue?: number | string | null;
}

export interface SearchParam extends PageParam {
}