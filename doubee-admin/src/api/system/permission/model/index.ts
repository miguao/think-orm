export interface Permission {
    id?: number;
    parent_id?: number;
    icon?: string;
    name?: string;
    path?: string;
    component?: string;
    authority?: string;
    type?: number;
    open_type?: number;
    hide?: number;
    sort?: number;
    metadata?: string;
    creation_time?: string;
    status?: number;
    children?: Permission[];
}

export interface SearchParam {
    'search-name'?: string;
    'search-path'?: string;
}