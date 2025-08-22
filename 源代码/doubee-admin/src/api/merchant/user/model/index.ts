import { PageParam } from "@/api";
import { Role } from "../../role/model";

export interface Merchant {
    id?: number;
    merchant_no?: number;
    phone?: string | null;
    password?: string;
    salting?: string;
    email?: string | null;
    balance?: number;
    freeze_balance?: number;
    creation_time?: string | Date;
    status?: number;
    role?: Role[]
}

export interface SearchParam extends PageParam {
    'search-merchant_no'?: number;
    'search-phone'?: string;
    'search-email'?: string;
}