<?php

declare(strict_types=1);

use app\exception\handle\AppExceptionHandle;
use app\kernel\database\contract\DatabaseInterface;
use app\kernel\database\Database;
use app\service\admin\impl\ConfigServiceImpl as SystemConfigServiceImpl;
use app\service\admin\impl\LogServiceImpl as SystemLogServiceImpl;
use app\service\admin\impl\UploadServiceImpl as SystemUploadServiceImpl;
use app\service\admin\impl\UserServiceImpl as SystemUserServiceImpl;
use app\service\admin\LogService as SystemLogService;
use app\service\admin\DictionaryService as SystemDictService;
use app\service\admin\impl\DictionaryServiceImpl as SystemDictServiceImpl;
use app\service\admin\UploadService as SystemUploadService;
use app\service\admin\UserService as SystemUserService;
use app\service\admin\MailerService as SystemMailerService;
use app\service\admin\impl\MailerServiceImpl as SystemMailerServiceImpl;
use app\service\admin\ConfigService as SystemConfigService;
use app\service\merchant\impl\UserServiceImpl as MerchantUserServiceImpl;
use app\service\merchant\UserService as MerchantUserService;
use app\service\openapi\impl\OrderServiceImpl as OpenApiOrderServiceImpl;
use app\service\openapi\OrderService as OpenApiOrderService;
use think\exception\Handle as KernelExceptionHandle;

return [
    // 自定义核心绑定关系
    KernelExceptionHandle::class => AppExceptionHandle::class,
    DatabaseInterface::class => Database::class,

    // 系统业务绑定关系
    SystemUserService::class => SystemUserServiceImpl::class,
    SystemDictService::class => SystemDictServiceImpl::class,
    SystemConfigService::class => SystemConfigServiceImpl::class,
    SystemLogService::class => SystemLogServiceImpl::class,
    SystemMailerService::class => SystemMailerServiceImpl::class,
    SystemUploadService::class => SystemUploadServiceImpl::class,

    // 商户业务绑定关系
    MerchantUserService::class => MerchantUserServiceImpl::class,

    // 开放平台业务绑定关系
    OpenApiOrderService::class => OpenApiOrderServiceImpl::class,
];
