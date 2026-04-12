<?php

namespace App\Enum;

enum Priorite: string
{
    case HAUTE = 'HAUTE';
    case MOYENNE = 'MOYENNE';
    case BASSE = 'BASSE';

    public function getLabel(): string
    {
        return match($this) {
            self::HAUTE => 'Haute',
            self::MOYENNE => 'Moyenne',
            self::BASSE => 'Basse',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::HAUTE => 'danger',
            self::MOYENNE => 'warning',
            self::BASSE => 'success',
        };
    }
}
