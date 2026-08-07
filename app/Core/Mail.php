<?php
namespace Neo\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    protected function configure()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? 'user@example.com';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? 'secret';
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 465;

            // Default Sender
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'hello@example.com',
                $_ENV['MAIL_FROM_NAME'] ?? 'Neo Framework'
            );
        } catch (Exception $e) {
            // Log error or handle silently? 
            // For now, allow instantiation even if config is bad, error will happen on send.
        }
    }

    public function to($address, $name = '')
    {
        $this->mailer->addAddress($address, $name);
        return $this;
    }

    public function subject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function body($html, $plain = '')
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $html;
        $this->mailer->AltBody = $plain ?: strip_tags($html);
        return $this;
    }

    public function send()
    {
        try {
            return $this->mailer->send();
        } catch (Exception $e) {
            // Throwing Neo exception or return false?
            // Let's return false and log/store error
            error_log("Mail Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function getError()
    {
        return $this->mailer->ErrorInfo;
    }
}
