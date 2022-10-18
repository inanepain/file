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
use function implode;
use function is_null;
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
class Path {
    protected array $path = [];

    /**
     * Constructor
     *
     * @param null|string $path
     * @return void
     */
    public function __construct(?string $path = null) {
        $this->setPath($path);
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
     * getPath
     *
     * @return string
     */
    public function getPath(): string {
        return implode(DIRECTORY_SEPARATOR, $this->path);
    }

    /**
     * setPath
     *
     * @param string $path
     * @return \Inane\File\Path
     */
    public function setPath(string $path): self {
        $this->path = static::parsePath($path);

        return $this;
    }
}
