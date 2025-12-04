import React from 'react';
import { Features, type FeaturesProps } from '@lobehub/ui/awesome';
import { RefreshCw, Shield, Puzzle, Zap, Wallet, Smartphone } from 'lucide-react';
import { Center } from 'react-layout-kit';

const items: FeaturesProps['items'] = [
  {
    description:
      '支持微信、支付宝、云闪付等多种支付通道聚合，智能轮训调度确保高可用性，提供扫码支付、H5 支付、公众号支付等多样化支付方式，满足不同业务场景。',
    icon: RefreshCw,
    title: '智能聚合轮训',
  },
  {
    description:
      '接口签名验证、RSA 加解密、IP 白名单限制等多重安全防护机制，全方位保障交易安全，让每一笔支付都安全可靠。',
    icon: Shield,
    title: '安全防护体系',
  },
  {
    description:
      '灵活的插件扩展系统，支持自定义支付通道、业务逻辑扩展，通过插件机制快速适配各类定制化需求，轻松实现功能扩展。',
    icon: Puzzle,
    title: '插件扩展系统',
  },
  {
    description:
      '基于 Swoole 协程和 PHP 8.1 高性能架构，支持高并发处理，响应速度快，系统稳定可靠，满足大规模业务场景需求。',
    icon: Zap,
    title: '高性能架构',
  },
  {
    description:
      '完整的钱包账户系统，提供余额管理、账变记录、充值、提现、冻结等全流程资金管理功能，满足各类金融业务需求。',
    icon: Wallet,
    title: '钱包账户系统',
  },
  {
    description:
      'RESTful API 设计，统一签名接入，快速适配 App、小程序、Web 等全平台场景。前端采用 Vue 3 现代化技术栈开发。',
    icon: Smartphone,
    title: '全平台接入',
  },
];

export default () => {
  return (
    <Center gap={32}>
      <Features items={items} />
    </Center>
  );
};