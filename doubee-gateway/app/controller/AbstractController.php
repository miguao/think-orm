<?php

declare (strict_types=1);

namespace app\controller;

use app\constant\StatusCode;
use app\kernel\route\annotation\Inject;
use think\Request;
use think\response\Json;
use think\Validate;

abstract class AbstractController
{
    #[Inject]
    protected Request $request;

    /**
     * 批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 中间件注入(通用模式)
     * @var array
     */
    protected array $middleware = [];

    /**
     * 验证器
     * @param array $data
     * @param string|array $validate
     * @param array $message
     * @param bool $batch
     * @return bool|array|string
     */
    protected function validator(array $data, string|array $validate, array $message = [], bool $batch = false): bool|array|string
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : app()->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 生成JSON对象
     * @param StatusCode|null $code
     * @param string|null $message
     * @param array|null $data
     * @param array $ext
     * @return Json
     */
    public function json(StatusCode $code = null, string $message = null, ?array $data = null, array $ext = []): Json
    {
        $code ??= StatusCode::SUCCESS;
        $message = $message ?? $code->getMessage();
        $json['code'] = $code->value;
        $json['message'] = $message;
        $data ? $json['data'] = $data : null;
        $ext ? $json['ext'] = $ext : null;

        return json($json);
    }
}