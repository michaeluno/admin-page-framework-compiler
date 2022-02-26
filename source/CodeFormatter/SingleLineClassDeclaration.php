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
        return $sCode;
    }

}