<?php

declare(strict_types=1);

namespace Inane\File\Tests;

use Inane\File\Mimer;
use PHPUnit\Framework\TestCase;

use function array_values;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class MimerTest extends TestCase {
    private string $tempDir;

    protected function setUp(): void {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/inane-mimer-tests-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    public function testFetchMimeTypeFileFiltersComments(): void {
        $mimeFile = $this->tempDir . '/mime.types';
        file_put_contents($mimeFile, "# comment\ntext/plain txt text\nimage/png png\n");

        $result = TestableMimer::fetch($mimeFile);

        $this->assertSame(['text/plain txt text', 'image/png png'], array_values($result));
    }

    public function testParseMimeEntryReturnsTypeAndExtensions(): void {
        $parsed = TestableMimer::parse('application/json json map');

        $this->assertSame('application/json', $parsed['type']);
        $this->assertSame(['json', 'map'], $parsed['exts']);
    }

    public function testUpdateMimeTypesMergesAndSortsExtensions(): void {
        $mimeData = [];

        TestableMimer::updateMimeTypes('text/plain', ['txt', 'text'], $mimeData);
        TestableMimer::updateMimeTypes('text/plain', ['log', 'txt'], $mimeData);

        $this->assertSame(['log', 'text', 'txt'], $mimeData['text/plain']);
    }

    public function testUpdateExtensionsBuildsLookupByExtension(): void {
        $extData = [];

        TestableMimer::updateExtensions('text/plain', ['txt', 'text'], $extData);
        TestableMimer::updateExtensions('application/json', ['txt', 'json'], $extData);

        $this->assertSame(['text/plain', 'application/json'], $extData['txt']);
        $this->assertSame(['text/plain'], $extData['text']);
        $this->assertSame(['application/json'], $extData['json']);
    }

    private function removeDirectory(string $directory): void {
        if (!is_dir($directory)) return;

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $directory . '/' . $item;
            if (is_dir($path)) $this->removeDirectory($path);
            else unlink($path);
        }

        rmdir($directory);
    }
}

final class TestableMimer extends Mimer {
    public static function fetch(string $file): ?array {
        return parent::fetchMimeTypeFile($file);
    }

    public static function parse(string $line): ?array {
        return parent::parseMimeEntry($line);
    }

    public static function updateMimeTypes(string $type, array $exts, ?array &$data = null): void {
        parent::updateMimeTypes($type, $exts, $data);
    }

    public static function updateExtensions(string $type, array $exts, ?array &$data = null): void {
        parent::updateExtensions($type, $exts, $data);
    }
}
