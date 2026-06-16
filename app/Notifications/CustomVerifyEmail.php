<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Akun DanzzCoffee Anda')
            ->greeting('Halo ' . $notifiable->fullname . '! 👋')
            ->line('Terima kasih telah mendaftar di DanzzCoffee! Kami senang Anda bergabung dengan komunitas kami.')
            ->line('Untuk menyelesaikan pendaftaran akun Anda, silakan verifikasi email ini dengan mengklik tombol di bawah:')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('**Link ini akan berlaku selama 24 jam.**')
            ->line('')
            ->line('Jika Anda tidak membuat akun ini, abaikan email ini.')
            ->line('')
            ->line('---')
            ->line('Informasi Keamanan:')
            ->line('- Jangan bagikan link verifikasi ini kepada siapa pun')
            ->line('- DanzzCoffee tidak akan pernah meminta verifikasi melalui metode lain')
            ->line('- Jika Anda merasa akun Anda kompromis, ubah password Anda segera')
            ->line('')
            ->line('Pertanyaan? Hubungi support@danzzcoffee.test')
            ->salutation('Salam hangat,')
            ->line('Tim DanzzCoffee');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
