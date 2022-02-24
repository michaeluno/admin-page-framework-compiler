# Admin Page Framework Compiler
A compiler script for Admin Page Framework, a WordPress development framework.

## Installation
### Composer
To install the library using Composer, run 

```bash
composer require michaeluno/admin-page-framework-compiler
```

## Basic Usage
```php
$oCompiler = new \AdminPageFrameworkCompiler\Compiler( $sSourceDirPath, $sDestinationDirPath );
$oCompiler->run();
```

## Options
The options array takes the following arguments.
- `output_buffer`       : (boolean) whether output buffer should be printed. 
- `exclude_classes`     : (array)        an array holding class names to exclude.
- `css_heredoc_keys`    : (array, optional) an array holding heredoc keywords used to assign CSS rules to a variable.
- `js_heredoc_keys`     : (array, optional) an array holding heredoc keywords used to assign JavaScript scripts to a variable.
- `combine` : (array, optional) Combine option
  - `inheritance` : (boolean) Whether to combine files in the same directory with hierarchical relationships.
  - `exclude_classes` : (string|array, optional)  Class names to exclude from combining.
- `search`				: (array)	the arguments for the directory search options.
   - `allowed_extensions`: (array) allowed file extensions to be listed. e.g. `[ 'php', 'inc' ]` 
   - `exclude_dir_paths`: (array) directory paths to exclude from the list.  
   - `exclude_dir_names`: (array) directory base names to exclude from the list. e.g. `[ 'temp', '_bak', '_del', 'lib', 'vendor', ]` 
   - `exclude_file_names`: (array) a sub-string of file names to exclude from the list. e.g. `[ '.min' ]` 
   - `exclude_substrings`: (array) sub-strings of paths to exclude from the list. e.g. `[ '.min', '_del', 'temp', 'library', 'vendor' ]`
   - `is_recursive`: (boolean) whether to scan sub-directories.
   - `ignore_note_file_names`: (array) ignore note file names that tell the parser to skip the directory. When one of the files exist in the parsing directory, the directory will be skipped. Default: `[ 'ignore-class-map.txt' ]`,
- `comment_header`  : (array, optional)   what header comment to insert at the top of the generated file
  - `text`  : (string, optional) the header comment to set    
  - `path`  : (string, optional) the file path to extract the comment from
  - `class` : (string, optional) the class name to use its doc-block as the header comment
  - `type`  : (string, optional) indicates what type of data to collect. Accepted values are `DOCBLOCK`, `CONSTANTS`.
  When `type` is `CONSTANTS`, the constants of the header class must include `VERSION`, `NAME`, `DESCRIPTION`, `URI`, `AUTHOR`, `COPYRIGHT`, `LICENSE`.
  ```php
      class Sample_Registry {
        const VERSION       = '1.0.0';
         const NAME          = 'Sample Project';
         const DESCRIPTION   = 'Provides an enhanced task management system for WordPress.';
         const URI           = 'https://en.michaeluno.jp/';
         const AUTHOR        = 'miunosoft (Michael Uno)';
         const AUTHOR_URI    = 'https://en.michaeluno.jp/';
         const COPYRIGHT     = 'Copyright (c) 2014, <Michael Uno>';
         const LICENSE       = 'GPL v2 or later';
         const CONTRIBUTORS  = '';
      }
  ```
  - `php_cs_fixer` : (array, optional)  PHP CS Fixer options. 
    - `config`: (string, object) The config object or the config file path.
    - `rules`: (array) An array holding custom rules.
  
## Example
```php
$oCompiler = new \AdminPageFrameworkCompiler\Compiler(
    $sSourceDirPath,
    $sDestinationDirPath,
    [
        'output_buffer'        => true,
        'exclude_classes'      => [],
        'css_heredoc_keys'     => [ 'CSSRULES' ],       // to disable inline CSS minification, set an empty array
        'js_heredoc_keys'      => [ 'JAVASCRIPTS' ],    // to disable inline JavaScript minification, set an empty array
        'search'               => [
            'allowed_extensions'    => [ 'php' ],    // e.g. array( 'php', 'inc' )
            // 'exclude_dir_paths'  => array( $sTargetBaseDir . '/include/class/admin' ),
            'exclude_dir_names'     => [ '_document', 'document', 'cli' ],
            'exclude_dir_names_regex' => [
                '/\.bundle$/'
            ],
            'exclude_file_names'    => [
                'AdminPageFramework_InclusionClassFilesHeader.php',
                'AdminPageFramework_MinifiedVersionHeader.php',
                'AdminPageFramework_BeautifiedVersionHeader.php',
            ],
            'is_recursive'            => true,
        ],
        'include'               => [
            'allowed_extensions'    => [ 'js', 'css', 'map' ],    // e.g. array( 'php', 'inc' )
        ],
        'comment_header'        => [
            'path' => $sFilePath,
        ],
    ]
);
$oCompiler->run();
```