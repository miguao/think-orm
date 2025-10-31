<?php

declare(strict_types=1);

namespace app\service\admin\impl;

use app\exception\JsonException;
use app\kernel\route\annotation\Inject;
use app\service\admin\ConfigService;
use app\service\admin\MailerService;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailerServiceImpl implements MailerService
{
    #[Inject]
    protected ConfigService $configService;

    public function instance(): PHPMailer
    {
        $config = $this->configService->getConfig("mailer_config")['data'];
        $mailer = new PHPMailer(true);
        $mailer->SMTPDebug = SMTP::DEBUG_OFF;
        $mailer->isSMTP();
        $mailer->Host = $config['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['username'];
        $mailer->Password = $config['password'];
        $mailer->SMTPSecure = $config['secure'];
        $mailer->Port = $config['port'];

        $mailer->setFrom($config['sender_email'], $config['sender_name']);
        $mailer->addReplyTo($config['sender_email'], $config['sender_name']);

        return $mailer;
    }

    public function sendEmail(string $email, string $subject, string $body): bool
    {
        $config = $this->configService->getConfig("mailer_config")['data'];
        try {
            $mailer = $this->instance();
            $mailer->addAddress($email);
            $mailer->isHTML(true);
            $mailer->Subject = "【{$config['sender_name']}】{$subject}";
            $mailer->Body = $body;
            $mailer->AltBody = $body;
            if ($mailer->send()) {
                return true;
            }
        } catch (PHPMailerException $exception) {
            throw new JsonException($exception->getMessage());
        }

        return false;
    }
}