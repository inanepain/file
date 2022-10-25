<?php

/**
 * Inane
 *
 * File
 *
 * PHP version 8.1
 *
 * @author Philip Michael Raab<peep@inane.co.za>
 * @package Inane\Stdlib
 *
 * @license UNLICENSE
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\File;

use function explode;
use function getcwd;
use function is_dir;
use function is_null;
use function mkdir;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * Path
 *
 * Path specific utility
 *
 * @package Inane\File
 * @version 0.1.0
 */
class Path extends File {
    /**
     * FileInfo
     *
     * @param null|string $file_name file, default: current dir
     *
     * @return void
     */
    public function __construct(?string $file_name = null) {
        if (is_null($file_name)) $file_name = getcwd();

        parent::__construct($file_name);
        $this->setInfoClass(static::class);
    }

    /**
     * parsePath
     *
     * @param null|string $path
     * @return array
     */
    protected static function parsePath(?string $path = null): array {
        if (is_null($path)) $path = getcwd();
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return explode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Creates this path
     *
     * @param bool $recursive
     *
     * @return bool
     */
    public function makePath(bool $recursive = true): bool {
        return mkdir(directory: $this->getPathname(), recursive: $recursive);
    }
}
