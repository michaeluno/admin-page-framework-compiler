<?php
include( dirname( __DIR__ ) . '/vendor/autoload.php' );
include( dirname( __DIR__ ) . '/source/Compiler.php' );

$_sSourceDirPath = dirname( __DIR__ ) . '/source/';
$_sOutputDirPath = __DIR__ . '/output';

$_oCompiler = new \AdminPageFrameworkCompiler\Compiler(
    $_sSourceDirPath,
    $_sOutputDirPath,
    [
        'excludes'  => [
            'file_names' => [ 'Compiler.php', 'TraitLog.php' ],
        ],
        'search'    => [
            'exclude_dir_names' => [ 'library' ],
        ]
    ]
);
$_oCompiler->run();