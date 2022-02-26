<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;

/**
 *
 * @since 1.1.0
 */
class EmptyMethods extends AbstractCodeFormatter {
    /**
     * @param  string $sCode
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        $_sPattern = '/'    // opening delimiter
            . '{[\s\r\n]+}'
            . '/';           // closing delimiter
        return preg_replace( $_sPattern, '{}', $sCode );
    }
}