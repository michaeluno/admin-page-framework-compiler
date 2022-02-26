<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;

/**
 *
 * @since 1.1.0
 */
class SingleLineClassDeclaration extends AbstractCodeFormatter {

    /**
     * @param  string $sCode
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        $_sPattern = '/'    // opening delimiter
            // . '[^\S\r\n]\K' // a white space is required to match
            . '([\s\r\n])' // a white space or a line break is required to match
            . '('           // $1
                . '(((abstract|static|final)\s+)+)?'    // scope and visibility keywords
                . 'class\s+[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*+' // class + {class name}
                // not implemented at the moment as there are multiple interfaces and return types in PHP 8 can be set and these will be really complicated to match including supporting multiple lines
                //  . '(\s+(extends|implements)\s+[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*+)?'
            . ')'
            // . '[\s\r\n]+' . '{'  // not implemented
            . '/'  // closing delimiter
            ;
        return preg_replace( $_sPattern, PHP_EOL . '$2', $sCode );
    }

}