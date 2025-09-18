<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    /**
     * Get all status values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status label for display
     */
    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed',
        };
    }

    /**
     * Get status label for display (with "Inquiry" suffix for NEW status)
     */
    public function getLabel(): string
    {
        return match($this) {
            self::NEW => 'New Inquiry',
            self::CONTACTED => 'Contacted',
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed',
        };
    }

    /**
     * Get valid next statuses for transitions
     */
    public function validTransitions(): array
    {
        return match($this) {
            self::NEW => [self::CONTACTED, self::CLOSED],
            self::CONTACTED => [self::SCHEDULED, self::CLOSED],
            self::SCHEDULED => [self::COMPLETED, self::CLOSED],
            self::COMPLETED => [self::CLOSED],
            self::CLOSED => [], // Terminal state
        };
    }

    /**
     * Check if transition to another status is valid
     */
    public function canTransitionTo(InquiryStatus $newStatus): bool
    {
        return in_array($newStatus, $this->validTransitions());
    }

    /**
     * Get CSS class for status display
     */
    public function getCssClass(): string
    {
        return match($this) {
            self::NEW => 'bg-blue-100 text-blue-800',
            self::CONTACTED => 'bg-yellow-100 text-yellow-800',
            self::SCHEDULED => 'bg-purple-100 text-purple-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CLOSED => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get CSS class for status display (alias for backward compatibility)
     */
    public function cssClass(): string
    {
        return $this->getCssClass();
    }

    /**
     * Check if status requires broker response
     */
    public function requiresBrokerResponse(): bool
    {
        return $this === self::NEW;
    }

    /**
     * Get statuses that require broker response (static method)
     */
    public static function getStatusesRequiringBrokerResponse(): array
    {
        return [self::NEW];
    }

    /**
     * Check if status is terminal (no further actions needed)
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CLOSED]);
    }

    /**
     * Get terminal statuses
     */
    public static function getTerminalStatuses(): array
    {
        return [self::COMPLETED, self::CLOSED];
    }

    /**
     * Get terminal statuses (alias)
     */
    public static function terminal(): array
    {
        return self::getTerminalStatuses();
    }

    /**
     * Get all statuses
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get active statuses (non-terminal)
     */
    public static function active(): array
    {
        return [self::NEW, self::CONTACTED, self::SCHEDULED];
    }

    /**
     * Get status options for select dropdown
     */
    public static function forSelect(): array
    {
        return [
            self::NEW->value => 'New Inquiry',
            self::CONTACTED->value => 'Contacted',
            self::SCHEDULED->value => 'Scheduled',
            self::COMPLETED->value => 'Completed',
            self::CLOSED->value => 'Closed',
        ];
    }

    /**
     * Get active status options for select dropdown (excluding terminal states)
     */
    public static function forSelectActive(): array
    {
        return [
            self::NEW->value => 'New Inquiry',
            self::CONTACTED->value => 'Contacted',
            self::SCHEDULED->value => 'Scheduled',
        ];
    }
}