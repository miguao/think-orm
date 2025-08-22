<?php

declare(strict_types=1);

namespace app\constant;

enum Component: string
{
    # 输入框组件
    case INPUT = 'input';

    # 长文本框组件
    case TEXTAREA = 'textarea';

    # 下拉选择框组件
    case SELECT = 'select';

    # 单选框组件
    case RADIO = 'radio';

    # 复选框组件
    case CHECKBOX = 'checkbox';

    # 开关组件
    case SWITCH = 'switch';

    # 文件上传组件
    case FILE = 'file';

    # 图片上传组件
    case IMAGE = 'image';

    # 富文本组件
    case EDITOR = 'editor';
}
