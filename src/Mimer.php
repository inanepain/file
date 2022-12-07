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
 * @category filesystem
 *
 * @license UNLICENSE
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\File;

use function array_filter;
use function array_key_exists;
use function array_shift;
use function count;
use function file;
use function in_array;
use function is_null;
use function preg_match_all;
use function sort;
use function str_starts_with;
use function trim;
use const false;
use const FILE_IGNORE_NEW_LINES;
use const null;

/**
 * Mimer
 *
 * MIME type query tool.
 *
 * @package Inane\File
 * @version 0.1.0
 */
class Mimer {
    /**
     * Url of apache's mime.types file
     */
    const APACHE_MIME_TYPES_URL = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';

    /**
     * Location of cached custom MIME type data
     *
     * @var string
     */
    private static string $cacheFile = __DIR__ . '/../mime.types.blast';

    /**
     * MIME type data
     *
     * @var array
     */
    private static array $data = [
        'extension' => [],
        'mimetype' => [],
        'modified' => 0,
    ];

    /**
     * Gets a mime.type file
     *
     * @param string $file path or url of file
     *
     * @return null|array file as array without comments
     */
    protected static function fetchMimeTypeFile(string $file): ?array {
        $data = file($file, FILE_IGNORE_NEW_LINES);

        if ($data !== false) $data = array_filter($data, fn ($line) => !str_starts_with($line, '#'));
        else $data = null;

        return $data;
    }

    /**
     * Parses a line from a mime.type file
     *
     * @param string $line MIME type entry
     *
     * @return null|array MIME type data
     */
    protected static function parseMimeEntry(string $line): ?array {
        $count = preg_match_all('([^\s]+)', $line, $matches);
        $extensions = $matches[0];
        $mimeType = array_shift($extensions);

        return ['type' => $mimeType, 'exts' => $extensions];
    }

    /**
     * Update MIME type lookups
     *
     * @param string $type MIME type
     * @param array $exts extensions
     * @param null|array $data MIME types data if not using internal data
     *
     * @return void
     */
    protected static function updateMimeTypes(string $type, array $exts, ?array &$data = null): void {
        if (is_null($data)) $data = &static::$data['mimetype'];

        if (!array_key_exists($type, $data))
            $data[$type] = $exts;
        else foreach ($exts as $ext) {
            if (!in_array($ext, $data[$type]))
                $data[$type][] = $ext;
        }

        sort($data[$type]);
    }

    /**
     * Update extension lookups
     *
     * @param string $type mimetype
     * @param array $exts extensions
     * @param null|array $data extensions data if not using internal data
     *
     * @return void
     */
    protected static function updateExtensions(string $type, array $exts, ?array &$data = null): void {
        if (is_null($data)) $data = &static::$data['extension'];

        foreach ($exts as $ext) {
            if (!array_key_exists($ext, $data))
                $data[$ext] = [$type];
            else if (!in_array($type, $data[$ext]))
                $data[$ext][] = $type;
        }

        sort($data[$ext]);
    }

    /**
     * Update database from apache mime.types on the web
     *
     * @return array cached data
     */
    public static function updateMimeTypeCache(): array {
        $raw = static::fetchMimeTypeFile(static::APACHE_MIME_TYPES_URL);

        $mimetype = [];
        $extension = [];

        foreach ($raw as $line) {
            $mime = static::parseMimeEntry($line);

            $mime['data'] = $mimetype;
            $mimetype = static::updateMimeTypes(...$mime);

            $mime['data'] = $extension;
            $extension = static::updateExtensions(...$mime);
        }

        if (count($mimetype) > 0 && count($extension) > 0) static::$data = [
            'mimetype' => $mimetype,
            'extension' => $extension,
            'modified' => \Inane\Datetime\Timestamp::now(),
        ];

        return static::$data;
    }

    public static function buildDatabaseV1(): void {
        $data = file(static::APACHE_MIME_TYPES_URL, FILE_IGNORE_NEW_LINES);
        $mimeTypes = [
            'extension' => [],
            'mimetype' => [],
        ];

        foreach ($data as $line) {
            $l = trim($line);
            if (!str_starts_with($l, '#')) {
                $i = preg_match_all('([^\s]+)', $l, $matches);
                if ($i > 0) {
                    $exts = $matches[0];
                    $type = array_shift($exts);



                    if (!array_key_exists($type, $mimeTypes['mimetype'])) {
                        $mimeTypes['mimetype'][$type] = $exts;
                    } else {
                        foreach ($exts as $x) {
                            if (!in_array($x, $mimeTypes['mimetype'][$type])) {
                                $mimeTypes['mimetype'][$type][] = $x;
                            }
                        }
                    }
                    sort($mimeTypes['mimetype'][$type]);

                    foreach ($mimeTypes['mimetype'][$type] as $x) {
                        if (!array_key_exists($x, $mimeTypes['extension'])) {
                            $mimeTypes['extension'][$x] = [$type];
                        } else {
                            if (!array_key_exists($type, $mimeTypes['extension'][$x])) {
                                $mimeTypes['extension'][$x][] = $type;
                            }
                        }
                    }
                    sort($mimeTypes['extension'][$x]);
                }
            }
        }
    }
}
