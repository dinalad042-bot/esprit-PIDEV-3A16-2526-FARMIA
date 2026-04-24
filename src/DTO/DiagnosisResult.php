<?php

namespace App\DTO;

class DiagnosisResult
{
    public string $condition   = '';
    public string $confidence  = 'LOW';
    public array $symptoms     = [];
    public string $treatment   = '';
    public string $prevention  = '';
    public string $urgency     = '';
    public bool   $needsExpert = false;
    public string $rawResponse = '';

    public static function fromArray(array $data): self
    {
        $r = new self();
        $r->condition   = $data['condition']        ?? 'Inconnu';
        $r->confidence  = $data['confidence']       ?? 'LOW';
        $r->symptoms     = is_array($data['symptoms'] ?? []) ? $data['symptoms'] : [$data['symptoms']];
        $r->treatment   = $data['treatment']        ?? '';
        $r->prevention  = $data['prevention']       ?? '';
        $r->urgency     = $data['urgency']          ?? '';
        $r->needsExpert = (bool)($data['needsExpertConsult'] ?? false);
        $r->rawResponse = $data['rawResponse']      ?? '';
        return $r;
    }

    public function confidenceBadgeStyle(): string
    {
        return match(strtoupper($this->confidence)) {
            'HIGH'   => 'background:#27ae60;color:#fff;padding:3px 10px;border-radius:12px;',
            'MEDIUM' => 'background:#f39c12;color:#fff;padding:3px 10px;border-radius:12px;',
            default  => 'background:#7f8c8d;color:#fff;padding:3px 10px;border-radius:12px;',
        };
    }
}