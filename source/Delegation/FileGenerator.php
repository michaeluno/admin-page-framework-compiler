<?php

namespace AdminPageFrameworkCompiler\Delegation;

use AdminPageFrameworkCompiler\Compiler;
use AdminPageFrameworkCompiler\TraitFileSystemUtility;
use \Exception;

class FileGenerator extends AbstractDelegation {

    use TraitFileSystemUtility;
    
    public $sTempDirPath = '';

    public $aPHPFiles = [];

    public $aAdditionalFiles = [];

    /**
     * Sets up properties.
     */
    public function __construct( Compiler $oCompiler, $sTempDirPath, array $aPHPFiles, array $aAdditionalFiles ) {
        parent::__construct( $oCompiler );
        $this->sTempDirPath     = $sTempDirPath;
        $this->aPHPFiles        = $aPHPFiles;
        $this->aAdditionalFiles = $aAdditionalFiles;
    }

    /**
     *
     * @throws Exception
     */
    public function tryDoing() {
        $this->___tryCreatingFiles( 
            array_merge( $this->aPHPFiles, $this->aAdditionalFiles ), 
            $this->sTempDirPath,
            $this->oCompiler->sDestinationDirPath
        );
    }

    /**
     * Writes contents to files.
     * @since  1.0.0
     * @throws Exception
     */
    private function ___tryCreatingFiles( array $aFiles, $sTempDirPath, $sDestinationDirPath ) {

        // Remove old files.
        $this->oCompiler->output( 'Deleting: ' . $sDestinationDirPath );
        $this->deleteDir( $sDestinationDirPath );
        if ( ! is_dir( $sDestinationDirPath ) && ! mkdir( $sDestinationDirPath, 0755 ) ) {
            throw new Exception( 'Failed to create the destination directory.' );
        }

        // Create files.
        $_bProcessed = false;
        foreach( $aFiles as $_asFile ) {

            // For PHP files, the element is formatted as an array.
            // If it is not formatted, just copy it.
            if ( is_scalar( $_asFile ) ) {
                if ( ! file_exists( $_asFile ) ) {
                    $this->oCompiler->output( 'The file does not exist: ' . $_asFile );
                    continue;
                }
                $this->oCompiler->output( '.', false );
                $this->oCompiler->copy(
                    $_asFile,
                    $this->___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $_asFile ),
                    0755,
                    $this->oCompiler->aArguments[ 'include' ]
                );
                $_bProcessed = true;
                continue;
            }
            // Otherwise, it is a PHP file
            $this->oCompiler->output( '.', false );
            $this->write( $this->___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $_asFile[ 'path' ] ), $_asFile[ 'code' ] );
            $_bProcessed = true;

        }
        if ( $_bProcessed ) {
            $this->oCompiler->output( '' );    // add a carriage return after dots.
        }

    }
        /**
         * @return string
         * @since  1.0.0
         */
        private function ___getDestinationFilePathFromTempPath( $sDestinationDirPath, $sTempDirPath, $sFilePath ) {
            return $this->getAbsolutePathFromRelative( $sDestinationDirPath, $this->getRelativePath( $sTempDirPath, $sFilePath ) );
        }    

}