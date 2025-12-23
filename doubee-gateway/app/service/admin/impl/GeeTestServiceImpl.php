<?php

declare(strict_types=1);

namespace app\service\admin\impl;

use app\service\admin\GeeTestService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GeeTestServiceImpl implements GeeTestService
{
    public function getBehavioralVerificationCode(string $lotNumber, string $captchaOutput, string $passToken, string $genTime): array
    {
        $captchaId = "647f5ed2ed8acb4be36784e01556bb71";
        $captchaKey = "b09a7aafbfd83f73b35a9b530d0337bf";

        $params = [
            'lot_number' => $lotNumber,
            'captcha_output' => $captchaOutput,
            'pass_token' => $passToken,
            'gen_time' => $genTime,
            'sign_token' => hash_hmac('sha256', $lotNumber, $captchaKey),
        ];
        $client = new Client();
        $request = $client->post(GeeTestService::API_SERVER . "/validate?captcha_id=" . $captchaId, [
            RequestOptions::FORM_PARAMS => $params,
        ]);
        $responseBody = $request->getBody();
        return json_decode($responseBody->getContents(), true);
    }
}