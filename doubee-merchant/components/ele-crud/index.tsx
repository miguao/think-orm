/** 配置式增删改查 */
import type { VNode } from 'vue';
import {
  defineComponent,
  ref,
  reactive,
  computed,
  watch,
  h,
  onBeforeUnmount
} from 'vue';
import type { EleProFormProps, EleProTableInstance } from '../ele-app/plus';
import { useLocale } from '../ele-config-provider/receiver';
import { omit, mapTree, getMappedSlots } from '../utils/common';
import { useMobile } from '../utils/hook';
import { useMessage } from '../utils/message';
import { useMessageBox } from '../utils/message-box';
import ElePage from '../ele-page/index.vue';
import EleCard from '../ele-card/index.vue';
import EleProTable from '../ele-pro-table/index.vue';
import EleProForm from '../ele-pro-form/index.vue';
import {
  setValue,
  mergeValue,
  cloneDeep,
  getFormInitValue
} from '../ele-pro-form/util';
import type { ProFormItemProps } from '../ele-pro-form/types';
import EleSplitPanel from '../ele-split-panel/index.vue';
import { getValue as getRowValue } from '../ele-data-table/util';
import type { DataItem, Columns, Column } from '../ele-data-table/types';
import type { DatasourceFunction } from '../ele-pro-table/types';
import TableToolbar from './components/table-toolbar.vue';
import TableAction from './components/table-action.vue';
import TableExtra from './components/table-extra.vue';
import PageSide from './components/page-side.vue';
import {
  getDefaultSearchFormProps,
  getFieldsSearchFormItems,
  getTreeValueField,
  codeStringPrefix,
  getCodeResult
} from './util';
import type {
  BtnClickAction,
  CrudField,
  DeletePopOption,
  DeleteApi,
  TreeListApi,
  CrudLocale
} from './types';
import { crudProps, crudEmits } from './props';

export default defineComponent({
  name: 'EleCrud',
  props: crudProps,
  emits: crudEmits,
  setup(props, { emit, slots, expose }) {
    const { lang } = useLocale<CrudLocale>('crud', props);
    const message = useMessage({ plain: true });
    const messageBox = useMessageBox();
    const defaultSearchFormProps = getDefaultSearchFormProps();

    /** 表格组件 */
    const tableRef = ref<EleProTableInstance>(null);

    /** 表格选中数据 */
    const selections = ref<DataItem[]>([]);

    /** 添加弹窗是否打开 */
    const addVisible = ref<boolean | undefined>(false);

    /** 添加弹窗数据 */
    const addData = ref<DataItem>();

    /** 修改弹窗是否打开 */
    const editVisible = ref<boolean | undefined>(false);

    /** 修改弹窗数据 */
    const editData = ref<DataItem>();

    /** 删除气泡配置 */
    const deletePopOption = ref<DeletePopOption>();

    /** 搜索栏配置 */
    const searchFormProps = computed<EleProFormProps>(() => {
      const searchConfig =
        (props.searchConfig === true ? void 0 : props.searchConfig) || {};
      return searchConfig.formProps || {};
    });

    /** 搜索表单项配置数据 */
    const searchFormItems = computed<ProFormItemProps[]>(() => {
      const items = searchFormProps.value.items;
      return items?.length ? items : getFieldsSearchFormItems(props.fields);
    });

    /** 搜索表单数据 */
    const searchFormData = reactive<Record<string, any>>(
      getFormInitValue(
        searchFormItems.value,
        searchFormProps.value.itemTypeData || props.itemTypeData,
        true
      )
    );

    /** 表格搜索条件数据 */
    const searchWhere = ref<Record<string, any>>(cloneDeep(searchFormData));

    /** 侧栏树选中值 */
    const treeSelectedValue = ref<any>();

    /** 侧栏树数据 */
    const treeData = ref<Record<string, any>[]>([]);

    /** 侧栏树加载状态 */
    const treeLoading = ref(false);

    /** 侧栏树加载错误信息 */
    const treeErrorMessage = ref<string>();

    /** 分割面板折叠状态 */
    const splitPanelCollapse = ref<boolean | undefined>(false);

    const [isMobile] = useMobile((mobile) => {
      splitPanelCollapse.value = mobile;
    });

    /** 代码字符串解析缓存 */
    const codeCache = new Map<string, any>();

    /** 获取并缓存代码解析结果 */
    const getAndCacheCode = (code: string, codeResult?: any) => {
      const cacheResult = codeCache.get(code);
      if (cacheResult) {
        return cacheResult;
      }
      const result = codeResult ?? getCodeResult(code, props.httpRequest);
      codeCache.set(code, result);
      return result;
    };

    /** 表格数据源 */
    const tableDatasource: DatasourceFunction = (params) => {
      const datasource =
        props.listConfig?.tableProps?.datasource || props.listApi;
      if (datasource && Array.isArray(datasource)) {
        return Promise.resolve(datasource);
      }
      const func: DatasourceFunction | undefined =
        typeof datasource === 'function'
          ? datasource
          : typeof datasource === 'string' &&
              datasource.startsWith(codeStringPrefix)
            ? getAndCacheCode(datasource)
            : void 0;
      const showSide = !!props.pageConfig?.splitPanelProps;
      if (!func || (showSide && treeSelectedValue.value == null)) {
        return Promise.resolve([]);
      }
      const where: Record<string, any> = {};
      if (showSide && treeSelectedValue.value != null) {
        const filterField = props.pageConfig?.tableFilterField;
        if (filterField) {
          setValue(where, filterField, treeSelectedValue.value);
        }
      }
      mergeValue(where, searchWhere.value, params.where);
      return func({ ...params, where });
    };

    /** 获取表格选中数据 */
    const getTableSelections = () => {
      return selections.value;
    };

    /** 更新表格选中数据 */
    const handleUpdateSelections = (data: DataItem[]) => {
      selections.value = data;
      emit('tableSelectionsChange', data);
    };

    /** 刷新表格 */
    const reloadTable = (page?: number) => {
      tableRef.value?.reload?.({ page });
    };

    /** 更新搜索表单数据 */
    const handleUpdateSearchForm = (field: string, value: unknown) => {
      setValue(searchFormData, field, value);
    };

    /** 搜索表单提交 */
    const handleSubmitSearchForm = () => {
      selections.value = [];
      searchWhere.value = cloneDeep(searchFormData);
      reloadTable(1);
    };

    /** 搜索表单重置 */
    const handleResetSearchForm = () => {
      Object.keys(searchFormData).forEach((key) => {
        searchFormData[key] = void 0;
      });
      mergeValue(
        searchFormData,
        getFormInitValue(
          searchFormItems.value,
          searchFormProps.value.itemTypeData || props.itemTypeData,
          true
        )
      );
      handleSubmitSearchForm();
    };

    /** 按钮点击事件 */
    const handleBtnClick = (
      action: BtnClickAction,
      e: MouseEvent,
      item?: DataItem
    ) => {
      if (action === 'delConfirm') {
        handleDelete(item ? [item] : void 0);
      } else if (action === 'del') {
        const delPopConfirmProps = props.listConfig?.delPopConfirmProps;
        if (delPopConfirmProps === false) {
          handleDelete(item ? [item] : void 0);
        } else if (
          delPopConfirmProps === true ||
          !delPopConfirmProps?.isPopConfirm
        ) {
          messageBox
            .confirm(lang.value.deleteConfirm, lang.value.deleteConfirmTitle, {
              type: 'warning',
              draggable: true,
              ...omit(
                delPopConfirmProps === true ? void 0 : delPopConfirmProps,
                ['isPopConfirm']
              )
            })
            .then(() => {
              handleDelete(item ? [item] : void 0);
            })
            .catch(() => {});
        } else {
          deletePopOption.value = {
            item,
            triggerEl: e.currentTarget,
            confirmProps: delPopConfirmProps
          };
        }
      } else if (action === 'edit') {
        editData.value = item;
        editVisible.value = true;
      } else if (action === 'add') {
        const filterField = props.pageConfig?.tableFilterField;
        if (
          props.pageConfig?.splitPanelProps &&
          treeSelectedValue.value != null &&
          filterField
        ) {
          const data = {};
          setValue(data, filterField, treeSelectedValue.value);
          addData.value = data;
        } else {
          addData.value = void 0;
        }
        addVisible.value = true;
      } else if (action === 'delSelections') {
        const data = selections.value;
        if (!data.length) {
          message.error({
            ...(props.messageOptions || {}),
            message: lang.value.deleteBatchTip
          });
          return;
        }
        const delConfirmProps = props.listConfig?.delConfirmProps;
        if (delConfirmProps === false) {
          handleDelete(data);
        } else {
          messageBox
            .confirm(
              lang.value.deleteBatchConfirm,
              lang.value.deleteBatchConfirmTitle,
              {
                type: 'warning',
                draggable: true,
                ...((delConfirmProps === true ? void 0 : delConfirmProps) || {})
              }
            )
            .then(() => {
              handleDelete(data);
            })
            .catch(() => {});
        }
      }
    };

    /** 删除事件 */
    const handleDelete = (data?: DataItem[]) => {
      if (!data || !props.deleteApi) {
        return;
      }
      const deleteApiFunc: DeleteApi | undefined =
        typeof props.deleteApi === 'function'
          ? props.deleteApi
          : props.deleteApi.startsWith(codeStringPrefix)
            ? getAndCacheCode(props.deleteApi)
            : void 0;
      if (!deleteApiFunc) {
        return;
      }
      const loading = message.loading({
        ...(props.messageOptions || {}),
        message: lang.value.deleteLoading
      });
      const rowKey = props.listConfig?.tableProps?.rowKey;
      const dataKeys = rowKey
        ? data.map((row) => getRowValue(row, rowKey))
        : [];
      deleteApiFunc(dataKeys as string[], data)
        .then((msg) => {
          loading.close();
          message.success({
            ...(props.messageOptions || {}),
            message: msg ?? lang.value.deleteSuccess
          });
          reloadTable(1);
        })
        .catch((e) => {
          loading.close();
          if (e != null && typeof e === 'string') {
            message.error({
              ...(props.messageOptions || {}),
              message: e
            });
          } else {
            message.error({
              ...(props.messageOptions || {}),
              message: e?.message ?? lang.value.deleteError
            });
          }
        });
    };

    /** 添加失败事件 */
    const handleAddError = (e?: Error | string) => {
      if (e != null && typeof e === 'string') {
        message.error({
          ...(props.messageOptions || {}),
          message: e
        });
      } else {
        message.error({
          ...(props.messageOptions || {}),
          message: e?.message ?? lang.value.addError
        });
      }
    };

    /** 编辑失败事件 */
    const handleEditError = (e?: Error | string) => {
      if (e != null && typeof e === 'string') {
        message.error({
          ...(props.messageOptions || {}),
          message: e
        });
      } else {
        message.error({
          ...(props.messageOptions || {}),
          message: e?.message ?? lang.value.editError
        });
      }
    };

    /** 添加成功事件 */
    const handleAddDone = (msg?: string) => {
      message.success({
        ...(props.messageOptions || {}),
        message: msg ?? lang.value.editSuccess
      });
      reloadTable(1);
    };

    /** 修改成功事件 */
    const handleEditDone = (msg?: string) => {
      message.success({
        ...(props.messageOptions || {}),
        message: msg ?? lang.value.addSuccess
      });
      reloadTable();
    };

    /** 更新添加弹窗打开状态 */
    const handleUpdateAddVisible = (visible?: boolean) => {
      addVisible.value = visible;
    };

    /** 更新修改弹窗打开状态 */
    const handleUpdateEditVisible = (visible?: boolean) => {
      editVisible.value = visible;
    };

    /** 侧栏树点击事件 */
    const handleTreeNodeClick = (nodeValue?: any) => {
      if (isMobile.value) {
        splitPanelCollapse.value = true;
      }
      if (treeSelectedValue.value !== nodeValue) {
        treeSelectedValue.value = nodeValue;
        reloadTable();
      }
    };

    /** 设置树数据 */
    const setTreeData = (data?: Record<string, any>[]) => {
      treeLoading.value = false;
      treeData.value = data || [];
      if (treeData.value.length) {
        const field = getTreeValueField(props.pageConfig?.sideConfig);
        treeSelectedValue.value = treeData.value[0][field];
        reloadTable();
      } else {
        treeSelectedValue.value = void 0;
      }
    };

    /** 刷新树 */
    const reloadTree = () => {
      if (!props.treeListApi || !props.pageConfig?.splitPanelProps) {
        setTreeData([]);
        return;
      }
      if (Array.isArray(props.treeListApi)) {
        setTreeData(props.treeListApi);
        return;
      }
      const treeListApiFunc: TreeListApi | undefined =
        typeof props.treeListApi === 'function'
          ? props.treeListApi
          : props.treeListApi.startsWith(codeStringPrefix)
            ? getAndCacheCode(props.treeListApi)
            : void 0;
      if (!treeListApiFunc) {
        return;
      }
      treeLoading.value = true;
      treeListApiFunc()
        .then((data) => {
          setTreeData(data);
        })
        .catch((e) => {
          treeLoading.value = false;
          if (e != null && typeof e === 'string') {
            treeErrorMessage.value = e;
          } else {
            treeErrorMessage.value = e?.message;
          }
        });
    };

    /** 更新分割面板折叠状态 */
    const handleUpdateSplitPanelCollapse = (collapse?: boolean) => {
      splitPanelCollapse.value = collapse;
    };

    /** 清空代码解析结果缓存 */
    const clearCodeCache = () => {
      codeCache.clear();
    };

    onBeforeUnmount(() => {
      clearCodeCache();
    });

    /** 获取表格的默认列配置 */
    const getDefaultTableColumns = (
      fields?: CrudField[],
      selectionColumnProps?: boolean | Column,
      indexColumnProps?: boolean | Column,
      actionColumnProps?: boolean | Column
    ) => {
      const columns: Columns = [];
      if (selectionColumnProps !== false) {
        columns.push({
          type: 'selection',
          columnKey: 'selection',
          width: 50,
          align: 'center',
          fixed: 'left',
          ...((selectionColumnProps === true ? void 0 : selectionColumnProps) ||
            {})
        });
      }
      if (indexColumnProps !== false) {
        columns.push({
          type: 'index',
          columnKey: 'index',
          width: 50,
          align: 'center',
          fixed: 'left',
          ...((indexColumnProps === true ? void 0 : indexColumnProps) || {})
        });
      }
      mapTree<CrudField, Column>(fields || [], (field) => {
        if (field.hideInList === 'flat') {
          return 'flatChildren';
        } else if (!field.hideInList) {
          return {
            columnKey: field.key,
            prop: field.prop,
            label: field.label,
            ...(field.columnProps || {})
          };
        }
      }).forEach((column) => {
        columns.push(column);
      });
      if (actionColumnProps !== false) {
        columns.push({
          columnKey: 'action',
          label: lang.value.action,
          width: 120,
          align: 'center',
          fixed: 'right',
          slot: 'action',
          hideInPrint: true,
          hideInExport: true,
          ...((actionColumnProps === true ? void 0 : actionColumnProps) || {})
        });
      }
      return columns;
    };

    /** 渲染搜索栏 */
    const renderSearch = () => {
      if (props.searchConfig === false) {
        return;
      }
      const searchConfig =
        (props.searchConfig === true ? void 0 : props.searchConfig) || {};
      const cardProps = searchConfig.cardProps;

      const renderForm = () => {
        return h(
          props.proFormComponent || EleProForm,
          {
            ...defaultSearchFormProps,
            submitText: lang.value.search,
            itemTypeData: props.itemTypeData,
            httpRequest: props.httpRequest,
            screenSize: props.screenSize,
            ...searchFormProps.value,
            model: searchFormData,
            items: searchFormItems.value,
            onUpdateValue: handleUpdateSearchForm,
            onSubmit: handleSubmitSearchForm,
            onReset: handleResetSearchForm
          },
          getMappedSlots(slots, searchConfig.formSlots, [], [], true)
        );
      };
      if (!cardProps) {
        return renderForm();
      }
      return (
        <EleCard {...((cardProps === true ? void 0 : cardProps) || {})}>
          {{
            ...getMappedSlots(slots, searchConfig.cardSlots),
            default: renderForm
          }}
        </EleCard>
      );
    };

    /** 渲染表格 */
    const renderTable = () => {
      const listConfig = props.listConfig || {};
      const cardProps = listConfig.cardProps;
      const tableProps = listConfig.tableProps || {};
      const columns = tableProps.columns?.length
        ? tableProps.columns
        : getDefaultTableColumns(
            props.fields,
            listConfig.selectionColumnProps,
            listConfig.indexColumnProps,
            listConfig.actionColumnProps
          );
      const addBtnProps = listConfig.addBtnProps;
      const delBtnProps = listConfig.delBtnProps;
      const editLinkProps = listConfig.editLinkProps;
      const delLinkProps = listConfig.delLinkProps;
      const renderTable = () => {
        const tSlots = getMappedSlots(
          slots,
          listConfig.tableSlots,
          [],
          ['default'],
          true
        );
        const toolbarSlot = tSlots.toolbar;
        tSlots.toolbar = () => (
          <TableToolbar
            addBtnProps={addBtnProps}
            delBtnProps={delBtnProps}
            lang={lang.value}
            onBtnClick={handleBtnClick}
          >
            {{ default: toolbarSlot }}
          </TableToolbar>
        );
        const actionSlot = tSlots.action;
        tSlots.action = ({ row }: any) => (
          <TableAction
            editLinkProps={editLinkProps}
            delLinkProps={delLinkProps}
            item={row}
            lang={lang.value}
            onBtnClick={handleBtnClick}
          >
            {{ default: actionSlot }}
          </TableAction>
        );
        const bottomExtraSlot = tSlots.bottomExtra;
        tSlots.bottomExtra = () => (
          <TableExtra
            addVisible={addVisible.value}
            addData={addData.value}
            editVisible={editVisible.value}
            editData={editData.value}
            deletePopOption={deletePopOption.value}
            addConfig={props.addConfig}
            editConfig={props.editConfig}
            fields={props.fields}
            getAndCacheCode={getAndCacheCode}
            proFormComponent={props.proFormComponent}
            addApi={props.addApi}
            editApi={props.editApi}
            itemTypeData={props.itemTypeData}
            httpRequest={props.httpRequest}
            screenSize={props.screenSize}
            lang={lang.value}
            onBtnClick={handleBtnClick}
            onAddError={handleAddError}
            onAddDone={handleAddDone}
            onEditError={handleEditError}
            onEditDone={handleEditDone}
            onUpdate:addVisible={handleUpdateAddVisible}
            onUpdate:editVisible={handleUpdateEditVisible}
          >
            {{ ...omit(slots, ['default']), default: bottomExtraSlot }}
          </TableExtra>
        );
        const arrayDatasource =
          tableProps?.datasource == null
            ? props.listApi && Array.isArray(props.listApi)
              ? props.listApi
              : void 0
            : Array.isArray(tableProps.datasource)
              ? tableProps.datasource
              : void 0;
        return (
          <EleProTable
            {...tableProps}
            ref={tableRef}
            columns={columns}
            datasource={arrayDatasource || tableDatasource}
            selections={selections.value}
            onUpdate:selections={handleUpdateSelections}
          >
            {tSlots}
          </EleProTable>
        );
      };
      if (!cardProps) {
        return renderTable();
      }
      return (
        <EleCard {...((cardProps === true ? void 0 : cardProps) || {})}>
          {{
            ...getMappedSlots(slots, listConfig.cardSlots),
            default: renderTable
          }}
        </EleCard>
      );
    };

    /** 渲染主体内容 */
    const renderBodyContent = () => {
      const nodes: VNode[] = [];
      const sNodes = renderSearch();
      if (sNodes) {
        (Array.isArray(sNodes) ? sNodes : [sNodes]).forEach((node) => {
          nodes.push(node);
        });
      }
      const tbNodes = renderTable();
      if (tbNodes) {
        (Array.isArray(tbNodes) ? tbNodes : [tbNodes]).forEach((node) => {
          nodes.push(node);
        });
      }
      return nodes;
    };

    /** 渲染内容 */
    const renderContent = () => {
      const nodes: VNode[] = [];
      const bodyNodes = renderBodyContent();
      const pageConfig = props.pageConfig || {};
      const splitPanelProps = pageConfig.splitPanelProps;
      if (!splitPanelProps) {
        bodyNodes.forEach((node) => {
          nodes.push(node);
        });
      } else {
        nodes.push(
          <EleSplitPanel
            size="256px"
            space="0px"
            allowCollapse={true}
            collapse={splitPanelCollapse.value}
            class="ele-crud-split-panel"
            onUpdate:collapse={handleUpdateSplitPanelCollapse}
            {...((splitPanelProps === true ? void 0 : splitPanelProps) || {})}
          >
            {{
              ...getMappedSlots(slots, pageConfig.splitPanelSlots),
              default: () => (
                <PageSide
                  sideConfig={pageConfig.sideConfig}
                  data={treeData.value}
                  loading={treeLoading.value}
                  selectedValue={treeSelectedValue.value}
                  errorMessage={treeErrorMessage.value}
                  onTreeNodeClick={handleTreeNodeClick}
                >
                  {{ ...slots }}
                </PageSide>
              ),
              body: () => bodyNodes
            }}
          </EleSplitPanel>
        );
      }
      const cardProps = pageConfig.cardProps;
      if (!cardProps) {
        return nodes;
      }
      return (
        <EleCard {...((cardProps === true ? void 0 : cardProps) || {})}>
          {{
            ...getMappedSlots(slots, pageConfig.cardSlots),
            default: () => nodes
          }}
        </EleCard>
      );
    };

    /** 同步更新查询接口 */
    watch(
      () => props.listApi,
      () => {
        tableRef.value?.setData?.([]);
        handleSubmitSearchForm();
      },
      { deep: true }
    );

    watch(
      () => props.treeListApi,
      () => {
        reloadTree();
      },
      { deep: true, immediate: true }
    );

    /** 实例方法 */
    const exposeValue = { tableRef, getTableSelections, reloadTree };
    expose(exposeValue);

    return (() => {
      const pageProps = props.pageConfig?.pageProps;
      if (!pageProps) {
        return renderContent();
      }
      return (
        <ElePage {...((pageProps === true ? void 0 : pageProps) || {})}>
          {() => renderContent()}
        </ElePage>
      );
    }) as unknown as typeof exposeValue;
  }
});
