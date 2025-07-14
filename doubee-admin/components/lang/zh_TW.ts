/** 繁体中文 */
import type { EleLocale } from '../ele-config-provider/types';

const localeValues: EleLocale = {
  cropper: {
    zoomIn: '放大',
    zoomOut: '縮小',
    rotateLeft: '向左旋轉',
    rotateRight: '向右旋轉',
    moveLeft: '左移',
    moveRight: '右移',
    moveUp: '上移',
    moveDown: '下移',
    flipX: '左右翻轉',
    flipY: '上下翻轉',
    reset: '重新開始',
    upload: '選擇圖片',
    ok: '完成',
    title: '裁剪圖片'
  },
  fileList: {
    selectAll: '全選',
    selectTips: '已選中 {total} 個檔案/資料夾',
    fileName: '檔名',
    fileSize: '大小',
    fileTimestamp: '修改日期'
  },
  map: {
    title: '選擇位置',
    placeholder: '輸入關鍵字蒐索',
    message: '請點擊清單選中位置',
    ok: '確認',
    clickMessage: '請點擊地圖選中位置'
  },
  table: {
    refresh: '重繪',
    sizes: '密度',
    columns: '列設定',
    maximized: '全屏',
    export: '匯出',
    print: '打印',
    sizeLarge: '寬鬆',
    sizeDefault: '中等',
    sizeSmall: '緊湊',
    columnTitle: '列展示',
    columnReset: '重置',
    columnUntitled: '無標題',
    columnIndex: '序號欄',
    columnSelection: '選擇欄',
    columnExpand: '展開欄',
    columnFixedLeft: '固定在左側',
    columnFixedRight: '固定在右側',
    columnWidth: '欄寬',
    exportOk: '確認',
    exportCancel: '取消',
    exportFileName: '檔名',
    exportFileNamePlaceholder: '請輸入檔名',
    exportSelectData: '選擇數據',
    exportSelectColumn: '選擇字段',
    exportDataTypePage: '當前頁數據',
    exportDataTypeSelected: '選中數據',
    exportDataTypeAll: '全部數據',
    exportOther: '更多選項',
    exportOtherHeader: '表頭',
    exportOtherFooter: '表尾',
    exportOtherTreeIndex: '層級序號'
  },
  copyable: {
    copy: '複製',
    copied: '複製成功'
  },
  upload: {
    uploading: '上傳中',
    exception: '上傳失敗',
    retry: '重試',
    remove: '刪除',
    preview: '預覽',
    edit: '修改'
  },
  tour: {
    skip: '跳過',
    prev: '上一步',
    next: '下一步',
    finish: '結束'
  },
  proForm: {
    submit: '提交',
    reset: '重置',
    expand: '展開',
    shrink: '收起',
    required: '{label}必填'
  },
  crud: {
    save: '儲存',
    cancel: '取消',
    action: '操作',
    add: '新增',
    edit: '編輯',
    delete: '刪除',
    deleteConfirm: '確定要刪除嗎？',
    deleteConfirmTitle: '刪除確認',
    deleteBatch: '刪除',
    deleteBatchConfirm: '確定要刪除選中的資料嗎？',
    deleteBatchConfirmTitle: '刪除確認',
    deleteBatchTip: '請至少選擇一筆資料',
    deleteLoading: '刪除中..',
    deleteSuccess: '刪除成功',
    deleteError: '刪除失敗',
    editSuccess: '修改成功',
    editError: '修改失敗',
    addSuccess: '新增成功',
    addError: '新增失敗',
    search: '查詢'
  }
};

export default localeValues;
