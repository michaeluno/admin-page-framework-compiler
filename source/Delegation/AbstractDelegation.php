<?php

namespace AdminPageFrameworkCompiler\Delegation;

use AdminPageFrameworkCompiler\Compiler;
use Exception;

abstract class AbstractDelegation implements InterfaceDelegation {

    /**
     * @var Compiler
     */
    public $oCompiler;

    /**
     * Sets up properties and hooks.
     */
    public function __construct( Compiler $oCompiler ) {
        $this->oCompiler = $oCompiler;
    }

    /**
     * @throws Exception
     */
    public function tryDoing() {
        throw new Exception( 'Override this method' );
    }

}