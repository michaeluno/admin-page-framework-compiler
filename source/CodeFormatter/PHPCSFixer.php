<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;


use AdminPageFrameworkCompiler\FixerHelper\VariableCodeProcessor;

/**
 * @since 1.1.0
 */
class PHPCSFixer extends AbstractCodeFormatter {

    /**
     * @var   string 
     * @since 1.1.0
     */
    public $sHeaderComment = '';
    
    /**
     * Sets up properties and hooks.
     */
    public function __construct( array $aArguments, $sHeaderComment ) {
        parent::__construct( $aArguments );
        $this->sHeaderComment = $sHeaderComment;
    }
    
    /**
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        $_oFormatter = new VariableCodeProcessor( empty( $this->aArguments[ 'php_cs_fixer' ][ 'config' ] ) ? null : $this->aArguments[ 'php_cs_fixer' ][ 'config' ] );
        $_oFormatter->addRules([
            'header_comment' => [
                'header'        => $this->sHeaderComment,
                'comment_type'  => 'comment', // 'PHPDoc',
                'location'      => 'after_open',
                'separate'      => 'bottom'
            ],
        ]);
        if ( ! empty( $this->aArguments[ 'php_cs_fixer' ][ 'rules' ] ) && is_array( $this->aArguments[ 'php_cs_fixer' ][ 'rules' ] ) ) {
            $_oFormatter->addRules( $this->aArguments[ 'php_cs_fixer' ][ 'rules' ] );
        }
        return $_oFormatter->get( '<?php ' . trim( $sCode ) );
    }

}