import type EleAdminLayout from '../ele-admin-layout/index.vue';
import type { BacktopProps } from '../ele-backtop/props';
import type EleBasicSelect from '../ele-basic-select/index.vue';
import type { BreadcrumbProps } from '../ele-breadcrumb/props';
import type { CropperProps } from '../ele-cropper/props';
import type { CardProps } from '../ele-card/props';
import type EleCrud from '../ele-crud/index';
import type { CrudProps } from '../ele-crud/props';
import type EleCrudBuilder from '../ele-crud-builder/index.vue';
import type EleDataTable from '../ele-data-table/index';
import type { DataTableProps } from '../ele-data-table/props';
import type EleDropdown from '../ele-dropdown/index.vue';
import type { DropdownProps } from '../ele-dropdown/props';
import type { FileListProps } from '../ele-file-list/props';
import type { ImageViewerProps } from '../ele-image-viewer/props';
import type { LoadingProps } from '../ele-loading/props';
import type EleMenus from '../ele-menus/index.vue';
import type { MenusProps } from '../ele-menus/props';
import type { MenuItem } from '../ele-menus/types';
import type { ModalProps } from '../ele-modal/props';
import type { PageProps } from '../ele-page/props';
import type { PaginationProps } from '../ele-pagination/props';
import type ElePopconfirm from '../ele-popconfirm/index.vue';
import type { PopconfirmProps } from '../ele-popconfirm/props';
import type ElePopover from '../ele-popover/index.vue';
import type { PopoverProps } from '../ele-popover/props';
import type { PrinterProps } from '../ele-printer/props';
import type EleProForm from '../ele-pro-form/index.vue';
import type { ProFormProps } from '../ele-pro-form/props';
import type EleProFormBuilder from '../ele-pro-form-builder/index.vue';
import type { ProFormBuilderProps } from '../ele-pro-form-builder/props';
import type EleProTable from '../ele-pro-table/index.vue';
import type { ProTableProps } from '../ele-pro-table/props';
import type EleSplitPanel from '../ele-split-panel/index.vue';
import type { SplitPanelProps } from '../ele-split-panel/props';
import type { TableProps } from '../ele-table/props';
import type EleTabs from '../ele-tabs/index.vue';
import type EleText from '../ele-text/index.vue';
import type { TextProps } from '../ele-text/props';
import type EleTool from '../ele-tool/index.vue';
import type { ToolProps } from '../ele-tool/props';
import type { ToolbarProps } from '../ele-toolbar/props';
import type EleTooltip from '../ele-tooltip/index.vue';
import type { TooltipProps } from '../ele-tooltip/props';
import type EleUploadList from '../ele-upload-list/index.vue';
import type { UploadListProps } from '../ele-upload-list/props';
import type EleVirtualTable from '../ele-virtual-table/index.vue';
import type ReceiverView from '../ele-config-provider/components/receiver-view';
import type { MessageOptions } from '../utils/message';
import type { MessageBoxOptions } from '../utils/message-box';
import type { ComponentProps } from './types';

/**
 * EleAdminLayout
 */
export type EleAdminLayoutInstance = InstanceType<typeof EleAdminLayout> | null;

/**
 * EleBacktop
 */
export type EleBacktopProps = ComponentProps<BacktopProps>;

/**
 * EleBasicSelect
 */
export type EleBasicSelectInstance = InstanceType<typeof EleBasicSelect> | null;

/**
 * EleBreadcrumb
 */
export type EleBreadcrumbProps = ComponentProps<BreadcrumbProps>;

/**
 * EleCard
 */
export type EleCardProps = ComponentProps<CardProps>;

/**
 * EleConfigProvider
 */
export type ReceiverViewInstance = InstanceType<typeof ReceiverView> | null;

/**
 * EleCropper
 */
export type EleCropperProps = ComponentProps<CropperProps>;

/**
 * EleCrud
 */
export type EleCrudInstance = InstanceType<typeof EleCrud> | null;

export type EleCrudProps = ComponentProps<CrudProps>;

/**
 * EleCrud
 */
export type EleCrudBuilderInstance = InstanceType<typeof EleCrudBuilder> | null;

/**
 * EleDataTable
 */
export type EleDataTableInstance = InstanceType<typeof EleDataTable> | null;

export type EleDataTableProps = ComponentProps<DataTableProps>;

/**
 * EleDropdown
 */
export type EleDropdownInstance = InstanceType<typeof EleDropdown> | null;

export type EleDropdownProps = ComponentProps<DropdownProps>;

/**
 * EleFileList
 */
export type EleFileListProps = ComponentProps<FileListProps>;

/**
 * EleImageViewer
 */
export type EleImageViewerProps = ComponentProps<ImageViewerProps>;

/**
 * EleLoading
 */
export type EleLoadingProps = ComponentProps<LoadingProps>;

/**
 * EleMenus
 */
export type EleMenusInstance = InstanceType<typeof EleMenus> | null;

export type EleMenusProps = ComponentProps<MenusProps>;

export type EleMenuItemProps = Partial<MenuItem>;

/**
 * EleModal
 */
export type EleModalProps = ComponentProps<ModalProps>;

/**
 * ElePage
 */
export type ElePageProps = ComponentProps<PageProps>;

/**
 * ElePagination
 */
export type ElePaginationProps = ComponentProps<PaginationProps>;

/**
 * ElePopconfirm
 */
export type ElePopconfirmInstance = InstanceType<typeof ElePopconfirm> | null;

export type ElePopconfirmProps = Partial<PopconfirmProps>;

/**
 * ElePopover
 */
export type ElePopoverInstance = InstanceType<typeof ElePopover> | null;

export type ElePopoverProps = Partial<PopoverProps>;

/**
 * ElePrinter
 */
export type ElePrinterProps = ComponentProps<PrinterProps>;

/**
 * EleProForm
 */
export type EleProFormInstance = InstanceType<typeof EleProForm> | null;

export type EleProFormProps = ComponentProps<ProFormProps>;

/**
 * EleProFormBuilder
 */
export type EleProFormBuilderInstance = InstanceType<
  typeof EleProFormBuilder
> | null;

export type EleProFormBuilderProps = ComponentProps<ProFormBuilderProps>;

/**
 * EleProTable
 */
export type EleProTableInstance = InstanceType<typeof EleProTable> | null;

export type EleProTableProps = ComponentProps<ProTableProps>;

/**
 * EleSplitPanel
 */
export type EleSplitPanelInstance = InstanceType<typeof EleSplitPanel> | null;

export type EleSplitPanelProps = ComponentProps<SplitPanelProps>;

/**
 * EleTable
 */
export type EleTableProps = ComponentProps<TableProps>;

/**
 * EleTabs
 */
export type EleTabsInstance = InstanceType<typeof EleTabs> | null;

/**
 * EleText
 */
export type EleTextInstance = InstanceType<typeof EleText> | null;

export type EleTextProps = ComponentProps<TextProps>;

/**
 * EleTool
 */
export type EleToolInstance = InstanceType<typeof EleTool> | null;

export type EleToolProps = Partial<ToolProps>;

/**
 * EleToolbar
 */
export type EleToolbarProps = ComponentProps<ToolbarProps>;

/**
 * EleTooltip
 */
export type EleTooltipInstance = InstanceType<typeof EleTooltip> | null;

export type EleTooltipProps = Partial<TooltipProps>;

/**
 * EleUploadList
 */
export type EleUploadListInstance = InstanceType<typeof EleUploadList> | null;

export type EleUploadListProps = Partial<UploadListProps>;

/**
 * EleVirtualTable
 */
export type EleVirtualTableInstance = InstanceType<
  typeof EleVirtualTable
> | null;

/**
 * Message
 */
export type EleMessageOptions = Partial<MessageOptions>;

/**
 * MessageBox
 */
export type EleMessageBoxOptions = Partial<MessageBoxOptions>;
