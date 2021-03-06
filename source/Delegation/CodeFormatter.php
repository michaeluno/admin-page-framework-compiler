<?php

namespace AdminPageFrameworkCompiler\Delegation;

use AdminPageFrameworkCompiler\CodeFormatter\AbstractCodeFormatter;
use AdminPageFrameworkCompiler\CodeFormatter\PHPCSFixer;
use AdminPageFrameworkCompiler\Compiler;
use AdminPageFrameworkCompiler\InheritanceCombiner;
use AdminPageFrameworkCompiler\TraitFileSystemUtility;
use AdminPageFrameworkCompiler\Minifier\InlineCSSMinifier;
use AdminPageFrameworkCompiler\Minifier\InlineJSMinifier;
use \Exception;
use PHPClassMapGenerator\Header\HeaderGenerator;

class CodeFormatter extends AbstractDelegation {

    use TraitFileSystemUtility;
    
    public $sTempDirPath = '';

    public $aPHPFiles = [];

    public $aAdditionalFiles = [];

    /**
     * Sets up properties.
     */
    public function __construct( Compiler $oCompiler, $sTempDirPath, array $aPHPFiles, array $aAdditionalFiles ) {
        parent::__construct( $oCompiler );
        $this->sTempDirPath     = $sTempDirPath;
        $this->aPHPFiles        = $aPHPFiles;
        $this->aAdditionalFiles = $aAdditionalFiles;
    }

    public function get() {
        $_aPHPFiles      = $this->___getInheritanceCombined( $this->aPHPFiles );
        $_sHeaderComment = $this->___getHeaderComment( $_aPHPFiles );
        $_aPHPFiles      = $this->___getPHPCodeBeautified( $_aPHPFiles, $_sHeaderComment );
        return $this->___getInlineCSSAndJSMinified( $_aPHPFiles );
    }

    /**
     * @return string
     * @since  1.0.0
     */
    private function ___getHeaderComment( array $aPHPFiles ) {

        $_sHeaderComment = '';
        try {
            $_oHeader        = new HeaderGenerator( $aPHPFiles, $this->oCompiler->aArguments[ 'comment_header' ] );
            $_sHeaderComment = $_oHeader->get();
            if ( $_sHeaderComment ) {
                $this->oCompiler->output( 'File Header:' );
                $this->oCompiler->output( $_sHeaderComment );
            }
        } catch ( \ReflectionException $_oReflectionException ) {
            $this->oCompiler->output( $_oReflectionException->getCode() . ': ' . $_oReflectionException->getMessage() );
        }
        return trim( $_sHeaderComment );

    }    
    
    /**
     * @param  array $aPHPFiles
     * @return array
     * @since  1.0.0
     */
    private function ___getInheritanceCombined( array $aPHPFiles ) {
        if ( empty( $this->oCompiler->aArguments[ 'combine' ][ 'inheritance' ] ) ) {
            return $aPHPFiles;
        }
        $_oCombiner = new InheritanceCombiner( $aPHPFiles, $this->___getCombineArguments() );
        return $_oCombiner->get();
    }
        /**
         * @return array
         * @since  1.1.1
         */
        private function ___getCombineArguments() {
            $_aArguments             = empty( $this->oCompiler->aArguments[ 'combine' ] ) || ! is_array( $this->oCompiler->aArguments[ 'combine' ] )
                ? [  'exclude_classes' => [] ]
                : $this->oCompiler->aArguments[ 'combine' ];
            $_aExcludeClassesCombine = empty( $_aArguments[ 'exclude_classes' ] ) || ! is_array( $_aArguments[ 'exclude_classes' ] )
                ? []
                : $_aArguments[ 'exclude_classes' ];
            $_aExcludeClasses        = empty( $this->oCompiler->aArguments[ 'excludes' ][ 'classes' ] ) || ! is_array( $this->oCompiler->aArguments[ 'excludes' ][ 'classes' ] )
                ? []
                : $this->oCompiler->aArguments[ 'excludes' ][ 'classes' ];
            $_aArguments[ 'exclude_classes' ] = array_unique( array_merge( $_aExcludeClassesCombine, $_aExcludeClasses ) );
            return $_aArguments;
        }

    /**
     * @param  array  $aPHPFiles
     * @param  string $sHeaderComment
     * @return array
     * @since  1.0.0
     */
    private function ___getPHPCodeBeautified( array $aPHPFiles, $sHeaderComment ) {
        $sHeaderComment = false !== strpos( $sHeaderComment, '/*' )
            ? HeaderGenerator::getMultiLineCommentUnwrapped( $sHeaderComment )
            : $sHeaderComment;
        $this->oCompiler->output( 'Beautifying PHP code.' );
        $_aNew = array();
        /**
         * @var array $_aFile
         * ### Structure
         * ```
         * [
         *   'path'         => '...InlineJSMinifier.php',
         *   'dependency'   => 'AbstractMinifier',
         *   'classes'      => [
         *      0 => 'AdminPageFrameworkCompiler\Minifier\InlineJSMinifier'
         *   ],
         *   'interfaces'   => [],
         *   'traits'       => [],
         *   'namespaces'   => [
         *      0 => 'AdminPageFrameworkCompiler\Minifier\',
         *   ],
         *   'aliases'      => [],
         * ]
         * ```
         */
        foreach( $aPHPFiles as $_sFileBasenameWOExt => $_aFile  ) {
            $_aFile[ 'code' ] = $this->___isExcluded( $_sFileBasenameWOExt, $_aFile )
                ? file_get_contents( $_aFile[ 'path' ] )
                : $this->getCodeBeautified( $_aFile[ 'code' ], $sHeaderComment );
            $_aNew[ $_sFileBasenameWOExt ] = $_aFile;
            $this->oCompiler->output( '.', false );
        }
        $this->oCompiler->output( $this->oCompiler->aArguments[ 'carriage_return' ] );
        return $_aNew;
    }
        /**
         * @since  1.2.0
         * @param  string   $sFileBaseName
         * @param  array    $aFile
         * @return boolean
         */
        private function ___isExcluded( $sFileBaseName, array $aFile ) {
            if ( in_array( $aFile[ 'path' ], ( array ) $this->oCompiler->aArguments[ 'excludes' ][ 'paths' ] ) ) {
                return true;
            }
            $_sFileBaseName = basename( $aFile[ 'path' ] ); // with its file extension
            if ( in_array( $_sFileBaseName, ( array ) $this->oCompiler->aArguments[ 'excludes' ][ 'file_names' ], true ) ) {
                return true;
            }
            foreach( $aFile[ 'classes' ] as $_sClassName ) {
                if ( in_array( $_sClassName, ( array ) $this->oCompiler->aArguments[ 'excludes' ][ 'classes' ], true ) ) {
                    return true;
                }
            }
            return false;
        }
    
    /**
     * @param  string $sCode          PHP code without the beginning <? php.
     * @param  string $sHeaderComment
     * @return string
     * @since  1.0.0
     */
    public function getCodeBeautified( $sCode, $sHeaderComment='' ) {

        try {
            $_oFormatter = new PHPCSFixer( $this->oCompiler->aArguments, $sHeaderComment );
            $sCode       = $_oFormatter->get( $sCode );
            $_aClasses   = [
                'AdminPageFrameworkCompiler\CodeFormatter\SingleLineClassDeclaration',
                'AdminPageFrameworkCompiler\CodeFormatter\SingleLineEndIf',
                'AdminPageFrameworkCompiler\CodeFormatter\EmptyMethods',
                'AdminPageFrameworkCompiler\CodeFormatter\SingleLineOpeningCurlyBrackets',
            ];
            $_aClasses   = array_unique( array_merge( $this->oCompiler->aArguments[ 'code_formatters' ], $_aClasses ) );
            foreach( $_aClasses as $_sClassNameOrInstance ) {
                if ( is_string( $_sClassNameOrInstance ) ) {
                    $_oFormatter = new $_sClassNameOrInstance( $this->oCompiler->aArguments );
                    $sCode       = $_oFormatter->get( $sCode );
                }
                if ( $_sClassNameOrInstance instanceof AbstractCodeFormatter ) {
                    $_sClassNameOrInstance->setArguments( $this->oCompiler->aArguments );
                    $sCode       = $_sClassNameOrInstance->get( $sCode );
                }
            }
        }
        catch ( Exception $_oException ) {
            $this->oCompiler->output( $_oException->getCode() . ': ' . $_oException->getMessage() );
            return '';
        }

        return $sCode;
        
        // @deprecated 1.1.0    No longer needed as PHP Beautifier is not used any more.
        // Somehow the ending enclosing braces gets 4-spaced indents. So fix them.
        // return preg_replace( '/[\r\n]\K\s{4}}$/', '}', $sCode );

    }
    
    /**
     * @param  array $aPHPFiles
     * @return array
     * @since  1.0.0
     */
    private function ___getInlineCSSAndJSMinified( array $aPHPFiles ) {
        $this->oCompiler->output( 'Minifying Inline CSS and JavaScript...' );
        $this->oCompiler->output( 'CSS Here-doc Keys: ' . implode( ',', $this->oCompiler->aArguments[ 'css_heredoc_keys' ] ) );
        $this->oCompiler->output( 'JS Here-doc Keys: ' . implode( ',', $this->oCompiler->aArguments[ 'js_heredoc_keys' ] ) );
        $_oInlineCSSMinifier = new InlineCSSMinifier( $this->oCompiler->aArguments[ 'css_heredoc_keys' ] );
        $_oInlineJSMinifier  = new InlineJSMinifier( $this->oCompiler->aArguments[ 'js_heredoc_keys' ] );
        $_iProcessed = 0;
        foreach( $aPHPFiles as $_sBaseName => $_aFile ) {
            $_sPHPCode = $_aFile[ 'code' ];
            $_sPHPCode = $_oInlineCSSMinifier->get( $_sPHPCode );
            $_sPHPCode = $_oInlineJSMinifier->get( $_sPHPCode );
            if ( $_sPHPCode !== $_aFile[ 'code' ] ) {   // means minified
                $this->oCompiler->output( '.', false );
                $_iProcessed++;
            }
            $_aFile[ 'code' ] = $_sPHPCode;
            $aPHPFiles[ $_sBaseName ] = $_aFile;
        }
        if ( $_iProcessed ) {
            $this->oCompiler->output( '' );    // echo carriage return
            $this->oCompiler->output( sprintf( 'Here-doc resources of %1$s files code were minified.', $_iProcessed ) );
        }
        return $aPHPFiles;
    }    

}