<?php

/**
 * Inane: File
 *
 * File utilities for local and remote files.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\file
 * @category file
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\File;

use function getcwd;
use function is_null;

/**
 * Path
 *
 * Path specific utility
 *
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
