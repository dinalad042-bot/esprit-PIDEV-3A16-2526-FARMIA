<?php

namespace App\DTO;

class DiagnosisResult
{
    public string $condition     = '';
    public string $confidence    = 'LOW';
    public array  $symptoms       = [];
    public string $treatment     = '';
    public string $prevention    = '';
    public string $urgency       = '';
    public bool   $needsExpert   = false;
    public string $rawResponse   = '';
    public bool   $success       = true;
    public string $errorMessage  = '';
    public float  $confidencePct = 0.0;
    public string $plantName     = '';
    public string $diseaseName   = '';
    public bool   $isHealthy     = false;
    public string $subjectType   = '';  // 'plant' or 'animal'

    public static function fromArray(array $data): self
    {
        $r = new self();
        $r->condition     = $data['condition']       ?? 'Inconnu';
        $r->confidence    = $data['confidence']      ?? 'LOW';
        $r->subjectType   = $data['subject_type']    ?? '';
        $r->symptoms      = is_array($data['symptoms'] ?? []) ? $data['symptoms'] : [$data['symptoms']];
        // Handle treatment and prevention as either string or array
        $r->treatment     = is_array($data['treatment'] ?? '') ? implode("\n", $data['treatment']) : ($data['treatment'] ?? '');
        $r->prevention    = is_array($data['prevention'] ?? '') ? implode("\n", $data['prevention']) : ($data['prevention'] ?? '');
        $r->urgency       = $data['urgency']         ?? '';
        $r->needsExpert   = (bool)($data['needsExpertConsult'] ?? false);
        $r->rawResponse   = $data['rawResponse']     ?? '';
        $r->success       = (bool)($data['success'] ?? true);
        $r->errorMessage  = $data['errorMessage']  ?? '';
        
        // Support confidence as both string and float
        if (isset($data['confidence']) && is_numeric($data['confidence'])) {
            $r->confidencePct = (float) $data['confidence'];
        } elseif (isset($data['confidence_percent']) && is_numeric($data['confidence_percent'])) {
            $r->confidencePct = (float) $data['confidence_percent'];
        }
        
        // Support alternate field names from tests
        $r->plantName   = $data['plant_name']        ?? $data['condition'] ?? '';
        $r->diseaseName = $data['disease_name']      ?? $data['condition'] ?? '';
        $r->isHealthy   = (bool)($data['is_healthy'] ?? false);
        
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

    // Methods expected by tests

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    public function getDiseaseName(): ?string
    {
        return $this->diseaseName !== '' ? $this->diseaseName : null;
    }

    public function getConfidence(): float
    {
        return $this->confidencePct;
    }

    public function isHealthy(): bool
    {
        return $this->isHealthy;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}