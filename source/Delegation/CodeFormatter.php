<?php

namespace AdminPageFrameworkCompiler\Delegation;

use AdminPageFrameworkCompiler\Compiler;
use AdminPageFrameworkCompiler\FixerHelper\VariableCodeProcessor;
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
        $_oCombiner = new InheritanceCombiner( $aPHPFiles, $this->oCompiler->aArguments[ 'combine' ] );
        return $_oCombiner->get();
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
        foreach( $aPHPFiles as $_sFileBasename => $_aFile  ) {
            $_aFile[ 'code' ] = $this->getCodeBeautified( $_aFile[ 'code' ], $sHeaderComment );
            $_aNew[ $_sFileBasename ] = $_aFile;
            $this->oCompiler->output( '.', false );
        }
        $this->oCompiler->output( $this->oCompiler->aArguments[ 'carriage_return' ] );
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
            $_oFormatter = new VariableCodeProcessor( empty( $this->oCompiler->aArguments[ 'php_cs_fixer' ][ 'config' ] ) ? null : $this->oCompiler->aArguments[ 'php_cs_fixer' ][ 'config' ] );
            $_oFormatter->addRules([
                'header_comment' => [
                    'header'        => $sHeaderComment,
                    'comment_type'  => 'comment', // 'PHPDoc',
                    'location'      => 'after_open',
                    'separate'      => 'bottom'
                ],
            ]);
            if ( ! empty( $this->oCompiler->aArguments[ 'php_cs_fixer' ][ 'rules' ] ) && is_array( $this->oCompiler->aArguments[ 'php_cs_fixer' ][ 'rules' ] ) ) {
                $_oFormatter->addRules( $this->oCompiler->aArguments[ 'php_cs_fixer' ][ 'rules' ] );
            }
            $sCode = '<?php ' . trim( $sCode );
            $sCode = $_oFormatter->get( $sCode );
        }
        catch ( Exception $_oException ) {
            $this->oCompiler->output( $_oException->getCode() . ': ' . $_oException->getMessage() );
            return '';
        }

        // Somehow the ending enclosing braces gets 4-spaced indents. So fix them.
        return preg_replace( '/[\r\n]\K\s{4}}$/', '}', $sCode );

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