<?php

declare(strict_types=1);

namespace Inane\File\Tests;

use Inane\File\File;
use Inane\File\Path;
use Inane\Stdlib\Options;
use Inane\Stdlib\String\Capitalisation;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class FileTest extends TestCase {
    private string $tempDir;

    protected function setUp(): void {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/inane-file-tests-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    public function testParsePathNormalisesAndTrimsDirectorySeparator(): void {
        $path = File::parsePath('alpha\\beta/');

        $this->assertStringEndsNotWith('/', $path);
        $this->assertStringContainsString('alpha/beta', $path);
    }

    public function testCombinePathsJoinsParentAndChild(): void {
        $combined = File::combinePaths('alpha/beta/', '/gamma');

        $this->assertSame('alpha/beta/gamma', $combined);
    }

    public function testGetExtensionSupportsCaseTransform(): void {
        $file = new File($this->tempDir . '/photo.JpG');

        $this->assertSame('JpG', $file->getExtension());
        $this->assertSame('JPG', $file->getExtension(Capitalisation::UPPERCASE));
        $this->assertSame('jpg', $file->getExtension(Capitalisation::lowercase));
    }

    public function testReadCachesUntilFreshReadIsRequested(): void {
        $file = new File($this->tempDir . '/notes.txt');
        $file->write('first');

        $this->assertSame('first', $file->read());

        file_put_contents($file->getPathname(), 'second');

        $this->assertSame('first', $file->read());
        $this->assertSame('second', $file->read(true));
    }

    public function testReadAsArrayOptionsAndGetLineCount(): void {
        $file = new File($this->tempDir . '/lines.txt');
        $file->write("alpha\n\nbeta\n");

        $lines = $file->readAsArray(true, ['ignoreNewLines' => true, 'skipEmptyLines' => true]);

        $this->assertInstanceOf(Options::class, $lines);
        $this->assertSame(['alpha', 'beta'], $lines->toArray());
        $this->assertSame(2, $file->getLineCount());
    }

    public function testAppendAndPrependModifyFileContents(): void {
        $file = new File($this->tempDir . '/append-prepend.txt');
        $file->write('world');

        $file->prepend('hello ');
        $file->append('!');

        $this->assertSame('hello world!', $file->read(true));
    }

    public function testGetFilesGetDirectoriesAndGetFile(): void {
        mkdir($this->tempDir . '/child');
        file_put_contents($this->tempDir . '/alpha.txt', 'A');
        file_put_contents($this->tempDir . '/beta.log', 'B');

        $path = new Path($this->tempDir);

        $txtFiles = $path->getFiles('*.txt');
        $this->assertCount(1, $txtFiles);
        $this->assertInstanceOf(File::class, $txtFiles[0]);
        $this->assertSame('alpha.txt', $txtFiles[0]->getFilename());

        $dirs = $path->getDirectories('*');
        $this->assertCount(1, $dirs);
        $this->assertInstanceOf(Path::class, $dirs[0]);
        $this->assertSame('child', $dirs[0]->getFilename());

        $existing = $path->getFile('alpha.txt', true);
        $missing = $path->getFile('missing.txt', true);

        $this->assertInstanceOf(File::class, $existing);
        $this->assertNull($missing);
    }

    public function testGetChildPathAndGetParentReturnExpectedPaths(): void {
        $file = new File($this->tempDir . '/parent.txt');

        $child = $file->getChildPath('nested/final');
        $parent = $file->getParent();

        $this->assertInstanceOf(Path::class, $child);
        $this->assertSame($this->tempDir . '/nested/final', $child->getPathname());
        $this->assertSame($this->tempDir, $parent->getPathname());
    }

    public function testMakePathCreatesNestedDirectoryPath(): void {
        $path = new Path($this->tempDir . '/a/b/c');

        $created = $path->makePath();

        $this->assertInstanceOf(Path::class, $created);
        $this->assertTrue(is_dir($this->tempDir . '/a/b/c'));
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
