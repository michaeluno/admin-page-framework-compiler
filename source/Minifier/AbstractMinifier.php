<?php

namespace AdminPageFrameworkCompiler\Minifier;

abstract class AbstractMinifier {

    /**
     * The heredoc or nowdoc keywords.
     *
     * ```
     * <<<EOL
     * Some text
     * Another line.
     * EOL;
     *
     * <<<`TEXT`
     * Some text
     * Another line.
     * TEXT;
     * ```
     * In the above code, `EOL` is the heredoc keyword and `TEXT` is the nowdoc keyword.
     *
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
            if ( ! strlen( $_sHereDocKey ) ) {
                continue;
            }
            $_sPattern = "/"    // delimiter
                . "\s?+\K"
                . "(<<<(\Q{$_sHereDocKey}\E|\Q'{$_sHereDocKey}'\E)[\r\n])"  // $1, $2
                . "(.+?)"       // Here/now doc content     // $3
                . "(" // $4
                    . "[\r\n]{$_sHereDocKey};"
                    . "(\s+)?[\r\n]"    // $5
                . ")"
                . "/ms";    // delimiter and regex modifieres
            $sPHPCode = preg_replace_callback( $_sPattern, array( $this, '___replyToGetMinified' ), $sPHPCode, -1 );
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
        if ( ! isset( $aMatch[ 2 ], $aMatch[ 3 ], $aMatch[ 4 ] ) ) {
            return $aMatch[ 0 ];
        }
        $_sCode = $aMatch[ 3 ];
        return $aMatch[ 1 ] . $this->getMinified( $_sCode ) . $aMatch[ 4 ];
    }

    /**
     * @param  string $sResourceCode The resource code to minify.
     * @return string
     * @since  1.0.0
     */
    public function getMinified( $sResourceCode ) {
        return $sResourceCode;
    }

}