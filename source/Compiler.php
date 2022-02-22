<?php
/**
 * Admin Page Framework Compiler
 *
 * Compiles Admin Page Framework files including assets.
 *
 * @copyright 2022- (c) Michael Uno <https://github.com/michaeluno/admin-page-framework-compiler>
 * @license   MIT
 * @version   1.0.0
 */
namespace AdminPageFrameworkCompiler;

use \PHPClassMapGenerator\PHPClassMapGenerator;
use \PHPClassMapGenerator\Header\HeaderGenerator;
use Exception;

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

    public $sSourceDirPath = '';
    public $sDestinationDirPath = '';
    public $sTempDirPrefix = 'PHPCodeBeautifier_';      // for Windows, this prefix gets shortened to 3 characters like 'PHP'.
    public $aArguments = [];
    public $aDefaults  = [

        'output_buffer'     => true,
        'carriage_return'   => PHP_EOL,

        'header_class_name' => '',
        'header_class_path' => '',
        'header_type'       => 'DOCBLOCK',

        // 'character_encode'  => 'UTF-8',  // unused at the moment

        'exclude_classes'   => [],
        'css_heredoc_keys'  => [ 'CSSRULES' ],
        'js_heredoc_keys'   => [ 'JAVASCRIPTS' ],

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

    ];

    /**
     * @var array Dependency information to dynamically download when missing.
     */
    public $aDependencies = [
        'PHP_Beautifier'   => [
            'name'             => 'PHP_Beautifier',
            'type'             => 'zip', // @todo support phar
            'url'              => 'https://github.com/michaeluno/PHP_Beautifier/archive/0.1.17.1.zip',
            'requirements' => [
                'functions' => [
                    'token_get_all',
                ],
                'classes'   => [],
            ],
            'autoloader'   => 'Beautifier.php',    // the bootstrap file base name of the library that includes its components
        ],
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

            $_aPHPFiles = $_aAdditionalFiles = [];
            $this->tryListingFiles( $_sTempDirPath, $_aPHPFiles, $_aAdditionalFiles );

            $_aPHPFiles = $this->getInlineCSSAndJSMinified( $_aPHPFiles );
            
            $_aPHPFiles = $this->getInheritanceCombined( $_aPHPFiles );

            $_sHeaderComment = $this->getHeaderComment( $_aPHPFiles );

            $this->tryIncludingDependencies();

            $_aPHPFiles = $this->getPHPFilesBeautified( $_aPHPFiles, $_sHeaderComment );

            $this->tryCreatingFiles( array_merge( $_aPHPFiles, $_aAdditionalFiles ), $_sTempDirPath, $this->sDestinationDirPath );

        } catch ( Exception $oException ) {

            $this->deleteDir( $_sTempDirPath );
            $_sCode = $oException->getCode();   // error code
            exit( ( $_sCode ? $_sCode . ': ' : '' ) . $oException->getMessage() );

        }
        $this->deleteDir( $_sTempDirPath );
        $this->output( 'Compilation completed.' );

    }

    /**
     * @param  array $aPHPFiles
     * @return array
     * @since  1.0.0
     */
    public function getInheritanceCombined( array $aPHPFiles ) {
        if ( empty( $this->aArguments[ 'combine' ][ 'inheritance' ] ) ) {
            return $aPHPFiles;
        }
        $_oCombiner = new InheritanceCombiner( $aPHPFiles, $this->aArguments[ 'combine' ] );
        return $_oCombiner->get();
    }

    /**
     * @param  array $aPHPFiles
     * @return array
     * @since  1.0.0
     */
    public function getInlineCSSAndJSMinified( array $aPHPFiles ) {
        $this->output( 'Minifying Inline CSS and JavaScript...' );
        $this->output( 'CSS Here-doc Keys: ' . implode( ',', $this->aArguments[ 'css_heredoc_keys' ] ) );
        $this->output( 'JS Here-doc Keys: ' . implode( ',', $this->aArguments[ 'js_heredoc_keys' ] ) );
        $_oInlineCSSMinifier = new Minifier\InlineCSSMinifier( $this->aArguments[ 'css_heredoc_keys' ] );
        $_oInlineJSMinifier  = new Minifier\InlineJSMinifier( $this->aArguments[ 'js_heredoc_keys' ] );
        $_iProcessed = 0;
        foreach( $aPHPFiles as $_sBaseName => $_aFile ) {
            $_sPHPCode = $_aFile[ 'code' ];
            $_sPHPCode = $_oInlineCSSMinifier->get( $_sPHPCode );
            $_sPHPCode = $_oInlineJSMinifier->get( $_sPHPCode );
            if ( $_sPHPCode !== $_aFile[ 'code' ] ) {   // means minified
                $this->output( '.', false );
                $_iProcessed++;
            }
            $_aFile[ 'code' ] = $_sPHPCode;
            $aPHPFiles[ $_sBaseName ] = $_aFile;
        }
        if ( $_iProcessed ) {
            $this->output( '' );    // echo carriage return
            $this->output( sprintf( '%1$s files were modified.', $_iProcessed ) );
        }
        return $aPHPFiles;
    }

    /**
     * @throws Exception
     * @since  1.0.0
     */
    public function tryIncludingDependencies() {
        foreach( $this->aDependencies as $_sName => $_aLibrary  ) {
            $_oDependencyLoader = new DependencyLoader( $_aLibrary );
            $_bLoaded  = $_oDependencyLoader->load();
            $_sMessage = $_bLoaded
                ? 'Included the dependency: ' . $_aLibrary[ 'name' ]
                : 'Failed to include the dependency: ' . $_aLibrary[ 'name' ];
            $this->output( $_sMessage );
        }
    }

    /**
     * Writes contents to files.
     * @since  1.0.0
     * @throws Exception
     */
    public function tryCreatingFiles( array $aFiles, $sTempDirPath, $sDestinationDirPath ) {

        // Remove old files.
        $this->output( 'Deleting: ' . $sDestinationDirPath );
        $this->deleteDir( $sDestinationDirPath );
        if ( ! is_dir( $sDestinationDirPath ) && ! mkdir( $sDestinationDirPath, 0755 ) ) {
            throw new Exception( 'Failed to create the destination directory.' );
        }

        // Create files.
        $_bProcessed = false;
        foreach( $aFiles as $_asFile ) {

            // For PHP files, the element is formatted as an array.
            // If it is not formatted, just copy it.
            if ( is_scalar( $_asFile ) ) {
                if ( ! file_exists( $_asFile ) ) {
                    $this->output( 'The file does not exist: ' . $_asFile );
                    continue;
                }
                $this->output( '.', false );
                // $this->output( 'Copying: ' . basename( $_asFile ) . ' To: ' . $this->___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $_asFile ) );
                $this->copy(
                    $_asFile,
                    $this->___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $_asFile ),
                    0755,
                    $this->aArguments[ 'include' ]
                );
                $_bProcessed = true;
                continue;
            }
            // Otherwise, it is a PHP file
            $this->output( '.', false );
            $this->write( $this->___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $_asFile[ 'path' ] ), $_asFile[ 'code' ] );
            $_bProcessed = true;

        }
        if ( $_bProcessed ) {
            $this->output( '' );    // add a carriage return after dots.
        }

    }
        /**
         * @return string
         * @since  1.0.0
         */
        private function ___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $sFilePath ) {
            return $this->getAbsolutePathFromRelative( $sDestinationDirPath, $this->getRelativePath( $sTempDirPath, $sFilePath ) );
        }

    /**
     * @param  array  $aPHPFiles
     * @param  string $sHeaderComment
     * @return array
     * @since  1.0.0
     */
    public function getPHPFilesBeautified( array $aPHPFiles, $sHeaderComment ) {
        $this->output( 'Beautifying PHP code.' );
        $_aNew = array();
        foreach( $aPHPFiles as $_sFileBasename => $_aFile  ) {
            $_aFile[ 'code' ] = $this->getCodeBeautified( $_aFile[ 'code' ], $sHeaderComment );
            $_aNew[ $_sFileBasename ] = $_aFile;
            $this->output( '.', false );
        }
        $this->output( $this->aArguments[ 'carriage_return' ] );
        return $_aNew;
    }

    /**
     * @param  string $sCode          PHP code without the beginning <? php.
     * @param  string $sHeaderComment
     * @return string
     * @since  1.0.0
     */
    public function getCodeBeautified( $sCode, $sHeaderComment='' ) {

        try {

            $_oBeautifier = new \PHP_Beautifier();
            $_oBeautifier->setIndentChar(' ' );
            // $_oBeautifier->setIndentNumber( 4 );
            $_oBeautifier->setNewLine( "\n" );

            $sCode = '<?php ' . trim( $sCode ); // PHP_Beautifier needs the beginning < ?php notation. The passed code is already formatted and the notation is removed.
            $_oBeautifier->setInputString( $sCode );
            $_oBeautifier->process();

            $sCode = $_oBeautifier->get();
            $sCode = trim( $sCode ); // remove a trailing line-feed.

        }
        catch ( Exception $_oException ) {
            $this->output( $_oException->getCode() . ': ' . $_oException->getMessage() );
            return '';
        }

        // Add the file comment header
        if ( strlen( $sHeaderComment ) ) {
            $sCode = preg_replace(
                '/^<\?php\s+?/',        // search
                '<?php ' . PHP_EOL . $sHeaderComment . PHP_EOL, // replace
                $sCode  // subject
            );
        }

        // Somehow the ending enclosing braces gets 4-spaced indents. So fix them.
        return preg_replace( '/[\r\n]\K\s{4}\}$/', '}', $sCode );

    }

    /**
     * @return string
     * @since  1.0.0
     */
    public function getHeaderComment( array $aPHPFiles ) {

        $_sHeaderComment = '';
        try {
            $_oHeader        = new HeaderGenerator( $aPHPFiles, $this->aArguments );
            $_sHeaderComment = $_oHeader->get();
            if ( $_sHeaderComment ) {
                $this->output( 'File Header:' );
                $this->output( $_sHeaderComment );
            }
        } catch ( \ReflectionException $_oReflectionException ) {
            $this->output( $_oReflectionException->getCode() . ': ' . $_oReflectionException->getMessage() );
        }
        return trim( $_sHeaderComment );

    }

    /**
     * @param  string    $sTempDirPath
     * @throws Exception
     * @since  1.0.0
     */
    public function tryListingFiles( $sTempDirPath, array &$aPHPFiles, array &$aAdditionalFiles ) {

        $this->output( 'Searching files under the directory: ' . $this->sSourceDirPath );

        /**
         * @var array associative array consisting of class name keys with sub elements.
         * ```
         * [
         *  [AdminPageFramework_Form_View___FieldsetRow] => Array (
         *       [path] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow.php
         *       [code] => class AdminPageFramework_Form_View___FieldsetRow extends AdminPageFramework_Form_View___FieldsetTableRow { ...
         *       [dependency] => AdminPageFramework_Form_View___FieldsetTableRow
         *       [classes] => Array(
         *          [0] => AdminPageFramework_Form_View___FieldsetRow
         *       )
         *       [interfaces] => Array()
         *       [traits] => Array()
         *       [namespaces] => Array()
         *       [aliases] => Array()
         *  // continues
         * ]
         * ```
         */
        $aPHPFiles = $this->___getFileList( $sTempDirPath, $this->aArguments[ 'search' ], 'CLASS' );
        $this->output( sprintf( 'Found %1$s file(s).', count( $aPHPFiles ) ) );

        /**
         * Structure
         * @var array numerically indexed linear array holding found file paths.
         * [
         *      [0] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow.php
         *      [1] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow2.php
         *      // continues...
         * ]
         */
        if ( ! empty( $this->aArguments[ 'include' ][ 'allowed_extensions' ] ) ) {
            $aAdditionalFiles = $this->___getFileList( $sTempDirPath, $this->aArguments[ 'include' ], 'PATH' );
            $this->output( sprintf( 'Found %1$s additional file(s).', count( $aAdditionalFiles ) ) );
        }

        if ( empty( $aPHPFiles ) && empty( $aAdditionalFiles ) ) {
            throw new Exception( 'Could not find files.' );
        }

    }
        /**
         * @param  string $sTempDirPath
         * @param  array  $aSearchOptions
         * @param  string $sStructureType
         * @return array
         * @since  1.0.0
         */
        private function ___getFileList( $sTempDirPath, array $aSearchOptions, $sStructureType='CLASS' ) {
            $_oGenerator = new PHPClassMapGenerator(
                $sTempDirPath,  // doesn't matter
                [ $sTempDirPath ],
                '',
                array(
                    'do_in_constructor'  => false,
                    'output_buffer'      => false,
                    'structure'          => $sStructureType,    // PATH (linear array holding found file path) or CLASS (consists of keys of class names and sub-elements as a value)
                    'search'             => $aSearchOptions,
                )
            );
            return 'PATH' === $sStructureType
                ? $_oGenerator->get()
                : $_oGenerator->getItems();
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

}