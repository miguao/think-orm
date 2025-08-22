import { PageParam } from '@/api';

export interface DictionaryData {
  id?: number;
  dictionary_id?: number;
  name: string;
  value: string;
  sort?: number;
  creation_time?: string;
  status?: number;
}

export interface SearchParam extends PageParam {
  'equal-dictionary_id'?: number;
  'search-name'?: string;
  'search-value'?: string;
}
