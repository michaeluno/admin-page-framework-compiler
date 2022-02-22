<?php
include( dirname( __DIR__ ) . '/vendor/autoload.php' );
include( dirname( __DIR__ ) . '/source/Compiler.php' );

$_sSourceDirPath = dirname( __DIR__ ) . '/source/';
$_sOutputDirPath = __DIR__ . '/output';

$_oCompiler = new \AdminPageFrameworkCompiler\Compiler(
    $_sSourceDirPath,
    $_sOutputDirPath,
    [
        'search'    => [
            'exclude_dir_names' => [ 'library' ],
        ]
    ]
);
$_oCompiler->run();