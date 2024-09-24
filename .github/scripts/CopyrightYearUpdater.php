<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter SMSRocket.
 *
 * (c) Pooya Parsa Dadashi <admin@codeigniter4.ir>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Class CopyrightYearUpdater
 *
 * This class updates the copyright years in specified files.
 */
class CopyrightYearUpdater
{
    private const SKIP_PHRASES = [
        'Lonnie Ezell',
        'British Columbia Institute of Technology',
    ];

    private string $currentYear;
    private array $files;

    public function __construct(string $currentYear, array $files)
    {
        $this->currentYear = $currentYear;
        $this->files       = $files;
    }

    public function run(): void
    {
        foreach ($this->files as $file) {
            $this->processFile($file);
        }
    }

    private function processFile(string $file): void
    {
        if (! file_exists($file)) {
            echo "File {$file} not found. Skipping...\n";

            return;
        }

        try {
            $content        = $this->readFileContent($file);
            $updatedContent = $this->updateCopyright($content);
            $this->writeFileContent($file, $updatedContent);

            echo "Updated {$file} with copyright year {$this->currentYear}\n";
        } catch (Exception $e) {
            echo "An error occurred while processing {$file}: " . $e->getMessage() . "\n";
        }
    }

    private function readFileContent(string $file): string
    {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new Exception("Failed to read file {$file}");
        }

        return $content;
    }

    private function writeFileContent(string $file, string $content): void
    {
        if (file_put_contents($file, $content) === false) {
            throw new Exception("Failed to write to file {$file}");
        }
    }

    private function updateCopyright(string $content): string
    {
        $skipPattern = implode('|', array_map('preg_quote', self::SKIP_PHRASES));
        $pattern     = "/(\\d{4})(?:-(\\d{4}))?(?![^<]*?({$skipPattern}))/";

        return preg_replace_callback($pattern, function (array $matches): string {
            if ($this->shouldSkipUpdate($matches[0])) {
                return $matches[0];
            }

            $startYear = $matches[1];
            $endYear   = $matches[2] ?? null;

            if ($endYear && $endYear < $this->currentYear) {
                return "{$startYear}-" . $this->currentYear;
            }
            if (! $endYear && $startYear < $this->currentYear) {
                return "{$startYear}-" . $this->currentYear;
            }

            return $matches[0];
        }, $content);
    }

    private function shouldSkipUpdate(string $line): bool
    {
        foreach (self::SKIP_PHRASES as $phrase) {
            if (str_contains($line, $phrase)) {
                return true;
            }
        }

        return false;
    }
}

// Get the current year
$currentYear = date('Y');

// Files to update
$files = ['mkdocs.yml', 'LICENSE'];

// Create an instance of the class and run the update
$updater = new CopyrightYearUpdater($currentYear, $files);
$updater->run();
