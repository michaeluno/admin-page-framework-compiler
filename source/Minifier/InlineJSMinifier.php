<?php

namespace PHPCodeBeautifier\Minifier;

include_once( __DIR__ . '/AbstractMinifier.php' );

class InlineJSMinifier extends AbstractMinifier {

    public $sResourceType = 'js';

}