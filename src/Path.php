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
}
