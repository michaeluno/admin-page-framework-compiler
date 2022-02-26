<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;

/**
 *
 * @since 1.1.0
 */
class SingleLineOpeningCurlyBrackets extends AbstractCodeFormatter {
    /**
     * @param  string $sCode
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        $_sPattern = '/'    // opening delimiter
            . '[\r\n]{[\r\n\s]+'
            . '/';           // closing delimiter
        $_sReplace = ' {' . PHP_EOL // move it to the previous line with a white space
            . '    ';   // indent
        return preg_replace( $_sPattern, $_sReplace, $sCode );
    }
}