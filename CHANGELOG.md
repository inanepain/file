# Changelog: File

> version: $Id$ ($Date$)

## History

### 0.2.1-dev @2022 Oct xx

 - new   : `Path::makePath` create directory
 - new   : `File::getParent` gets parent directory
 - new   : `File::getBase64Image` added support for more image types
 - new   : `File::isDir` replaces parent method, handles non-existing paths better (more to my liking)
 - new   : `File::getChildPath` return a path based on the current path
 - new   : `File::parsePath` Parse a string path or current working directory
 - new   : `File::combinePaths` Combines two string paths
 - update: `File::getFile` new argument `$onlyIfValid=false` only requires file path to be valid when set to `true`

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
