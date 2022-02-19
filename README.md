# Admin Page Framework Compiler
A compiler script for Admin Page Framework, a WordPress development framework.

## Basic Usage
```php
$oCompiler = new \AdminPageFrameworkCompiler\Compiler( $sSourceDirPath, $sDestinationDirPath );
$oCompiler->run();
```

## Options
The options array takes the following arguments.
- 'output_buffer'       : (boolean) whether output buffer should be printed.
- 'header_class_name'   : (string)  the class name that provides the information for the heading comment of the result output of the minified script.
- 'header_class_path'   : (string, optional) the path to the header class file.
- 'header_type'         : (string) Indicates the comment header type.  Accepted values: `CONSTANTS` or `DOCBLOCK`.
  When `header_type` is `CONSTANTS`, the constants of the header class must include `VERSION`, `NAME`, `DESCRIPTION`, `URI`, `AUTHOR`, `COPYRIGHT`, `LICENSE`.
  ```
     class Sample_Registry_Base {
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
- 'exclude_classes'     : (array)        an array holding class names to exclude.
- 'css_heredoc_keys'    : (array, optional) an array holding heredoc keywords used to assign CSS rules to a variable.
- 'js_heredoc_keys'     : (array, optional) an array holding heredoc keywords used to assign JavaScript scripts to a variable.
- 'combine' : (array, optional) Combine option
  - 'inheritance' : (boolean) Whether or not to combine files in the same directory with hierarchical relationships.
  - 'exclude_classes' : (string|array, optional)  Class names to exclude from combining.
- 'search'              : array        the arguments for the directory search options.

## Example
```php
$oCompiler = new \AdminPageFrameworkCompiler\Compiler(
    $sSourceDirPath,
    $sDestinationDirPath,
    [
        'header_class_name'    => $sHeaderClassName,
        'header_class_path'    => $sHeaderClassPath,
        'output_buffer'        => true,
        'header_type'          => 'CONSTANTS',
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
    ]
);
$oCompiler->run();
```