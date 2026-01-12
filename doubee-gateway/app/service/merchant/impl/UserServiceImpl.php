<?php

declare(strict_types=1);

namespace app\service\merchant\impl;

use app\constant\Cache as CacheConstant;
use app\exception\JsonException;
use app\kernel\route\annotation\Inject;
use app\model\Merchant;
use app\model\MerchantApplication;
use app\service\admin\MailerService;
use app\service\merchant\UserService;
use app\utils\AesUtils;
use app\utils\DateUtils;
use app\utils\StringUtils;
use app\utils\ValidatorUtils;
use Firebase\JWT\JWT;
use think\db\Query;
use think\facade\Cache;
use think\facade\Request;

class UserServiceImpl implements UserService
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected MailerService $mailerService;

    public function login(string $username, string $password): string
    {
        $field = match (true) {
            ValidatorUtils::isValidPhoneNumber($username) => "phone",
            ValidatorUtils::isValidEmail($username) => "email",
            default => throw new JsonException("用户名不正确"),
        };

        $merchant = Merchant::where($field, $username)->find();
        if (!$merchant) {
            throw new JsonException("用户不存在");
        }

        if (StringUtils::generatePassword($password, $merchant->salting) !== $merchant->password) {
            throw new JsonException("密码错误");
        }

        if ($merchant->status != 1) {
            throw new JsonException("状态异常，如有疑问请联系客服。");
        }

        return $this->setLoginSuccess($merchant);
    }

    public function verificationCodeLogin(string $username, string $code): string
    {
        if (!ValidatorUtils::isValidEmail($username) && !ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException('咦，格式好像不对？请用手机号码或邮箱地址试试~');
        }

        $cacheKey = sprintf(CacheConstant::LOGIN_VERIFICATION_CODE->value, $username);
        $cacheCode = Cache::store("redis")->get($cacheKey);
        if (!$cacheCode) {
            throw new JsonException("验证码已失效，请重新获取。");
        }

        if ($cacheCode !== $code) {
            throw new JsonException("验证码错误");
        }

        $field = ValidatorUtils::isValidPhoneNumber($username) ? 'phone' : 'email';
        $merchant = Merchant::where($field, $username)->find();
        if (!$merchant) {
            throw new JsonException("用户不存在");
        }

        if ($merchant->status != 1) {
            throw new JsonException("状态异常，如有疑问请联系客服。");
        }

        Cache::store("redis")->delete($cacheKey);

        return $this->setLoginSuccess($merchant);
    }

    public function sendLoginVerificationCode(string $username): void
    {
        if (!ValidatorUtils::isValidEmail($username) && !ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException('咦，格式好像不对？请用手机号码或邮箱地址试试~');
        }

        $cacheKey = sprintf(CacheConstant::LOGIN_VERIFICATION_CODE->value, $username);
        if (Cache::store("redis")->has($cacheKey)) {
            throw new JsonException("验证码发送过于频繁，请稍后再试。");
        }

        $field = ValidatorUtils::isValidPhoneNumber($username) ? 'phone' : 'email';
        $merchant = Merchant::where($field, $username)->find();
        if (!$merchant) {
            throw new JsonException("该手机号或邮箱尚未注册，请先注册账号");
        }

        $code = StringUtils::generateVerificationCode();
        if (ValidatorUtils::isValidEmail($username)) {
            $this->mailerService->sendEmail($username, "登录验证码", "您的本次登录验证码为：{$code}，有效期 3 分钟。请勿泄露给他人。");
            Cache::store("redis")->set($cacheKey, $code, 180);
        }

        if (ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException("短信验证码功能暂未开放，敬请期待。");
        }
    }

    public function setLoginSuccess(Merchant $merchant): string
    {
        $loginTime = DateUtils::current();

        $payload = [
            'expire' => time() + (int)env("MERCHANT_JWT_EXPIRE"),
            'loginTime' => $loginTime,
            'userId' => AesUtils::encrypt($merchant->id),
        ];

        return JWT::encode($payload, env("MERCHANT_JWT_KEY"), "HS256");
    }

    public function emailRegister(string $email, string $password, string $code): string
    {
        $merchant = Merchant::where('email', $email)->find();
        if ($merchant) {
            throw new JsonException("该邮箱已被注册，请使用其他邮箱。");
        }

        $cacheKey = sprintf(CacheConstant::REGISTER_VERIFICATION_CODE->value, $email);
        $cacheCode = Cache::store("redis")->get($cacheKey);
        if (!$cacheCode) {
            throw new JsonException("验证码已失效，请重新获取。");
        }

        if ($cacheCode !== $code) {
            throw new JsonException("验证码错误");
        }

        $model = new Merchant();
        $model->merchant_no = StringUtils::generateUniqueMerchantNumber();
        $model->email = $email;
        $model->salting = StringUtils::generateRandStr();
        $model->password = StringUtils::generatePassword($password, $model->salting);
        $model->creation_time = DateUtils::current();
        $model->status = 1;
        $model->save();

        $this->createWithApplication($model);

        Cache::store("redis")->delete($cacheKey);

        return $this->setLoginSuccess($model);
    }

    public function phoneRegister(string $phone, string $password, string $code): string
    {

    }

    public function sendRegisterCode(string $username): void
    {
        if (!ValidatorUtils::isValidEmail($username) && !ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException('咦，格式好像不对？请用手机号码或邮箱地址试试~');
        }

        $cacheKey = sprintf(CacheConstant::REGISTER_VERIFICATION_CODE->value, $username);
        if (Cache::store("redis")->has($cacheKey)) {
            throw new JsonException("验证码发送过于频繁，请稍后再试。");
        }

        $field = ValidatorUtils::isValidPhoneNumber($username) ? 'phone' : 'email';
        $merchant = Merchant::where($field, $username)->find();
        if ($merchant) {
            throw new JsonException("该手机号或邮箱已注册，请使用其他账号或尝试登录");
        }

        $code = StringUtils::generateVerificationCode();
        if (ValidatorUtils::isValidEmail($username)) {
            $this->mailerService->sendEmail($username, "注册验证码", "您正在进行账户注册操作，验证码为：{$code}。该验证码有效期为 3 分钟，请勿泄露。如非本人操作，请忽略本邮件。");
            Cache::store("redis")->set($cacheKey, $code, 180);
        }

        if (ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException("短信验证码功能暂未开放，敬请期待。");
        }
    }

    public function resetPassword(string $username, string $password, string $code): void
    {
        if (!ValidatorUtils::isValidEmail($username) && !ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException('咦，格式好像不对？请用手机号码或邮箱地址试试~');
        }

        $field = ValidatorUtils::isValidPhoneNumber($username) ? 'phone' : 'email';
        $merchant = Merchant::where($field, $username)->find();
        if (!$merchant) {
            throw new JsonException("该手机号或邮箱尚未注册，请先注册账号");
        }

        $cacheKey = sprintf(CacheConstant::RESET_PASSWORD_VERIFICATION_CODE->value, $username);
        $cacheCode = Cache::store("redis")->get($cacheKey);
        if (!$cacheCode) {
            throw new JsonException("验证码已失效，请重新获取。");
        }

        if ($cacheCode !== $code) {
            throw new JsonException("验证码错误");
        }

        $merchant->salting = StringUtils::generateRandStr();
        $merchant->password = StringUtils::generatePassword($password, $merchant->salting);
        $merchant->save();

        Cache::store("redis")->delete($cacheKey);
    }

    public function sendResetPasswordCode(string $username): void
    {
        if (!ValidatorUtils::isValidEmail($username) && !ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException('咦，格式好像不对？请用手机号码或邮箱地址试试~');
        }

        $field = ValidatorUtils::isValidPhoneNumber($username) ? 'phone' : 'email';
        $merchant = Merchant::where($field, $username)->find();
        if (!$merchant) {
            throw new JsonException("该手机号或邮箱尚未注册，请先注册账号");
        }

        $cacheKey = sprintf(CacheConstant::RESET_PASSWORD_VERIFICATION_CODE->value, $username);
        if (Cache::store("redis")->has($cacheKey)) {
            throw new JsonException("验证码发送过于频繁，请稍后再试。");
        }

        $code = StringUtils::generateVerificationCode();
        if (ValidatorUtils::isValidEmail($username)) {
            $this->mailerService->sendEmail($username, "找回密码验证码", "您正在进行找回密码操作，验证码为：{$code}，有效期为 3 分钟。请勿泄露给他人。如非本人操作，请忽略本邮件。");
            Cache::store("redis")->set($cacheKey, $code, 180);
        }

        if (ValidatorUtils::isValidPhoneNumber($username)) {
            throw new JsonException("短信验证码功能暂未开放，敬请期待。");
        }
    }

    public function createWithApplication(Merchant $merchant): bool
    {
        $application = new MerchantApplication();
        $application->merchant_id = $merchant->id;
        $applicationNo = StringUtils::generateUniqueAppNumber();
        $application->application_no = $applicationNo;
        $application->name = "默认应用{$applicationNo}";
        $application->secret = StringUtils::generateRandStr();
        $application->creation_time = DateUtils::current();
        $application->status = 1;

        return $application->save();
    }

    public function findByUserPermissions(int $userId): array
    {
        $user = Merchant::with(['merchantGroup' => function (Query $query) {
            $query->with(['permissions' => function (Query $query) {
                $query->where("status", 1)->order("sort", "desc");
            }]);
        }])->find($userId);

        if (!$user) {
            throw new JsonException("用户不存在");
        }

        $menus = [];
        $permissions = [];

        $group = $user->merchantGroup;
        if ($group && $group->permissions) {
            foreach ($group->permissions as $permission) {
                if ($permission->type == 2) {
                    $permissions[] = [
                        'id' => $permission->id,
                        'path' => $permission->path,
                    ];
                }

                if ($permission->type == 1 || $permission->type == 0) {
                    $menus[] = [
                        'menuId' => $permission->id,
                        'parentId' => $permission->parent_id,
                        'icon' => $permission->icon,
                        'title' => $permission->name,
                        'path' => $permission->path,
                        'component' => $permission->component,
                        'hide' => $permission->hide,
                        'metadata' => $permission->metadata,
                    ];
                }
            }
        }

        return ['permissions' => $permissions, 'menus' => $menus];
    }

    public function logout(int $userId): void
    {

    }
}