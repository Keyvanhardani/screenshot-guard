<?php

namespace ScreenshotGuard;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Screenshot Guard PHP Client
 *
 * Scans files and directories for exposed secrets using pattern matching and OCR.
 */
class Client
{
    private HttpClient $http;
    private string $pythonBinary;
    private bool $ocrEnabled;
    private string $backend;

    /**
     * Create a new Screenshot Guard client
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->pythonBinary = $config['python'] ?? 'python';
        $this->ocrEnabled = $config['ocr'] ?? true;
        $this->backend = $config['backend'] ?? 'llamacpp';
        $this->http = new HttpClient($config['http'] ?? []);
    }

    /**
     * Scan a path for secrets
     *
     * @param string $path Path to scan
     * @param array $options Scan options
     * @return array Findings
     */
    public function scan(string $path, array $options = []): array
    {
        $command = [
            $this->pythonBinary,
            '-m', 'screenshot_guard',
            'scan', $path,
            '--format', 'json'
        ];

        if (isset($options['severity'])) {
            $command[] = '--severity';
            $command[] = $options['severity'];
        }

        if (isset($options['ocr']) && !$options['ocr']) {
            $command[] = '--no-ocr';
        } elseif ($this->ocrEnabled) {
            $command[] = '--backend';
            $command[] = $options['backend'] ?? $this->backend;
        }

        $output = $this->execute($command);

        return $this->parseFindings($output);
    }

    /**
     * List available detection patterns
     *
     * @return array Pattern information
     */
    public function patterns(): array
    {
        $command = [
            $this->pythonBinary,
            '-m', 'screenshot_guard',
            'patterns'
        ];

        $output = $this->execute($command);

        return $this->parsePatterns($output);
    }

    /**
     * Execute a command and return output
     *
     * @param array $command Command to execute
     * @return string Output
     */
    private function execute(array $command): string
    {
        $process = proc_open(
            implode(' ', array_map('escapeshellarg', $command)),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to execute screenshot-guard');
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0 && empty($output)) {
            throw new \RuntimeException("screenshot-guard failed: $errors");
        }

        return $output;
    }

    /**
     * Parse JSON findings output
     *
     * @param string $output Raw output
     * @return array Parsed findings
     */
    private function parseFindings(string $output): array
    {
        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $data['findings'] ?? [];
    }

    /**
     * Parse patterns output
     *
     * @param string $output Raw output
     * @return array Parsed patterns
     */
    private function parsePatterns(string $output): array
    {
        // Parse the text output from patterns command
        $patterns = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if (preg_match('/(\w+)\s+(\d+)/', $line, $matches)) {
                $patterns[$matches[1]] = (int)$matches[2];
            }
        }

        return $patterns;
    }
}
