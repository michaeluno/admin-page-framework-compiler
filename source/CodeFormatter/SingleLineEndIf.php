<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;

/**
 *
 * @since 1.1.0
 */
class SingleLineEndIf extends AbstractCodeFormatter {

    /**
     * @param  string $sCode
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        $_sPattern = '/'    // opening delimiter
            . '\s+(\Qendif;\E)([\s\r\n]+|$)'
            . '/'           // closing delimiter
            ;
        return preg_replace( $_sPattern, PHP_EOL . '$1' . PHP_EOL, $sCode );
    }

}