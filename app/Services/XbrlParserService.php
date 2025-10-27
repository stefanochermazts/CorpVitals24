<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\XbrlParseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class XbrlParserService
{
    public function __construct(
        private string $arellePath = '',
        private int $timeout = 300,
        private string $mode = 'cli',
    ) {
        $this->arellePath = (string) config('xbrl.arelle_path');
        $this->timeout = (int) config('xbrl.parse_timeout');
        $this->mode = (string) config('xbrl.mode', 'cli');
    }

    /**
     * Parse XBRL/iXBRL file via Arelle and return normalized structure.
     * @return array{facts:array,contexts:array,units:array,metadata:array}
     */
    public function parse(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new XbrlParseException("File not found: {$filePath}");
        }

        // Ensure local tmp dir inside storage exists when using docker
        $hash = md5($filePath . microtime(true));
        $hostOutputPath = storage_path('tmp/arelle-' . $hash . '.json');
        if (!is_dir(dirname($hostOutputPath))) {
            @mkdir(dirname($hostOutputPath), 0775, true);
        }

        $containerFilePath = $filePath;
        $containerOutputPath = $hostOutputPath;
        if ($this->mode === 'docker') {
            $containerFilePath = $this->toContainerPath($filePath);
            $containerOutputPath = '/storage/tmp/arelle-' . $hash . '.json';
        }

        $cmd = $this->buildCommand($containerFilePath, $containerOutputPath);

        $process = new Process($cmd, null, null, null, $this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new XbrlParseException('Arelle failed: ' . $process->getErrorOutput());
        }

        $outputPathToRead = $this->mode === 'docker' ? $hostOutputPath : $containerOutputPath;
        if (!is_file($outputPathToRead)) {
            throw new XbrlParseException('Arelle output not found');
        }

        $json = file_get_contents($outputPathToRead);
        @unlink($outputPathToRead);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new XbrlParseException('Invalid JSON from Arelle');
        }

        return [
            'facts' => $data['facts'] ?? [],
            'contexts' => $data['contexts'] ?? [],
            'units' => $data['units'] ?? [],
            'metadata' => [
                'arelle_version' => $data['arelleVersion'] ?? null,
                'file' => basename($filePath),
            ],
        ];
    }

    private function buildCommand(string $filePath, string $outputPath): array
    {
        $base = [
            '--file', $filePath,
            '--facts', $outputPath,
            '--factListCols', 'Label contextRef unitRef Dec value',
        ];

        if ($this->mode === 'docker') {
            return ['docker', 'exec', 'arelle', 'python', '/arelle/arelleCmdLine.py', ...$base];
        }

        return ['python3', $this->arellePath, ...$base];
    }

    private function toContainerPath(string $hostPath): string
    {
        $hostStorage = base_path('storage');
        if (str_starts_with($hostPath, $hostStorage)) {
            return '/storage' . substr($hostPath, strlen($hostStorage));
        }
        return $hostPath;
    }
}


