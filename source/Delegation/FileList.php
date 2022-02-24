<?php

namespace AdminPageFrameworkCompiler\Delegation;

use AdminPageFrameworkCompiler\Compiler;
use PHPClassMapGenerator\PHPClassMapGenerator;
use \Exception;

class FileList extends AbstractDelegation {

    public $sTempDirPath = '';

    public $aPHPFiles = [];

    public $aAdditionalFiles = [];

    /**
     * Sets up properties.
     */
    public function __construct( Compiler $oCompiler, $sTempDirPath, array &$aPHPFiles, array &$aAdditionalFiles ) {
        parent::__construct( $oCompiler );
        $this->sTempDirPath     = &$sTempDirPath;
        $this->aPHPFiles        = &$aPHPFiles;
        $this->aAdditionalFiles = &$aAdditionalFiles;
    }

    /**
     * @throws Exception
     */
    public function tryDoing() {
        $this->___tryListingFiles( $this->sTempDirPath, $this->aPHPFiles, $this->aAdditionalFiles );
    }

        /**
         * @param  string    $sTempDirPath
         * @throws Exception
         * @since  1.0.0
         */
        private function ___tryListingFiles( $sTempDirPath, array &$aPHPFiles, array &$aAdditionalFiles ) {

            $this->oCompiler->output( 'Searching files under the directory: ' . $this->oCompiler->sSourceDirPath );

            /**
             * @var array associative array consisting of class name keys with sub elements.
             * ```
             * [
             *  [AdminPageFramework_Form_View___FieldsetRow] => Array (
             *       [path] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow.php
             *       [code] => class AdminPageFramework_Form_View___FieldsetRow extends AdminPageFramework_Form_View___FieldsetTableRow { ...
             *       [dependency] => AdminPageFramework_Form_View___FieldsetTableRow
             *       [classes] => Array(
             *          [0] => AdminPageFramework_Form_View___FieldsetRow
             *       )
             *       [interfaces] => Array()
             *       [traits] => Array()
             *       [namespaces] => Array()
             *       [aliases] => Array()
             *  // continues
             * ]
             * ```
             */
            $aPHPFiles = $this->___getFileList( $sTempDirPath, $this->oCompiler->aArguments[ 'search' ], 'CLASS' );
            $this->oCompiler->output( sprintf( 'Found %1$s file(s).', count( $aPHPFiles ) ) );

            /**
             * Structure
             * @var array numerically indexed linear array holding found file paths.
             * [
             *      [0] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow.php
             *      [1] => .../factory/_common/form/_view/sectionset/AdminPageFramework_Form_View___FieldsetRow2.php
             *      // continues...
             * ]
             */
            if ( ! empty( $this->oCompiler->aArguments[ 'include' ][ 'allowed_extensions' ] ) ) {
                $aAdditionalFiles = $this->___getFileList( $sTempDirPath, $this->oCompiler->aArguments[ 'include' ], 'PATH' );
                $this->oCompiler->output( sprintf( 'Found %1$s additional file(s).', count( $aAdditionalFiles ) ) );
            }

            if ( empty( $aPHPFiles ) && empty( $aAdditionalFiles ) ) {
                throw new Exception( 'Could not find files.' );
            }

        }
            /**
             * @param  string $sTempDirPath
             * @param  array  $aSearchOptions
             * @param  string $sStructureType
             * @return array
             * @since  1.0.0
             */
            private function ___getFileList( $sTempDirPath, array $aSearchOptions, $sStructureType='CLASS' ) {
                $_oGenerator = new PHPClassMapGenerator(
                    $sTempDirPath,  // doesn't matter
                    [ $sTempDirPath ],
                    '',
                    array(
                        'do_in_constructor'  => false,
                        'output_buffer'      => false,
                        'structure'          => $sStructureType,    // PATH (linear array holding found file path) or CLASS (consists of keys of class names and sub-elements as a value)
                        'search'             => $aSearchOptions,
                    )
                );
                return 'PATH' === $sStructureType
                    ? $_oGenerator->get()
                    : $_oGenerator->getItems();
            }

}