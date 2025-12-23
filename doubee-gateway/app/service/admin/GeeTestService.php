<?php

declare(strict_types=1);

namespace app\service\admin;

interface GeeTestService
{
    const API_SERVER = "https://gcaptcha4.geetest.com";

    public function getBehavioralVerificationCode(string $lotNumber, string $captchaOutput, string $passToken, string $genTime): array;
}