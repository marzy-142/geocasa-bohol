<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;

class CustomEmailVerificationNotification extends VerifyEmail
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $userName = $notifiable->name ?? 'Valued User';

        return (new MailMessage)
            ->subject('Complete Your GeoCasa Bohol Registration')
            ->greeting("Welcome to GeoCasa Bohol, {$userName}!")
            ->line('Thank you for choosing us as your real estate partner. We\'re excited to help you discover the perfect property in beautiful Bohol.')
            ->line('To complete your registration and access our exclusive property listings, please verify your email address:')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link expires in 24 hours for your security.')
            ->line('If you didn\'t create an account with GeoCasa Bohol, please ignore this email.')
            ->salutation('Best regards,')
            ->line('The GeoCasa Bohol Team')
            ->line('Your trusted real estate partner in Bohol')
            ->line('📧 support@geocasa-bohol.com | 🌐 www.geocasa-bohol.com');
    }

    /**
     * Send the notification using our custom Mailable.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
