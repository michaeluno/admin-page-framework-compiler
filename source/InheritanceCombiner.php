<?php

namespace AdminPageFrameworkCompiler;

class InheritanceCombiner {

    public $aFiles = [];

    public $aArguments = [];

    /**
     * Sets up properties and hooks.
     */
    public function __construct( array $aFiles, array $aArguments=[] ) {
        $this->aFiles     = $aFiles;
        $this->aArguments = $aArguments + [ 'exclude_classes' => [] ];
    }
    /**
     * @return array
     * @since  1.0.0
     */
    public function get() {

        $aFiles           = $this->aFiles;
        $_aCombineOptions = $this->aArguments;

        $_aNew = array();
        $_aClassNamesToRemove = array();
        foreach( $aFiles as $_sClassName => $_aFile ) {

            // $_aFile = $_aFile + [ 'code' => null, 'dependency' => null, 'path' => null, ];
            $_sParentClassName = $_aFile[ 'dependency' ] && $_sClassName !== $_aFile[ 'dependency' ]
                ? $_aFile[ 'dependency' ]
                : '';

            // If it does not extend any, do nothing.
            if ( empty( $_sParentClassName ) ) {
                $_aNew[ $_sClassName ] = $_aFile;
                continue;
            }
            // For parent classes which do not belong to the project,
            if ( ! isset( $aFiles[ $_sParentClassName ] ) ) {
                $_aNew[ $_sClassName ] = $_aFile;
                continue;
            }
            if ( in_array( $_sClassName, $_aCombineOptions[ 'exclude_classes' ], true ) ) {
                $_aNew[ $_sClassName ] = $_aFile;
                continue;
            }
            // If it is a parent of another class, do nothing.
            if ( $this->___isParent( $_sClassName, $aFiles, true, $_aCombineOptions[ 'exclude_classes' ] ) ) {
                $_aNew[ $_sClassName ] = $_aFile;
                continue;
            }

            // At this point, the parsing item is the most extended class in the same directory.

            // Combine code
            $_sThisCode = $_aFile[ 'code' ];
            foreach( $this->___getAncestorClassNames( $_sClassName, $aFiles, true ) as $_sAncestorClassName ) {

                // Insert the parent code at the top of the code of the parsing file
                $_sThisCode = $aFiles[ $_sAncestorClassName ][ 'code' ] . ' ' . $_sThisCode;
                unset( $aFiles[ $_sAncestorClassName ] );
                $_aClassNamesToRemove[] = $_sAncestorClassName;

            }
            $_aFile[ 'code' ] = $_sThisCode;

            // Add it to the new array
            $_aNew[ $_sClassName ] = $_aFile;

        }

        // Remove combined items
        foreach ( $_aClassNamesToRemove as $_sClassNameToRemove ) {
            unset( $_aNew[ $_sClassNameToRemove ] );
        }

        return $_aNew;

    }
        /**
         * Checks if there is a class extending the subject class in the project files.
         * @since       1.0.0
         * @return      boolean
         */
        private function ___isParent( $sClassName, $aFiles, $bOnlyInTheSameDirectory=true, array $aExcludingClassNames=array() ) {

            if ( ! isset( $aFiles[ $sClassName ] ) ) {
                return false;
            }
            $_sSubjectDirPath = dirname( $aFiles[ $sClassName ][ 'path' ] );
            foreach( $aFiles as $_sClassName => $_aFile ) {

                if ( in_array( $_sClassName, $aExcludingClassNames, true ) ) {
                    continue;
                }

                if ( $bOnlyInTheSameDirectory && $_sSubjectDirPath !== dirname( $_aFile[ 'path' ] ) ) {
                    continue;
                }

                if ( $sClassName === $_aFile[ 'dependency' ] ) {
                    return true;
                }
            }
            return false;

        }
        /**
         * @remark      The closet ancestor (the direct parent) will come first and the farthest one goes the last in the array
         * The order is important as their contents will be appended to the subject class code. And in some PHP versions,
         * parent classes must be written before its child class; otherwise. it causes a fatal error.
         * @since       1.0.0
         * @return      array
         */
        private function ___getAncestorClassNames( $sClassName, &$aFiles, $bOnlyInTheSameDirectory=true ) {

            $_aAncestors = array();

            $_sParentClass = isset( $aFiles[ $sClassName ] ) ? $aFiles[ $sClassName ][ 'dependency' ] : '';
            // Make sure the retrieved parent one also belongs to the project files.
            $_sParentClass = $_sParentClass && isset( $aFiles[ $_sParentClass ] ) ? $_sParentClass : '';
            if ( ! $_sParentClass ) {
                return $_aAncestors;
            }
            if ( $sClassName === $_sParentClass ) {
                return $_aAncestors;
            }
// print_r( [ 'this' => $sClassName, 'parent' => $_sParentClass ] );
// echo PHP_EOL;
// print_r( $aFiles[ $sClassName ] );
// echo PHP_EOL;
            // Add the parent class to the returning array.
            if ( $bOnlyInTheSameDirectory ) {
                $_sThisDirPath        = isset( $aFiles[ $sClassName ][ 'path' ] ) ? dirname( $aFiles[ $sClassName ][ 'path' ] ) : '';
                $_sParentClassDirPath = isset( $aFiles[ $_sParentClass ][ 'path' ] ) ? dirname( $aFiles[ $_sParentClass ][ 'path' ] ) : '';
                if ( ! $_sThisDirPath || ! $_sParentClassDirPath ) {
                    return $_aAncestors;
                }
                if ( $_sThisDirPath !== $_sParentClassDirPath ) {
                    return $_aAncestors;
                }
            }

            $_aAncestors[] = $_sParentClass;
            return array_unique( array_merge(
                $_aAncestors,
                $this->___getAncestorClassNames( $_sParentClass, $aFiles, $bOnlyInTheSameDirectory )
            ) );
            
        }
        
}