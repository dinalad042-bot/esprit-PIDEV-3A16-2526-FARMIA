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

    public function badgeStyle(): string
    {
        return match($this) {
            self::HAUTE => 'background:#ffebee; color:#c62828; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;',
            self::MOYENNE => 'background:#fff8e1; color:#f57f17; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;',
            self::BASSE => 'background:#e8f5e9; color:#2e7d32; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::HAUTE => '🔴',
            self::MOYENNE => '🟡',
            self::BASSE => '🟢',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
