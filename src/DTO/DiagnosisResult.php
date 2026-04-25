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

    // Plant disease specific fields
    private ?string $plantName = null;
    private ?string $diseaseName = null;
    private float $confidenceScore = 0.0;
    private bool $healthy = false;
    private bool $success = true;
    private ?string $errorMessage = null;

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

        // Plant disease specific fields
        $r->plantName = $data['plant_name'] ?? null;
        $r->diseaseName = $data['disease_name'] ?? null;
        
        // Handle confidence as both string and numeric
        if (isset($data['confidence'])) {
            if (is_numeric($data['confidence'])) {
                $r->confidenceScore = (float)$data['confidence'];
            } else {
                // Map string confidence to numeric
                $r->confidenceScore = match(strtoupper($data['confidence'])) {
                    'HIGH' => 90.0,
                    'MEDIUM' => 70.0,
                    'LOW' => 50.0,
                    default => 0.0
                };
            }
        }
        
        $r->healthy = (bool)($data['is_healthy'] ?? false);
        $r->success = true;

        return $r;
    }

    public static function error(string $message): self
    {
        $r = new self();
        $r->success = false;
        $r->errorMessage = $message;
        $r->condition = 'Erreur de diagnostic';
        $r->confidence = 'LOW';
        return $r;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getPlantName(): string
    {
        return $this->plantName ?? $this->condition;
    }

    public function getDiseaseName(): ?string
    {
        return $this->diseaseName ?? ($this->healthy ? null : $this->condition);
    }

    public function getConfidence(): float
    {
        return $this->confidenceScore;
    }

    public function isHealthy(): bool
    {
        return $this->healthy;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
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
