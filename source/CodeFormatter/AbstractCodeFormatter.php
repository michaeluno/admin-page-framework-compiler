<?php

namespace AdminPageFrameworkCompiler\CodeFormatter;

/**
 * @since 1.1.0
 */
abstract class AbstractCodeFormatter {

    /**
     * @since 1.1.0
     * @var   array
     */
    public $aArguments = [];

    /**
     * Sets up properties.
     * @since 1.1.0
     */
    public function __construct( array $aArguments ) {
        $this->aArguments = $aArguments;
    }

    /**
     * @param array $aArguments
     * @since 1.1.0
     */
    public function setArguments( array $aArguments ) {
        $this->aArguments = $aArguments;
    }
    
    /**
     * @return string
     * @since  1.1.0
     */
    public function get( $sCode ) {
        return $sCode;
    }

}