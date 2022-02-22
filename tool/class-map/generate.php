<?php
/* Configuration */
$sTargetBaseDir  = dirname( dirname( __DIR__ ) );
$sResultFilePath = $sTargetBaseDir . '/source/class-map.php';

/* If accessed from a browser, exit. */
$bIsCLI          = php_sapi_name() == 'cli';
$sCarriageReturn = $bIsCLI ? PHP_EOL : '<br />';
if ( ! $bIsCLI ) {
    exit;
}

/* Include necessary files */
require( $sTargetBaseDir . '/vendor/autoload.php' );

/* Check the permission to write. */
if ( ! file_exists( $sResultFilePath ) ) {
    file_put_contents( $sResultFilePath, '', FILE_APPEND | LOCK_EX );
}
if (
    ( file_exists( $sResultFilePath ) && ! is_writable( $sResultFilePath ) )
    || ! is_writable( dirname( $sResultFilePath ) )
) {
    exit( sprintf( 'The permission denied. Make sure if the folder, %1$s, allows to modify/create a file.', dirname( $sResultFilePath ) ) );
}

/* Create a minified version of the framework. */
echo 'Started...' . $sCarriageReturn;

$_oGenerator = new \PHPClassMapGenerator\PHPClassMapGenerator(
    $sTargetBaseDir . '/source',
    [ $sTargetBaseDir . '/source' ],  // scan dirs
    $sResultFilePath,
    [
        'comment_header'    => [
            'path'  => $sTargetBaseDir . '/source/Compiler.php'
        ],
        'output_buffer'     => true,
        'output_var_name'   => 'return',
        'base_dir_var'      => '__DIR__',
        'exclude_classes'   => [],
        'search'            => [
            'exclude_dir_names'  => [
                'library',
                'del.bak',
                '_del',
                '_bak',
                '_notes',
                'vendor',
                'node_modules',
                'asset',
                'assets'
            ],
            'is_recursive'       => true,
            'allowed_extensions' => [ 'php' ],
            // 'exclude_file_names'     => [ '.min.', ],
            // 'exclude_dir_paths'      => [],
            // 'ignore_note_file_names' => [ 'ignore-class-map.txt' ],
        ],
    ]
);

echo 'Done!' . $sCarriageReturn;