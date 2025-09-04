<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Instructor = 'instructor';
    case Student = 'student';

    /**
     * Return all values for seeding, validation, etc.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return the display label (for UI, optional).
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Instructor => 'Instructor',
            self::Student => 'Student',
        };
    }
}
