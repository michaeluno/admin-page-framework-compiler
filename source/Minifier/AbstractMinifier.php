<?php

namespace AdminPageFrameworkCompiler\Minifier;

abstract class AbstractMinifier {

    /**
     * @var string
     */
    public $sResourceType = ''; // `js` or `css`

    /**
     * @var array
     */
    public $aHereDocKeys = [];

    /**
     * Sets up properties.
     */
    public function __construct( array $aHereDocKeys=[] ) {
        $this->aHereDocKeys = $aHereDocKeys;
    }

    /**
     * Minifies CSS Rules in variables defined with the PHP heredoc syntax.
     * @param  string $sPHPCode
     * @return string
     */
    public function get( $sPHPCode ) {
        foreach( $this->aHereDocKeys as $_sHereDocKey ) {
            $sPHPCode = preg_replace_callback(
                "/\s?+\K(<<<{$_sHereDocKey}[\r\n])(.+?)([\r\n]{$_sHereDocKey};(\s+)?[\r\n])/ms",   // needle
                array( $this, '___replyToGetMinified' ),  // callback
                $sPHPCode,                                // haystack
                -1  // limit -1 for no limit
            );
        }
        return $sPHPCode;
    }

    /**
     * The callback function to modify the CSS rules defined in heredoc variable assignments.
     *
     * @since    1.0.0
     * @callback preg_replace_callback()
     * @return   string
     */
    private function ___replyToGetMinified( $aMatch ) {
        if ( ! isset( $aMatch[ 1 ], $aMatch[ 2 ], $aMatch[ 3 ] ) ) {
            return $aMatch[ 0 ];
        }
        $_sCSSRules = $aMatch[ 2 ];
        // this library produces some unwanted warnings so disable it with @.
        return '"' . @$this->getMinified( $_sCSSRules ) . '"; ';
    }

    /**
     * @param  string $sResourceCode    CSS rules.
     * @return string
     * @since  1.0.0
     */
    public function getMinified( $sResourceCode ) {
        $_oMinifier      = \Asika\Minifier\MinifierFactory::create( strtolower( $this->sResourceType ) );
        $_oMinifier->addContent( $sResourceCode );
        return $_oMinifier->minify();
    }

}