<?php 
/**
 * Admin Page Framework Compiler
 *
 * Compiles Admin Page Framework files including assets.
 *
 * @copyright 2022- (c) Michael Uno <https://github.com/michaeluno/admin-page-framework-compiler>
 * @license   MIT
 * @version   1.0.0
 */
return array(
    "AdminPageFrameworkCompiler\\Compiler" => __DIR__ . "/Compiler.php", 
    "AdminPageFrameworkCompiler\\DependencyLoader" => __DIR__ . "/DependencyLoader.php", 
    "AdminPageFrameworkCompiler\\InheritanceCombiner" => __DIR__ . "/InheritanceCombiner.php", 
    "AdminPageFrameworkCompiler\\InterfaceCompiler" => __DIR__ . "/InterfaceCompiler.php", 
    "AdminPageFrameworkCompiler\\TraitDownload" => __DIR__ . "/TraitDownload.php", 
    "AdminPageFrameworkCompiler\\TraitFileSystemUtility" => __DIR__ . "/TraitFileSystemUtility.php", 
    "AdminPageFrameworkCompiler\\Minifier\\AbstractMinifier" => __DIR__ . "/Minifier/AbstractMinifier.php", 
    "AdminPageFrameworkCompiler\\Minifier\\InlineCSSMinifier" => __DIR__ . "/Minifier/InlineCSSMinifier.php", 
    "AdminPageFrameworkCompiler\\Minifier\\InlineJSMinifier" => __DIR__ . "/Minifier/InlineJSMinifier.php", 
);