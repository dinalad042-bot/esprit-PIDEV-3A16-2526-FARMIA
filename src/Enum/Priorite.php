<?php

namespace App\Enum;

enum Priorite: string
{
    case HAUTE   = 'HAUTE';
    case MOYENNE = 'MOYENNE';
    case BASSE   = 'BASSE';

    public function label(): string
    {
        return match($this) {
            Priorite::HAUTE   => 'Haute',
            Priorite::MOYENNE => 'Moyenne',
            Priorite::BASSE   => 'Basse',
        };
    }

    public function badgeStyle(): string
    {
        return match($this) {
            Priorite::HAUTE   => 'background:#e74c3c;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;',
            Priorite::MOYENNE => 'background:#f39c12;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;',
            Priorite::BASSE   => 'background:#27ae60;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;',
        };
    }

    public function icon(): string
    {
        return match($this) {
            Priorite::HAUTE   => '🔴',
            Priorite::MOYENNE => '🟡',
            Priorite::BASSE   => '🟢',
        };
    }
}