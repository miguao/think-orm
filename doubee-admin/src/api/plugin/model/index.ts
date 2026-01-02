import { PageParam } from "@/api";

export interface PluginInfo {
    icon?: string;
    name?: string;
    author?: string;
    description?: string;
    version?: string;
    type?: string;
}

export interface PluginForm {
    [key: string]: any;
}

export interface Plugin {
    identifier?: number;
    info?: PluginInfo;
    form?: PluginForm;
    handler?: any;
}

export interface SearchParam extends PageParam {

}