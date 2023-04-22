<?php

namespace Bo\Base\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable, $email = null)
    {
        $email = $email ?? $notifiable->getEmailForPasswordReset();

        return (new MailMessage())
            ->subject(trans('bo::base.password_reset.subject'))
            ->greeting(trans('bo::base.password_reset.greeting'))
            ->line([
                trans('bo::base.password_reset.line_1'),
                trans('bo::base.password_reset.line_2'),
            ])
            ->action(trans('bo::base.password_reset.button'), route('bo.auth.password.reset.token', $this->token).'?email='.urlencode($email))
            ->line(trans('bo::base.password_reset.notice'));
    }
}
