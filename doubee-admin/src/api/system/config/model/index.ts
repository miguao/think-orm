import { PageParam } from '@/api';

export interface Config {
    id?: number;
    name?: string;
    key?: string;
    public?: number;
    creation_time?: string;
    status?: number;
    configData?: ConfigField[],
}

export interface ConfigField {
    id?: number;
    config_id?: number;
    key?: string;
    title?: string;
    value?: string;
    component_type?: string;
    component_data?: string;
    data_mode?: number;
    dict_key?: string;
    required?: number;
    pattern?: string;
    error_message?: string;
    sort?: number;
    remark?: string;
    creation_time?: string;
    update_time?: string;
}

export interface SearchParam extends PageParam {
    'search-name'?: string;
    'search-key'?: string;
}