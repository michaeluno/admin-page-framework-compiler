<?php

namespace AdminPageFrameworkCompiler\Minifier;

include_once( __DIR__ . '/AbstractMinifier.php' );

use MatthiasMullie\Minify;

class InlineJSMinifier extends AbstractMinifier {

    public $sResourceType = 'js';

    /**
     * @param  string $sResourceCode CSS rules.
     * @return string
     * @since  1.0.0
     */
    public function getMinified( $sResourceCode ) {
        $_oMinifier = new Minify\JS();
        $_oMinifier->add( $sResourceCode );
        return $_oMinifier->minify();
    }

}