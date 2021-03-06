<?php
/**
 * Admin Page Framework Compiler
 *
 * Compiles Admin Page Framework files including assets.
 *
 * @copyright 2022- (c) Michael Uno <https://github.com/michaeluno/admin-page-framework-compiler>
 * @license   MIT
 * @version   1.2.0
 */
namespace AdminPageFrameworkCompiler;

use PHPClassMapGenerator\PHPClassMapGenerator;
use PHPClassMapGenerator\Header\HeaderGenerator;
use Exception;

use AdminPageFrameworkCompiler\FixerHelper\VariableCodeProcessor;
include_once( __DIR__ . '/autoload.php' );

/**
 * Compiles Admin Page Framework files.
 *
 * ### Usage
 * ```
 * $oCompiler = new \AdminPageFrameworkCompiler\Compiler( $sSourceDirPath, $sDestinationDirPath );
 * $oCompiler->run();
 * ```
 */
class Compiler implements InterfaceCompiler {

    use TraitFileSystemUtility;
    use TraitLog;

    public $sSourceDirPath      = '';
    public $sDestinationDirPath = '';
    public $sTempDirPrefix      = 'APFCompiler_';      // for Windows, this prefix gets shortened to 3 characters like 'APF'.
    public $aArguments          = [];
    public $aDefaults           = [

        'output_buffer'     => true,
        'carriage_return'   => PHP_EOL,

        'comment_header'    => [
            'text'  => '',
            'path'  => '',
            'class' => '',
            // 'type'  => 'DOCBLOCK',
            // 'wrap'  => true,
        ],

        // 'character_encode'  => 'UTF-8',  // unused at the moment

        // 1.2.0 Excludes certain items from compiling while including those items.
        'excludes'          => [
            'classes'    => [],
            'paths'      => [],
            'file_names' => [],
        ],
        'css_heredoc_keys'  => [],
        'js_heredoc_keys'   => [],

        // Search options
        'search'    => [
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

        // Non-PHP files to include. The sub-arguments are the same as the `search` argument.
        'include'   => [
            'allowed_extensions'        => [],  // e.g. array( 'js', 'css' ) leave empty to disable the additinal files
            'exclude_dir_paths'         => [],
            'exclude_dir_names'         => [],
            'exclude_dir_names_regex'   => [],
            'exclude_file_names'        => [],
            'exclude_file_names_regex'  => [],
            'is_recursive'              => true,

            // To pass to PHP Class Map Generator
            'ignore_note_file_names'    => [ 'ignore-build-asset.txt' ],
            'exclude_substrings'	    => [],	     // e.g. array( '.min.js', '-dont-' )
        ],

        /**
         * Whether to combine files of PHP classes with hierarchical relationships in the same directory.
         *
         * For example, say there are A.php defining class A extends A_Base {} and A_Base.php defining A_Base {} in the same directory,
         * A.php will include the definition of A_Base in the same file and A_Base.php will be omitted.
         *
         * This helps to reduce time for loading files and improve performance when using auto-loader.
         * @since 1.0.0
         */
        'combine' => [
            'inheritance'       => true,
            'exclude_classes'   => [],
        ],

        /**
         * PHP CS Fixer options.
         * This compiler uses PHP CS Fixer so adding/setting custom rules is possible with this argument.
         */
        'php_cs_fixer' => [
            'config'    => '',  // configuration file path or an config object
            'rules'     => [],  // array of rules
        ],

        /**
         * Class names with name spaces or instances of those classes that extends the `AbstractCodeFormatter` class.
         */
        'code_formatters' => [],

    ];

    /**
     * @var array Dependency information to dynamically download when missing.
     */
    public $aDependencies = [
        // @deprecated  As using PHP CS Fixer
        // 'PHP_Beautifier'   => [
        //     'name'             => 'PHP_Beautifier',
        //     'type'             => 'zip', // @todo support phar
        //     'url'              => 'https://github.com/michaeluno/PHP_Beautifier/archive/0.1.17.1.zip',
        //     'requirements' => [
        //         'functions' => [
        //             'token_get_all',
        //         ],
        //         'classes'   => [],
        //     ],
        //     'autoloader'   => 'Beautifier.php',    // the bootstrap file base name of the library that includes its components
        // ],
    ];

    /**
     * Sets up properties and hooks.
     *
     * @param string $sSourceDirPath
     * @param string $sDestinationDirPath
     * @param array  $aArguments
     */
    public function __construct( $sSourceDirPath, $sDestinationDirPath, array $aArguments=[] ) {
        $this->sSourceDirPath      = $this->getPathFormatted( $sSourceDirPath );
        $this->sDestinationDirPath = $this->getPathFormatted( $sDestinationDirPath );
        $this->aArguments          = array_replace_recursive( $this->aDefaults, $aArguments );
    }

    /**
     * Performs the code diminishing process.
     */
    public function run() {

        $_sTempDirPath = '';
        try {

            $this->tryCheckingRequirements();

            $_sTempDirPath = $this->tryToGetTemporaryDirectoryCreated();
            $this->tryCopyingFilesToTemporaryDirectory( $_sTempDirPath );

            $_aPHPFiles      = $_aAdditionalFiles = [];
            $_oFileList      = new Delegation\FileList( $this, $_sTempDirPath, $_aPHPFiles, $_aAdditionalFiles );
            $_oFileList->tryDoing();

            $_oCodeFormatter = new Delegation\CodeFormatter( $this, $_sTempDirPath, $_aPHPFiles, $_aAdditionalFiles );
            $_aPHPFiles      = $_oCodeFormatter->get();

            $_oFileGenerator = new Delegation\FileGenerator( $this, $_sTempDirPath, $_aPHPFiles, $_aAdditionalFiles );
            $_oFileGenerator->tryDoing();


        } catch ( Exception $oException ) {

            $this->deleteDir( $_sTempDirPath );
            $_sCode = $oException->getCode();   // error code
            exit( ( $_sCode ? $_sCode . ': ' : '' ) . $oException->getMessage() );

        }
        $this->deleteDir( $_sTempDirPath );
        $this->output( 'Compilation completed.' );

    }

    /**
     * @throws Exception
     * @since  1.0.0
     * @deprecated No longer used. This was used to download and install PHP Beautifier.
     * But the function itself is not limited to the particular package so in the future it might be used again.
     */
    public function tryIncludingDependencies() {
        foreach( $this->aDependencies as $_aLibrary  ) {
            $_oDependencyLoader = new DependencyLoader( $_aLibrary );
            $_bLoaded  = $_oDependencyLoader->load();
            $_sMessage = $_bLoaded
                ? 'Included the dependency: ' . $_aLibrary[ 'name' ]
                : 'Failed to include the dependency: ' . $_aLibrary[ 'name' ];
            $this->output( $_sMessage );
        }
    }

    /**
     * @param  string $sTempDirPath
     * @throws Exception
     * @since  1.0.0
     */
    public function tryCopyingFilesToTemporaryDirectory( $sTempDirPath ) {
        if ( ! $this->copy( $this->sSourceDirPath, $sTempDirPath, 0755, $this->aArguments[ 'search' ] ) ) {
            throw new Exception( 'Failed to copy the directory: ' . $this->sSourceDirPath . ' to ' . $sTempDirPath );
        }
    }

    /**
     * @throws Exception
     * @return string Created temporary directory path.
     */
    public function tryToGetTemporaryDirectoryCreated() {
        $_sTempDirPath = $this->createTempDir( $this->sTempDirPrefix );
        if ( ! $_sTempDirPath ) {
            throw new Exception( 'Failed to create a temporary directory: ' . $_sTempDirPath );
        }
        return $_sTempDirPath;
    }

    /**
     * @throws Exception
     * @since  1.0.0
     */
    public function tryCheckingRequirements() {
        if ( ! is_dir( $this->sSourceDirPath ) ) {
            throw new Exception( 'The source directory path does not exist.' );
        }
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param  string   $sSourceDirPath    Source path
     * @param  string   $sDestDirPath      Destination path
     * @param  string   $iPermissions      New folder creation permissions
     * @param  array    $aOptions          Search options
     * @return boolean  Returns true on success, false on failure
     * @since  1.0.0
     */
    public function copy( $sSourceDirPath, $sDestDirPath, $iPermissions = 0755, array $aOptions=[] ) {

        // Check for symlinks
        if ( is_link( $sSourceDirPath ) ) {
            return symlink( readlink( $sSourceDirPath ), $sDestDirPath );
        }
        // Simple copy for a file
        if ( is_file( $sSourceDirPath ) ) {
            return $this->___copyFile( $sSourceDirPath, $sDestDirPath, $iPermissions, $aOptions );
        }
        // Make destination directory
        if ( ! is_dir( $sDestDirPath ) ) {
            if ( ! $this->___isInExcludeList( $sDestDirPath, $aOptions ) ) {
                mkdir( $sDestDirPath, $iPermissions );
            }
        }

        // Loop through the folder
        $dir = dir( $sSourceDirPath );
        while (false !== $entry = $dir->read()) {

            // Skip pointers
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            if ( $this->___isInExcludeList( $dir->path, $aOptions ) ) {
                continue;
            }

            // Deep copy directories
            $this->copy( "$sSourceDirPath/$entry", "$sDestDirPath/$entry", $iPermissions, $aOptions );
        }

        // Clean up
        $dir->close();
        return true;

    }
        /**
         * @param  string  $sSource
         * @param  string  $sDestination
         * @param  integer $iPermissions
         * @param  array   $aOptions
         * @return boolean
         * @since  1.0.0
         */
        private function ___copyFile( $sSource, $sDestination, $iPermissions, array $aOptions=array() ) {
            if ( ! file_exists( $sSource ) ) {
                return false;
            }
            // check it is in a class exclude list
            if ( $this->___isInClassExclusionList( $sSource, $aOptions ) ) {
                return false;
            }
            $_sPathDirDest = dirname( $sDestination );
            if ( ! file_exists( $_sPathDirDest ) ) {
                mkdir( $_sPathDirDest, $iPermissions, true );
            }
            return copy( $sSource, $sDestination );
        }
            /**
             * @param  string  $sSource
             * @param  array   $aOptions
             * @return boolean
             * @since  1.0.0
             */
            private function ___isInClassExclusionList( $sSource, $aOptions ) {
                $_sFileBaseName = basename( $sSource );
                if ( in_array( $_sFileBaseName, $aOptions[ 'exclude_file_names' ], true ) ) {
                    return true;
                }
                foreach( $aOptions[ 'exclude_file_names_regex' ] as $_sPattern ) {
                    if( false === @preg_match( $_sPattern, null ) ){ // Broken pattern
                        $this->output( 'The regex pattern for a file name is malformed: ' . $_sPattern );
                        continue;
                    }
                    if ( preg_match( $_sPattern, $_sFileBaseName ) ) {
                        return true;
                    }
                }
                return false;
            }

        /**
         * @param  string  $sDirPath
         * @param  array   $aOptions
         * @return boolean
         * @since  1.0.0
         */
        private function ___isInExcludeList( $sDirPath, array $aOptions=[] ) {

            $sDirPath          = $this->getPathFormatted( $sDirPath );
            $_aExcludeDirPaths = isset( $aOptions[ 'exclude_dir_paths' ] )
                ? ( array ) $aOptions[ 'exclude_dir_paths' ]
                : [];
            $_aExcludeDirNames = isset( $aOptions[ 'exclude_dir_names' ] )
                ? ( array ) $aOptions[ 'exclude_dir_names' ]
                : [];

            if ( in_array( $sDirPath, $_aExcludeDirPaths, true ) ) {
                return true;
            }
            $_sDirBaseName = pathinfo( $sDirPath, PATHINFO_BASENAME );
            if ( in_array( $_sDirBaseName, $_aExcludeDirNames, true ) ) {
                return true;
            }
            foreach( $aOptions[ 'exclude_dir_names_regex' ] as $_sPattern ) {
                if ( false === @preg_match( $_sPattern, null ) ) { // Broken pattern
                    $this->output( 'The regex pattern for directory name is malformed: ' . $_sPattern );
                    continue;
                }
                if ( preg_match( $_sPattern, $_sDirBaseName ) ) {
                    return true;
                }
            }
            return false;

        }

    /**
     * Echoes the passed string.
     *
     * @since 1.0.0
     */
    public function output( $sText, $bCarriageReturn=true ) {
        if ( ! $this->aArguments[ 'output_buffer' ] ) {
            return;
        }
        echo $sText
             . ( $bCarriageReturn ? $this->aArguments[ 'carriage_return' ] : '' );
    }

    /**
     * @param $sMessage
     */
    public function log( $sMessage ) {
        $this->log( $sMessage, __DIR__ . dirname( $this->sDestinationDirPath ) . '/apf-compile.log' );
    }
    /**
     * @param $sMessage
     */
    public function logError( $sMessage ) {
        $this->log( $sMessage, __DIR__ . dirname( $this->sDestinationDirPath ) . '/apf-compile-error.log' );
    }

}