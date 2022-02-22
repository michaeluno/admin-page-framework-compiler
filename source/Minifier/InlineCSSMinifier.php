<?php

namespace AdminPageFrameworkCompiler\Minifier;

include_once( __DIR__ . '/AbstractMinifier.php' );
class InlineCSSMinifier extends AbstractMinifier {

    public $sResourceType = 'css';

    /**
     * @param  string $sResourceCode    CSS rules.
     * @return string
     * @since  1.0.0
     */
    public function getMinified( $sResourceCode ) {
        return $sResourceCode;
    }

}