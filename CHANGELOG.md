# Changelog: File

> version: $Id$ ($Date$)

## History

### 0.2.5-dev @2025 Mar 19

- fix: return type of `getChildPath`

### 0.2.4 @2025 Mar 02

- fix: null reference errors

### 0.2.3 @2023 Jun 27

 - new: `File::getLineCount` returns number of lines for valid files and `null` for invalid files
 - fix: `File::readAsArray` options array default value
 - fix: `File::readAsArray` return type

### 0.2.2 @2023 May 03

 - update: `File::makePath` check if path exists before trying to create it
 - new   : `FSOInterface` **File System Object Interface** common root interface for fs items useful for typing
 - new   : `File::append` A convenience method for appending text to a file
 - new   : `File::readAsArray` Reads entire file into an array

### 0.2.1 @2023 Feb 02

 - new   : `Path::makePath` create directory
 - new   : `File::getParent` gets parent directory
 - new   : `File::getBase64Image` added support for more image types
 - new   : `File::isDir` replaces parent method, handles non-existing paths better (more to my liking)
 - new   : `File::getChildPath` return a path based on the current path
 - new   : `File::parsePath` Parse a string path or current working directory
 - new   : `File::combinePaths` Combines two string paths
 - update: `File::getFile` new argument `$onlyIfValid=false` only requires file path to be valid when set to `true`
 - update: `File` improved caching read file contents
 - general fixes and clean ups

### 0.2.0 @2022 Oct 18

 - rename: `FileInfo` to `File`
 - new   : `File` => `getDir` > returns directory, file's return parent directory
 - new   : `File` => `read` > gets file contents, caching contents
 - new   : `File` => `write` > sets file contents
 - new   : `File` => `move` > move file
 - update: `constructor` if no argument supplied the current directory is used
 - update: `getFile` & `getFiles` return `File` object(s)
 - minor : Improvements & fixes

### 0.1.0 @2022 Aug 04

 - initial
