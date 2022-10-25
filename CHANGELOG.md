# Changelog: File

> version: $Id$ ($Date$)

## History

### 0.2.1-dev @2022 Oct xx

 - new: `Path::makePath` create directory
 - new: `File::getParent` gets parent directory

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
