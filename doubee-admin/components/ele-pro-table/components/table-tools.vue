<!-- 默认工具按钮 -->
<template>
  <ToolExport
    ref="toolExportRef"
    :locale="lang"
    :cacheKey="cacheKey"
    :modalProps="exportConfig.modalProps"
    :columns="exportConfig.columns || columns"
    :selections="selections"
    :pageData="pageData"
    :datasource="exportConfig.datasource"
    :spanMethod="spanMethod"
    :tableHeader="exportConfig.showHeader ?? tableHeader"
    :showSummary="showSummary"
    :sumText="sumText"
    :summaryMethod="summaryMethod"
    :pageIndex="pageIndex"
    :treeProps="treeProps"
    :fetch="fetch"
    :defaultFileName="exportConfig.fileName"
    :defaultDataType="exportConfig.dataType"
    :defaultShowFooter="exportConfig.showFooter"
    :defaultShowTreeIndex="exportConfig.showTreeIndex"
    :beforeExport="exportConfig.beforeExport"
  />
  <ToolPrint
    ref="toolPrintRef"
    :locale="lang"
    :cacheKey="cacheKey"
    :modalProps="printConfig.modalProps"
    :printerProps="printConfig.printerProps"
    :tableProps="printConfig.tableProps"
    :columns="printConfig.columns || columns"
    :selections="selections"
    :pageData="pageData"
    :datasource="printConfig.datasource"
    :spanMethod="spanMethod"
    :tableHeader="printConfig.showHeader ?? tableHeader"
    :showSummary="showSummary"
    :sumText="sumText"
    :summaryMethod="summaryMethod"
    :tableStyle="tableStyle"
    :cellStyle="cellStyle"
    :cellClassName="cellClassName"
    :headerCellStyle="headerCellStyle"
    :headerCellClassName="headerCellClassName"
    :pageIndex="pageIndex"
    :treeProps="treeProps"
    :fetch="fetch"
    :defaultDataType="printConfig.dataType"
    :defaultShowFooter="printConfig.showFooter"
    :defaultShowTreeIndex="printConfig.showTreeIndex"
    :beforePrint="printConfig.beforePrint"
  >
    <template v-for="name in Object.keys($slots)" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>
  </ToolPrint>
  <template v-for="(tool, index) in tools">
    <!-- 刷新 -->
    <EleTool
      v-if="tool === 'reload'"
      :key="index + '-reload'"
      :placement="placement"
      :title="lang.refresh"
      @click="reload"
    >
      <ElIcon>
        <ReloadOutlined />
      </ElIcon>
    </EleTool>
    <!-- 导出 -->
    <EleTool
      v-else-if="tool === 'export'"
      :key="index + '-export'"
      :title="lang.export"
      :placement="placement"
      :clickHideTooltip="true"
      @click="openExportModal"
    >
      <ElIcon style="transform: scale(1.1); transform-origin: bottom">
        <DownloadOutlined />
      </ElIcon>
    </EleTool>
    <!-- 打印 -->
    <EleTool
      v-else-if="tool === 'print'"
      :key="index + '-print'"
      :title="lang.print"
      :placement="placement"
      :clickHideTooltip="true"
      @click="openPrintModal"
    >
      <ElIcon>
        <PrinterOutlined />
      </ElIcon>
    </EleTool>
    <!-- 密度设置 -->
    <EleTool
      v-else-if="tool === 'size'"
      :key="index + '-size'"
      :placement="placement"
      :title="lang.sizes"
    >
      <EleDropdown
        trigger="click"
        placement="bottom"
        :validateEvent="false"
        popperClass="ele-tool-size-popper"
        :modelValue="size"
        :items="sizeDropdownItems"
        :popperOptions="{
          modifiers: [{ name: 'offset', options: { offset: [0, 10] } }]
        }"
        @command="updateSize"
      >
        <ElIcon>
          <ColumnHeightOutlined />
        </ElIcon>
      </EleDropdown>
    </EleTool>
    <!-- 列设置 -->
    <ToolColumn
      v-else-if="tool === 'columns'"
      :key="index + '-columns'"
      :placement="placement"
      :locale="lang"
      :columns="columns"
      :sortable="columnSortable"
      :allowFixed="columnFixed"
      :cacheKey="cacheKey"
      @update:columns="updateColumns"
    />
    <!-- 全屏切换 -->
    <EleTool
      v-else-if="tool === 'maximized'"
      :key="index + '-maximized'"
      :placement="placement"
      :title="lang.maximized"
      :clickHideTooltip="true"
      @click="toggleMaximized"
    >
      <ElIcon>
        <FullscreenExitOutlined v-if="maximized" />
        <FullscreenOutlined v-else />
      </ElIcon>
    </EleTool>
    <!-- 自定义 -->
    <template v-else-if="tool && !ownSlots.includes(tool) && $slots[tool]">
      <slot :name="tool" :pageIndex="pageIndex" :fetch="fetch"></slot>
    </template>
  </template>
</template>

<script lang="ts" setup>
  import type { PropType } from 'vue';
  import { ref, computed } from 'vue';
  import { ElIcon } from 'element-plus';
  import {
    ReloadOutlined,
    DownloadOutlined,
    PrinterOutlined,
    ColumnHeightOutlined,
    SizeSlackOutlined,
    SizeMiddleOutlined,
    SizeCompactOutlined,
    FullscreenOutlined,
    FullscreenExitOutlined
  } from '../../icons/index';
  import type { StyleValue } from '../../ele-app/types';
  import EleDropdown from '../../ele-dropdown/index.vue';
  import { useLocale } from '../../ele-config-provider/receiver';
  import type { DropdownItem } from '../../ele-dropdown/types';
  import type {
    Columns,
    TableSize,
    DataItem,
    SpanMethod,
    SummaryMethod,
    CellStyle,
    CellClass,
    HeaderCellStyle,
    HeaderCellClass,
    TreeProps
  } from '../../ele-data-table/types';
  import { getSizeCacheKey } from '../util';
  import type {
    TableTool,
    TableLocale,
    FetchFunction,
    ExportConfig,
    PrintConfig,
    TableExportParams
  } from '../types';
  import type { ToolExportInstance, ToolPrintInstance } from '../props';
  import EleTool from '../../ele-tool/index.vue';
  import ToolColumn from './tool-column.vue';
  import ToolExport from './tool-export.vue';
  import ToolPrint from './tool-print.vue';
  import { markRaw } from 'vue';
  const ownSlots = ['default', 'printTop', 'printBottom'];

  const props = defineProps({
    /** 工具按钮布局 */
    tools: {
      type: Array as PropType<TableTool[]>,
      required: true
    },
    /** 表格尺寸 */
    size: String,
    /** 表格列数据 */
    columns: Array as PropType<Columns>,
    /** 是否开启列拖拽排序 */
    columnSortable: Boolean,
    /** 是否开启开关固定列 */
    columnFixed: Boolean,
    /** 是否最大化 */
    maximized: Boolean,
    /** 本地缓存的名称 */
    cacheKey: String,
    /** 国际化 */
    locale: Object as PropType<Partial<TableLocale>>,
    /** 表格选中数据 */
    selections: Array as PropType<DataItem[]>,
    /** 表格当前页数据 */
    pageData: Array as PropType<DataItem[]>,
    /** 单元格合并行列方法 */
    spanMethod: Function as PropType<SpanMethod>,
    /** 表格是否有表头 */
    tableHeader: Boolean,
    /** 是否显示合计行 */
    showSummary: Boolean,
    /** 合计行文本 */
    sumText: String,
    /** 合计行自定义方法 */
    summaryMethod: Function as PropType<SummaryMethod>,
    /** 自定义表格样式 */
    tableStyle: Object as PropType<StyleValue>,
    /** 单元格样式 */
    cellStyle: [Object, Function] as PropType<CellStyle>,
    /** 单元格类名自定义 */
    cellClassName: [String, Function] as PropType<CellClass>,
    /** 单元格样式 */
    headerCellStyle: [Object, Function] as PropType<HeaderCellStyle>,
    /** 单元格类名自定义 */
    headerCellClassName: [String, Function] as PropType<HeaderCellClass>,
    /** 序号列起始索引 */
    pageIndex: Number,
    /** 树表字段名 */
    treeProps: Object as PropType<TreeProps>,
    /** 表格请求数据方法 */
    fetch: Function as PropType<FetchFunction>,
    /** 导出配置 */
    exportConfig: {
      type: Object as PropType<ExportConfig>,
      required: true
    },
    /** 打印配置 */
    printConfig: {
      type: Object as PropType<PrintConfig>,
      required: true
    }
  });

  const emit = defineEmits({
    reload: () => true,
    'update:size': (_size: TableSize) => true,
    'update:columns': (
      _columns: Columns,
      _tableColumns: Columns,
      _isReset: boolean
    ) => true,
    'update:maximized': (_maximized: boolean) => true
  });

  const { lang } = useLocale<TableLocale>('table', props);

  /** 导出组件 */
  const toolExportRef = ref<ToolExportInstance>(null);

  /** 打印组件 */
  const toolPrintRef = ref<ToolPrintInstance>(null);

  /** 提示方向 */
  const placement = computed(() => (props.maximized ? 'bottom' : 'top'));

  /** 尺寸下拉选择数据 */
  const sizeDropdownItems = computed<DropdownItem[]>(() => {
    return [
      {
        title: lang.value.sizeLarge,
        command: 'large',
        icon: markRaw(SizeSlackOutlined)
      },
      {
        title: lang.value.sizeDefault,
        command: 'default',
        icon: markRaw(SizeMiddleOutlined)
      },
      {
        title: lang.value.sizeSmall,
        command: 'small',
        icon: markRaw(SizeCompactOutlined)
      }
    ];
  });

  /** 刷新表格 */
  const reload = () => {
    emit('reload');
  };

  /** 修改表格尺寸 */
  const updateSize = (size: string) => {
    if (props.cacheKey) {
      localStorage.setItem(getSizeCacheKey(props.cacheKey), size);
    }
    emit('update:size', size as TableSize);
  };

  /** 修改列配置 */
  const updateColumns = (
    columns: Columns,
    tableColumns: Columns,
    isReset: boolean
  ) => {
    emit('update:columns', columns, tableColumns, isReset);
  };

  /** 全屏切换 */
  const toggleMaximized = () => {
    emit('update:maximized', !props.maximized);
  };

  /** 打开导出弹窗 */
  const openExportModal = () => {
    if (toolExportRef.value) {
      toolExportRef.value.openModal();
    }
  };

  /** 打开打印弹窗 */
  const openPrintModal = () => {
    if (toolPrintRef.value) {
      toolPrintRef.value.openModal();
    }
  };

  /** 直接打印数据 */
  const printData = (params?: TableExportParams) => {
    if (toolPrintRef.value) {
      toolPrintRef.value.printData(params);
    }
  };

  /** 直接导出数据 */
  const exportData = (params?: TableExportParams) => {
    if (toolExportRef.value) {
      toolExportRef.value.exportData(params);
    }
  };

  defineExpose({
    openPrintModal,
    printData,
    openExportModal,
    exportData
  });
</script>
