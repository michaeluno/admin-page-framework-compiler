<?php

namespace PHPCodeBeautifier;

use \Exception;
use PHPClassMapGenerator\PHPClassMapGenerator;

include_once( __DIR__ . '/TraitFileSystemUtility.php' );
include_once( __DIR__ . '/TraitDownload.php' );

class DependencyLoader {

    use TraitFileSystemUtility;
    use TraitDownload;

    /**
     * @var array
     * #### Structure
     * ```
     * [
     *     'name'             => 'PHPBeautifier',
     *     'type'             => 'zip',
     *     'url'              => 'https://github.com/michaeluno/PHP_Beautifier/archive/0.1.17.1.zip',
     *     'pre_requirements' => [
     *         'functions' => [
     *             'token_get_all',
     *         ],
     *         'classes'   => [],
     *     ],
     *     'auto_loader'     => 'Beautifier.php',    // the bootstrap file base name of the library that includes its components
     * ],
     * ```
     */
    public $aDependency = [];

    public $aDefaults   = [
        'name'  => '',
        'type'  => 'zip',
        'url'   => '',
        'requirements'  => [
            'functions' => [],
            'classes'   => [],
        ],
        'autoloader'    => '',
    ];

    /**
     * Sets up properties.
     */
    public function __construct( array $aDependency ) {
        $this->aDependency = $aDependency;
    }

    /**
     * @throws \Exception
     * @return boolean      true on success; otherwise, false.
     */
    public function load() {
        $this->___tryCheckingRequirements( $this->aDependency[ 'requirements' ] );
        return $this->___tryIncludingDependency( $this->aDependency[ 'name' ], $this->aDependency );
    }

        /**
         * @since  1.0.0
         * @todo support phar besides zip
         * @throws Exception
         * @return true true on success
         */
        private function ___tryIncludingDependency( $sName, array $aLibrary, $iAttempt=0 ) {

            if ( $iAttempt >= 2 ) {
                throw new Exception( "Warning: Failed to include the library, {$sName}." );
            }
            if ( $_sPath = $this->___getAutoLoaderPath( $sName, $aLibrary ) ) {
                include_once( $_sPath );
                return true;
            }
            // Download the file
            $_sArchivePath = __DIR__ . '/library/' . $sName . '/' . $sName . '.zip';
            $_bDownloaded  = $this->download( $aLibrary[ 'url' ], $_sArchivePath );
            if ( ! $_bDownloaded ) {
                throw new Exception( "Warning: the library, {$sName}, could not be downloaded." );
            }
            $this->unzip( $_sArchivePath );
            unlink( $_sArchivePath );

            // Perform the same routine again
            return $this->___tryIncludingDependency( $sName, $aLibrary, ++$iAttempt );

        }
            /**
             * @param  string $sName
             * @param  array $aLibrary
             * @since  1.0.0
             * @return string
             */
            private function ___getAutoLoaderPath( $sName, array $aLibrary ) {
                // Scan the 'library' directory and return the script path if found.
                $_sPlacementDirPath = $this->getPathFormatted( __DIR__ . '/library/' . $sName );
                $_oFileList         = new PHPClassMapGenerator(
                    $_sPlacementDirPath,        // doesn't matter to generate a simple file list
                    [ $_sPlacementDirPath ],    // scan dir
                    '',
                    array(
                        'do_in_constructor'  => false,
                        'output_buffer'      => false,
                        'structure'          => 'PATH',    // PATH (linear array holding found file path) or CLASS (consists of keys of class names and sub-elements as a value)
                        'search'             => [
                            'allowed_extensions'        => [ 'php' ],    // e.g. array( 'php', 'inc' )
                            'exclude_dir_paths'         => [],
                            'exclude_dir_names'         => [],
                            'exclude_dir_names_regex'   => [],
                            'exclude_file_names'        => [],
                            'exclude_file_names_regex'  => [],
                            'is_recursive'              => true,

                            // To pass to PHP Class Map Generator
                            'ignore_note_file_names'    => [ 'ignore-build-php.txt' ],
                            'exclude_substrings'	    => [],	     // e.g. array( '.min.js', '-dont-' )
                        ],
                    )
                );
                $_aFiles = $_oFileList->get();
                foreach( $_aFiles as $_iIndex => $_sPath ) {
                    if ( basename( $_sPath ) === $aLibrary[ 'autoloader' ] ) {
                        return $_sPath;
                    }
                }
                return '';

            }

        /**
         * @since   1.0.0
         * @return  true
         * @throws  Exception
         */
        private function ___tryCheckingRequirements( $aRequirement ) {
            foreach( $aRequirement[ 'functions' ] as $_sFunction ) {
                if ( ! function_exists( $_sFunction ) ) {
                    throw new Exception( "Warning: the function, {$_sFunction}, is missing. The program will not run properly." );
                }
            }
            foreach( $aRequirement[ 'classes' ] as $_sClassName ) {
                if ( ! function_exists( $_sClassName ) ) {
                    throw new Exception( "Warning: the class, {$_sClassName}, is missing. The program will not run properly." );
                }
            }
            return true;
        }

}