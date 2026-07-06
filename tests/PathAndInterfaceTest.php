<?php

declare(strict_types=1);

namespace Inane\File\Tests;

use Inane\File\FSOInterface;
use Inane\File\Path;
use PHPUnit\Framework\TestCase;

final class PathAndInterfaceTest extends TestCase {
    public function testPathDefaultsToCurrentWorkingDirectoryWhenNullProvided(): void {
        $path = new Path();

        $this->assertTrue($path->isDir());
        $this->assertNotSame('', $path->getPathname());
    }

    public function testPathImplementsFsoInterface(): void {
        $path = new Path();

        $this->assertInstanceOf(FSOInterface::class, $path);
    }
}
