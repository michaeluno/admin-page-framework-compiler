<?php 
/**
 * Admin Page Framework Compiler
 *
 * Compiles Admin Page Framework files including assets.
 *
 * @copyright 2022- (c) Michael Uno <https://github.com/michaeluno/admin-page-framework-compiler>
 * @license   MIT
 * @version   1.1.0b01
 */
return array(
    "AdminPageFrameworkCompiler\\Compiler" => __DIR__ . "/Compiler.php", 
    "AdminPageFrameworkCompiler\\DependencyLoader" => __DIR__ . "/DependencyLoader.php", 
    "AdminPageFrameworkCompiler\\InheritanceCombiner" => __DIR__ . "/InheritanceCombiner.php", 
    "AdminPageFrameworkCompiler\\InterfaceCompiler" => __DIR__ . "/InterfaceCompiler.php", 
    "AdminPageFrameworkCompiler\\TraitDownload" => __DIR__ . "/TraitDownload.php", 
    "AdminPageFrameworkCompiler\\TraitFileSystemUtility" => __DIR__ . "/TraitFileSystemUtility.php", 
    "AdminPageFrameworkCompiler\\TraitLog" => __DIR__ . "/TraitLog.php", 
    "AdminPageFrameworkCompiler\\Delegation\\AbstractDelegation" => __DIR__ . "/Delegation/AbstractDelegation.php",
    "AdminPageFrameworkCompiler\\Delegation\\FileGenerator" => __DIR__ . "/Delegation/FileGenerator.php", 
    "AdminPageFrameworkCompiler\\Delegation\\FileList" => __DIR__ . "/Delegation/FileList.php", 
    "AdminPageFrameworkCompiler\\Delegation\\InterfaceDelegation" => __DIR__ . "/Delegation/InterfaceDelegation.php", 
    "AdminPageFrameworkCompiler\\FixerHelper\\VariableCodeProcessor" => __DIR__ . "/FixerHelper/VariableCodeProcessor.php", 
    "AdminPageFrameworkCompiler\\FixerHelper\\VirtualFileInfo" => __DIR__ . "/FixerHelper/VirtualFileInfo.php", 
    "AdminPageFrameworkCompiler\\FixerHelper\\VirtualVariableStreamWrapper" => __DIR__ . "/FixerHelper/VirtualVariableStreamWrapper.php", 
    "AdminPageFrameworkCompiler\\Minifier\\AbstractMinifier" => __DIR__ . "/Minifier/AbstractMinifier.php", 
    "AdminPageFrameworkCompiler\\Minifier\\InlineCSSMinifier" => __DIR__ . "/Minifier/InlineCSSMinifier.php", 
    "AdminPageFrameworkCompiler\\Minifier\\InlineJSMinifier" => __DIR__ . "/Minifier/InlineJSMinifier.php", 
);