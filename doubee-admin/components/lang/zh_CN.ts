/** 简体中文 */
import type { EleLocale } from '../ele-config-provider/types';

const localeValues: EleLocale = {
  cropper: {
    zoomIn: '放大',
    zoomOut: '缩小',
    rotateLeft: '向左旋转',
    rotateRight: '向右旋转',
    moveLeft: '左移',
    moveRight: '右移',
    moveUp: '上移',
    moveDown: '下移',
    flipX: '左右翻转',
    flipY: '上下翻转',
    reset: '重新开始',
    upload: '选择图片',
    ok: '完成',
    title: '裁剪图片'
  },
  fileList: {
    selectAll: '全选',
    selectTips: '已选中 {total} 个文件/文件夹',
    fileName: '文件名',
    fileSize: '大小',
    fileTimestamp: '修改日期'
  },
  map: {
    title: '选择位置',
    placeholder: '输入关键字搜索',
    message: '请点击列表选中位置',
    ok: '确定',
    clickMessage: '请点击地图选中位置'
  },
  table: {
    refresh: '刷新',
    sizes: '密度',
    columns: '列设置',
    maximized: '全屏',
    export: '导出',
    print: '打印',
    sizeLarge: '宽松',
    sizeDefault: '中等',
    sizeSmall: '紧凑',
    columnTitle: '全选',
    columnReset: '重置',
    columnUntitled: '无标题',
    columnIndex: '序号列',
    columnSelection: '选择列',
    columnExpand: '展开列',
    columnFixedLeft: '固定在左侧',
    columnFixedRight: '固定在右侧',
    columnWidth: '列宽',
    exportOk: '确定',
    exportCancel: '取消',
    exportFileName: '文件名',
    exportFileNamePlaceholder: '请输入文件名',
    exportSelectData: '选择数据',
    exportSelectColumn: '选择字段',
    exportDataTypePage: '当前页数据',
    exportDataTypeSelected: '选中数据',
    exportDataTypeAll: '全部数据',
    exportOther: '其它选项',
    exportOtherHeader: '表头',
    exportOtherFooter: '表尾',
    exportOtherTreeIndex: '层级序号'
  },
  copyable: {
    copy: '复制',
    copied: '复制成功'
  },
  upload: {
    uploading: '上传中',
    exception: '上传失败',
    retry: '重试',
    remove: '删除',
    preview: '预览',
    edit: '修改'
  },
  tour: {
    skip: '跳过',
    prev: '上一步',
    next: '下一步',
    finish: '结束'
  },
  proForm: {
    submit: '提交',
    reset: '重置',
    expand: '展开',
    shrink: '收起',
    required: '{label}必填'
  },
  crud: {
    save: '保存',
    cancel: '取消',
    action: '操作',
    add: '添加',
    edit: '修改',
    delete: '删除',
    deleteConfirm: '确定要删除吗？',
    deleteConfirmTitle: '删除确认',
    deleteBatch: '删除',
    deleteBatchConfirm: '确定要删除选中数据吗？',
    deleteBatchConfirmTitle: '删除确认',
    deleteBatchTip: '请至少选择一条数据',
    deleteLoading: '删除中..',
    deleteSuccess: '删除成功',
    deleteError: '删除失败',
    editSuccess: '修改成功',
    editError: '修改失败',
    addSuccess: '添加成功',
    addError: '添加失败',
    search: '查询'
  }
};

export default localeValues;
