<?php

namespace ScreenshotGuard;

/**
 * Represents a secret finding
 */
class Finding
{
    public string $file;
    public int $line;
    public string $type;
    public string $severity;
    public string $match;
    public bool $fromOcr;
    public string $provider;

    /**
     * Create a Finding from array data
     *
     * @param array $data Finding data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $finding = new self();
        $finding->file = $data['file_path'] ?? '';
        $finding->line = $data['line_number'] ?? 0;
        $finding->type = $data['pattern_name'] ?? '';
        $finding->severity = $data['severity'] ?? 'unknown';
        $finding->match = $data['match'] ?? '';
        $finding->fromOcr = $data['from_ocr'] ?? false;
        $finding->provider = $data['provider'] ?? 'generic';

        return $finding;
    }

    /**
     * Check if this is a critical finding
     *
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Check if this is a high severity finding
     *
     * @return bool
     */
    public function isHigh(): bool
    {
        return $this->severity === 'high';
    }

    /**
     * Get redacted match (hides most of the secret)
     *
     * @return string
     */
    public function redactedMatch(): string
    {
        if (strlen($this->match) <= 8) {
            return str_repeat('*', strlen($this->match));
        }

        return substr($this->match, 0, 4) . str_repeat('*', 8) . '…';
    }
}
