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

use SplFileInfo;
use Inane\Stdlib\{
	String\Capitalisation,
	Options
};

use function array_map;
use function array_pop;
use function base64_encode;
use function count;
use function file;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function floor;
use function getcwd;
use function glob;
use function in_array;
use function is_dir;
use function is_null;
use function is_string;
use function ltrim;
use function md5_file;
use function mkdir;
use function pow;
use function rename;
use function rtrim;
use function sprintf;
use function strtolower;
use function strtoupper;
use function unlink;
use function unserialize;

use const DIRECTORY_SEPARATOR;
use const FILE_APPEND;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const GLOB_ONLYDIR;
use const LOCK_EX;
use const null;

/**
 * File metadata
 *
 * @method File getFileInfo()
 *
 * @package Inane\File
 *
 * @version 0.16.0
 */
class File extends SplFileInfo implements FSOInterface {
	/**
	 * File contents cache
	 *
	 * @since 0.14.0
	 *
	 * @var null|\Inane\Stdlib\Options
	 */
	private ?Options $contentCache = null;

	/**
	 * FileInfo
	 *
	 * @param null|string $file_name file, default: current dir
	 *
	 * @return void
	 */
	public function __construct(?string $file_name = null) {
		if (is_null($file_name)) $file_name = getcwd();

		$this->contentCache = new Options([
			'string' => null,
			'array' => null,
		]);

		// parent::__construct($file_name);
		parent::__construct(static::parsePath($file_name));
		$this->setInfoClass(static::class);
	}

	/**
	 * Get the file extension
	 *
	 * @param Capitalisation    $case Optional: Capitalisation only UPPERCASE and lowercase have any effect
	 * {@inheritDoc}
	 * @see \SplFileInfo::getExtension()
	 */
	public function getExtension(?Capitalisation $case = null): string {
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
	 * Return number of lines
	 *
	 * @since 0.15.0
	 *
	 * @return int|null line count or `null` if invalid file
	 */
	public function getLineCount(): ?int {
		if ($this->isValid())
			return count($this->readAsArray(options: ['ignoreNewLines' => true, 'skipEmptyLines' => true]));

		return null;
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
	 * Get files matching filter
	 *
	 * If file: returns siblings
	 * If dir: returns children
	 *
	 * Flags:
	 * GLOB_MARK     - Adds a slash (a backslash on Windows) to each directory returned
	 * GLOB_NOSORT   - Return files as they appear in the directory (no sorting). When this flag is not used, the pathnames are sorted alphabetically
	 * GLOB_NOCHECK  - Return the search pattern if no files matching it were found
	 * GLOB_NOESCAPE - Backslashes do not quote metacharacters
	 * GLOB_BRACE    - Expands {a,b,c} to match 'a', 'b', or 'c'
	 * GLOB_ONLYDIR  - Return only directory entries which match the pattern
	 * GLOB_ERR      - Stop on read errors (like unreadable directories), by default errors are ignored.
	 *
	 * @since 0.2.5 returns array of `File` and `Path` objects.
	 *
	 * @param string $filter
	 * @param int $flags glob flags
	 *
	 * @return \Inane\File\File[]|\Inane\File\Path[]|null
	 */
	public function getFiles(string $filter = '*', int $flags = 0): ?array {
		if ($found = glob($this->getDir() . DIRECTORY_SEPARATOR . $filter, $flags))
			return array_map(fn ($f): File => is_dir($f) ? new Path($f) : new File($f), $found);

		return null;
	}

	/**
	 * Retrieves a list of directories based on the specified filter.
	 *
	 * @since 0.2.5
	 *
	 * @param string $filter A pattern to filter the directories. Defaults to '*'.
	 *
	 * @return \Inane\File\Path[]|null An array of directories matching the specified filter.
	 */
	public function getDirectories(string $filter = '*'): ?array {
		return $this->getFiles($filter, GLOB_ONLYDIR);
	}

	/**
	 * Get child/sibling file matching $file name
	 *
	 * @param string $filePattern file name or pattern relative to parent (pattern only effective if $onlyIfValid == false)
	 * @param bool $onlyIfValid only return file if it exists else null
	 *
	 * @return null|\Inane\File\File file or if it must be a valid file and is not null
	 */
	public function getFile(string $filePattern, bool $onlyIfValid = false): ?File {
		if ($onlyIfValid) {
			if ($fs = $this->getFiles($filePattern))
				return array_pop($fs);
		} else
			return new File($this->getDir() . DIRECTORY_SEPARATOR . ltrim($filePattern, DIRECTORY_SEPARATOR));

		return null;
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
		if ($this->isValid() && in_array($ext, ['png', 'jpg', 'gif', 'bmp'])) {
			$data = file_get_contents($this->getPathname());
			$base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
		}
		return $base64;
	}

	/**
	 * gets contents of file
	 *
	 * @since 0.9.0
	 *
	 * @param bool $fresh read from file even if a cached version in memory
	 *
	 * @return null|string file content
	 */
	public function read(bool $fresh = false): ?string {
		if (is_null($this->contentCache->string) || $fresh)
			$this->contentCache->string = ($this->isFile() && $this->isReadable()) ? file_get_contents($this->getPathname()) : null;

		return $this->contentCache->string === false ? null : $this->contentCache->string;
	}

	/**
	 * Reads entire file into an array
	 *
	 * You can supply an options array to adjust the following options:
	 *  * ignoreNewLines [bool=false] - Omit newline at the end of each array element
	 *  * skipEmptyLines [bool=false] - Skip empty lines
	 *
	 * Note: Each line in the resulting array will include the line ending, unless $options['ignoreNewLines'] is `true`.
	 *
	 * @since 0.14.0
	 *
	 * @param bool $fresh read from file even if a cached version in memory
	 * @param array $options [ 'ignoreNewLines' => false, 'skipEmptyLines' => false ]
	 *
	 * @return null|array|\Inane\Stdlib\Options Returns the file in an array. Each element of the array corresponds to a line in the file, with the newline still attached. Upon failure, readAsArray() returns `null`.
	 */
	public function readAsArray(bool $fresh = false, array $options = []): null|array|Options {
		$options = new Options($options + [ 'ignoreNewLines' => false, 'skipEmptyLines' => false ]);

		$flags = 0;
		if ($options->ignoreNewLines) $flags |= FILE_IGNORE_NEW_LINES;
		if ($options->skipEmptyLines) $flags |= FILE_SKIP_EMPTY_LINES;

		if (is_null($this->contentCache->array) || $fresh)
			$this->contentCache->array = ($this->isFile() && $this->isReadable()) ? file($this->getPathname(), $flags) : null;

		return $this->contentCache->array === false ? null : $this->contentCache->array;
	}

	/**
	 * Writes the given contents to the file.
	 *
	 * @since 0.9.0
	 * @since 0.16.0 option to skip caching contents
	 *
	 * @param mixed $contents The data to write to the file. Can be a string, array, or other writable data type.
	 * @param bool $append Whether to append the contents to the file. If false, the file will be overwritten. Default is false.
	 * @param bool $createPath Whether to create the directory path if it does not exist. Default is true.
	 * @param bool $cacheContents Whether to cache the contents after writing. Default is true.
	 *
	 * @return bool|int Returns the number of bytes written on success, or false on failure.
	 */
	public function write(mixed $contents, bool $append = false, bool $createPath = true, bool $cacheContents = true): bool|int {
		$flag = $append ? FILE_APPEND | LOCK_EX : 0;

		if (!$this->isValid())
			$this->getParent()->makePath(recursive: $createPath);
		elseif (!$this->isWritable())
			return false;

		$success = file_put_contents($this->getPathname(), $contents, $flag);

		if ($success !== false) {
			if ($cacheContents) {
				if (!$append) $this->contentCache->string = $contents;
				else $this->contentCache->string = null;

				$this->contentCache->array = null;
			}
			return $success;
		}

		return false;
	}

	/**
	 * Clears the cache associated with the file.
	 *
	 * @since 0.16.0
	 *
	 * @return bool Returns true if the cache was successfully cleared, false otherwise.
	 */
	public function clearCache(): bool {
		$cleared = false;

		if ($this->contentCache->string) {
			$cleared = true;
			$this->contentCache->string = null;
		}

		if ($this->contentCache->array) {
			$cleared = true;
			$this->contentCache->array = null;
		}

		return $cleared;
	}

	/**
	 * append $contents to the end of file
	 *
	 * A convenience method for appending text to a file.
	 *
	 * @since 0.14.0
	 *
	 * @param mixed $contents The data to append. Can be either a string, an array or a stream resource.
	 *
	 * @return bool|int false on failure otherwise number of bytes written.
	 */
	public function append(mixed $contents): bool|int {
		return $this->write($contents, true);
	}

	/**
	 * prepend $contents to beginning of file
	 *
	 * @since 0.10.0
	 *
	 * @param mixed $contents The data to prepend. Can be either a string, an array or a stream resource.
	 *
	 * @return bool|int false on failure otherwise number of bytes written.
	 */
	public function prepend(mixed $contents): bool|int {
		$contents .= $this->read();
		return $this->write($contents);
	}

	/**
	 * Moves a file
	 *
	 * @since 0.10.0
	 *
	 * @return bool success
	 */
	public function move(File|string $dest): bool {
		if ($this->isValid() && $this->isWritable()) {
			$target = is_string($dest) ? $dest : $dest->getPathname();
			return rename($this->getPathname(), $target);
		}

		return false;
	}

	/**
	 * Deletes a file
	 *
	 * @since 0.8.0
	 *
	 * @return bool
	 */
	public function remove(): bool {
		if ($this->isValid() && $this->isWritable()) unlink($this->getPathname());

		return false;
	}

	/**
	 * Parse a string path or current working directory
	 *
	 *  - sets directory separators to DIRECTORY_SEPARATOR
	 *  - removes trailing DIRECTORY_SEPARATOR
	 *
	 * @param null|string $path
	 *
	 * @return string
	 */
	public static function parsePath(?string $path = null): string {
		$path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path ?? getcwd());

		return rtrim($path, DIRECTORY_SEPARATOR);
	}

	/**
	 * Combines two string paths
	 *
	 * @since 0.12.0
	 *
	 * @param string $parent base path
	 * @param string $child path to append
	 *
	 * @return string combined path
	 */
	public static function combinePaths(string $parent, string $child): string {
		return static::parsePath($parent) . DIRECTORY_SEPARATOR . ltrim(static::parsePath($child), DIRECTORY_SEPARATOR);
	}

	/**
	 * Returns of the path is a file or directory
	 *
	 * {@inheritDoc}
	 * @see \SplFileInfo::isDir()
	 */
	public function isDir(): bool {
		if (static::class == Path::class) return true;
		else if ($this->isValid())
			return parent::isDir();
		else
			return $this->getExtension() == '' ? true : false;
	}

	/**
	 * Gets directory
	 *
	 * return:
	 * - dir: it's self
	 * - file: parent dir
	 *
	 * @since 0.8.0
	 *
	 * @return string|null
	 */
	public function getDir(): ?string {
		return $this->isDir() ? $this->getPathname() : $this->getPath();
	}

	/**
	 * Retrieves the child path of the given path name.
	 *
	 * @since 0.12.0
	 *
	 * @param string $pathName The name of the path to retrieve the child path for.
	 *
	 * @return Path The child path corresponding to the given path name.
	 */
	public function getChildPath(string $pathName): Path {
		return new Path(static::combinePaths($this->isDir() ? $this->getPathname() : $this->getPath(), $pathName));
	}

	/**
	 * Get Parent directory
	 *
	 * @since 0.11.0
	 *
	 * @return \Inane\File\Path
	 */
	public function getParent(): Path {
		return new Path($this->getPath());
	}

	/**
	 * Create missing elements of path.
	 *
	 * All missing segments except last are treated as directories.
	 * The last segment is treated as a dir if no extension is set.
	 *
	 * @since 0.12.0
	 *
	 * @param bool $recursive create all path segments recursively
	 * @param int $permissions filesystem permissions to apply to created path
	 *
	 * @return null|\Inane\File\File|\Inane\File\Path If this is a dir returns itself, if this is a file returns a new File pointing to final dir created, returns null on failure
	 */
	public function makePath(bool $recursive = true, int $permissions = 0777): ?static {
		$directory = $this->isDir() ? $this->getPathname() : $this->getPath();
		$path = $this->isDir() ? $this : $this->getParent();
		if (!$path->isValid()) if (mkdir(
			$path->getPathname(),
			$permissions,
			$recursive,
		)) return $this->isDir() ? $this : new static($directory);

		return null;
	}
}
