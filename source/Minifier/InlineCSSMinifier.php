<?php

namespace AdminPageFrameworkCompiler\Minifier;

use MatthiasMullie\Minify;

class InlineCSSMinifier extends AbstractMinifier {

    public $sResourceType = 'css';

    /**
     * @param  string $sResourceCode CSS rules.
     * @return string
     * @since  1.0.0
     */
    public function getMinified( $sResourceCode ) {
        $_oMinifier = new Minify\CSS();
        $_oMinifier->add( $sResourceCode );
        return $_oMinifier->minify();
    }

}