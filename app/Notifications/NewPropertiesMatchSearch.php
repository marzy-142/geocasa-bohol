<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPropertiesMatchSearch extends Notification implements ShouldQueue
{
    use Queueable;

    protected $savedSearch;
    protected $properties;

    /**
     * Create a new notification instance.
     */
    public function __construct($savedSearch, $properties)
    {
        $this->savedSearch = $savedSearch;
        $this->properties = $properties;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->properties);
        $searchName = $this->savedSearch->name;
        
        $mail = (new MailMessage)
            ->subject("🏠 {$count} New " . ($count === 1 ? 'Property Matches' : 'Properties Match') . " Your Saved Search")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We found {$count} new " . ($count === 1 ? 'property' : 'properties') . " matching your saved search: **{$searchName}**");

        // Add property details
        foreach ($this->properties->take(5) as $property) {
            $price = number_format($property->total_price, 0);
            $mail->line("---")
                 ->line("**{$property->title}**")
                 ->line("📍 {$property->municipality}, {$property->province}")
                 ->line("💰 ₱{$price}")
                 ->line("📏 {$property->area} sqm")
                 ->action('View Property', route('public.properties.show', $property->id));
        }

        if ($count > 5) {
            $remaining = $count - 5;
            $mail->line("---")
                 ->line("And {$remaining} more " . ($remaining === 1 ? 'property' : 'properties') . "...");
        }

        $mail->line('---')
             ->action('View All Matching Properties', route('client.properties', $this->savedSearch->filters))
             ->line('You can manage your saved searches anytime.')
             ->action('Manage Saved Searches', route('client.searches.index'))
             ->line('To stop receiving these notifications, you can disable them in your saved searches settings.');

        return $mail;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'saved_search_match',
            'saved_search_id' => $this->savedSearch->id,
            'saved_search_name' => $this->savedSearch->name,
            'property_count' => count($this->properties),
            'properties' => $this->properties->take(3)->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price' => $property->total_price,
                    'location' => "{$property->municipality}, {$property->province}",
                ];
            }),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'saved_search_id' => $this->savedSearch->id,
            'saved_search_name' => $this->savedSearch->name,
            'property_count' => count($this->properties),
        ];
    }
}
