<?php

/**
 * Inane: Stdlib
 *
 * Inane Standard Library
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

use Inane\Stdlib\String\Capitalisation;
use SplFileInfo;

use function array_pop;
use function base64_encode;
use function file_exists;
use function file_get_contents;
use function floor;
use function glob;
use function in_array;
use function md5_file;
use function pow;
use function rtrim;
use function sprintf;
use function strtolower;
use function strtoupper;
use function unserialize;

/**
 * File metadata
 *
 * @method FileInfo getFileInfo()
 *
 * @package Inane\File
 * @version 0.7.2
 */
class FileInfo extends SplFileInfo {
    /**
     * FileInfo
     *
     * @param string $file_name file
     *
     * @return void
     */
    public function __construct(string $file_name) {
        parent::__construct($file_name);
        $this->setInfoClass(static::class);
    }

    /**
     * Get the file extension
     *
     * @param Capitalisation    $case Optional: Capitalisation only UPPERCASE and lowercase have any effect
     * {@inheritDoc}
     * @see \SplFileInfo::getExtension()
     */
    public function getExtension(Capitalisation $case = null): string {
        $ext = parent::getExtension();

        return match ($case) {
            Capitalisation::UPPERCASE => strtoupper($ext),
            Capitalisation::lowercase => strtolower($ext),
            default => $ext,
        };
    }

    /**
     * Return human readable size (Kb, Mb, ...)
     *
     * @param int $decimals
     * @return string|null
     */
    public function getHumanSize($decimals = 2): ?string {
        return $this->humanSize(parent::getSize(), $decimals);
    }

    /**
     * Return md5 hash
     * @return string|bool
     */
    public function getMd5(): string|bool {
        return md5_file(parent::getPathname());
    }

    /**
     * Return the mime type
     *
     * @param string|null $default if not matched
     *
     * @return null|string
     */
    public function getMimetype(?string $default = null): ?string {
        $mimes = unserialize(file_get_contents(__DIR__ . '/../mimeic.blast'));
        return $mimes['mimes'][$this->getExtension(Capitalisation::lowercase)] ?? $default;
    }

    /**
     * True if file exists
     *
     * @return bool
     */
    public function isValid(): bool {
        return file_exists(parent::getPathname());
    }

    /**
     * Convert bites to human readable size
     *
     * @param int $size
     * @param int $decimals
     * @return string
     */
    protected function humanSize(int $size, int $decimals = 2): string {
        $sizes = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($size) - 1) / 3);
        $formattedSize = sprintf("%.{$decimals}f", $size / pow(1024, $factor));

        return rtrim($formattedSize, '0.') . ' ' . @$sizes[$factor];
    }

    /**
     * Get files in dir
     *
     * @param string $filter
     * @return array|null
     */
    public function getFiles(string $filter = '*'): ?array {
        return glob(parent::getPathname() . '/' . $filter) ?? null;
    }

    /**
     * Ges file in dir
     *
     * @param string $file the file to get
     * @return string|null
     */
    public function getFile(string $file): ?string {
        $paths = glob(parent::getPathname() . '/' . $file);
        return array_pop($paths);
    }

    /**
     * Get image as base64
     *
     * @since 0.7.0
     *
     * @return null|string base64 string or null if not a png/jpg/not a file
     */
    public function getBase64Image(): ?string {
        $ext = $this->getExtension(Capitalisation::lowercase);
        $base64 = null;
        if ($this->isValid() && in_array($ext, ['png', 'jpg'])) {
            $data = file_get_contents($this->getPathname());
            $base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
        }
        return $base64;
    }
}
