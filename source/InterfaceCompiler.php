<?php

namespace AdminPageFrameworkCompiler;


/**
 * Collections of utility methods which possibly can be used apart from the project.
 */
interface InterfaceCompiler {

    public function run();

    public function output( $sText, $bCarriageReturn=true );

}