<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email
        ]);

        return (new MailMessage)
            ->subject('Permintaan Reset Password DanzzCoffee')
            ->greeting('Halo ' . $notifiable->fullname . '! 👋')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->line('Untuk mereset password, silakan klik tombol di bawah:')
            ->action('Reset Password', $resetUrl)
            ->line('**Link ini akan berlaku selama 1 jam.**')
            ->line('')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.')
            ->line('')
            ->line('---')
            ->line('Informasi Keamanan:')
            ->line('- Jangan bagikan link reset password ini kepada siapa pun')
            ->line('- Jangan pernah beri tahu password Anda kepada siapa pun')
            ->line('- DanzzCoffee tidak akan pernah meminta password melalui email')
            ->line('- Jika Anda merasa akun Anda kompromis, segera hubungi support')
            ->line('')
            ->line('Pertanyaan? Hubungi support@danzzcoffee.test')
            ->salutation('Salam hangat,')
            ->line('Tim DanzzCoffee');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
