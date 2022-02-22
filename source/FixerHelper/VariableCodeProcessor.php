<?php
namespace AdminPageFrameworkCompiler\FixerHelper;

use PhpCsFixer\Config;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\ToolInfo;
use AdminPageFrameworkCompiler\FixerHelper\VirtualVariableStreamWrapper;

include_once( __DIR__ . '/VirtualVariableStreamWrapper.php' );

class VariableCodeProcessor {

    public $sScheme = 'apfcompiler';
    public $osConfig;

    /**
     * @param string|Config|null $osConfig A path to configuration file or a config object
     */
    public function __construct( $osConfig = null ) {
        $this->osConfig = is_string( $osConfig ) && file_exists( $osConfig )
            ? include( $osConfig )
            : ( ( $osConfig instanceof Config ) ? $osConfig : new Config( 'default' ) );
        if ( in_array( $this->sScheme, stream_get_wrappers(), true ) ) {
            stream_wrapper_unregister( $this->sScheme );
        }
        stream_wrapper_register( $this->sScheme, 'AdminPageFrameworkCompiler\FixerHelper\VirtualVariableStreamWrapper' );
    }

    public function setConfig( $osConfig ) {
        $this->osConfig = $osConfig;
    }

    // public function setRules( array $aRules ) {
    //     $this->osConfig->setRules( $aRules );
    // }
    public function addRules( array $aRules ) {
        $_aRules = $this->osConfig->getRules();
        $this->osConfig->setRules( $aRules + $_aRules );
    }

    public function getFromPath( $phpFilePath ) {
        return $this->get( file_get_contents( $phpFilePath ) );
    }

    public function get( $sCodeToFix ) {
        $sPathVirtual = $this->sScheme . '://' . uniqid() . '.virtual.php'; // .virtual.php is checked with VariableStream
        file_put_contents( $sPathVirtual, $sCodeToFix );
        $options = [
            // 'config' => $passedConfig,
            'dry-run'           => false,
            // 'rules' => $passedRules,
            'path'              => [ $sPathVirtual ],
            'using-cache'       => 'no',
            'stop-on-violation' => true,
        ];
        $_oResolver = new ConfigurationResolver(
            ( $this->osConfig instanceof Config ) ? $this->osConfig : new Config( 'default' ),
            $options,
            '',
            new ToolInfo()
        );

        $_oRunner = new Runner(
            new \ArrayIterator( [ new VirtualFileInfo( $sPathVirtual ) ] ),
            $_oResolver->getFixers(),
            $_oResolver->getDiffer(),
            null,
            new ErrorsManager(),
            $_oResolver->getLinter(),
            $_oResolver->isDryRun(),
            $_oResolver->getCacheManager(),
            $_oResolver->getDirectory(),
            $_oResolver->shouldStopOnViolation()
        );
        $_oRunner->fix();
        $_sFixedCode = file_get_contents( $sPathVirtual );
        unlink( $sPathVirtual );
        return $_sFixedCode;

    }

}