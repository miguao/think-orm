<?php

declare(strict_types=1);

namespace app\service\admin;

use PHPMailer\PHPMailer\PHPMailer;

interface MailerService
{
    /**
     * 获取实例
     * @return PHPMailer
     */
    public function instance(): PHPMailer;

    /**
     * 发送邮件
     * @param string $email
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function sendEmail(string $email, string $subject, string $body): bool;
}