<?php

namespace AdminPageFrameworkCompiler;

/**
 * Collections of utility methods which possibly can be used apart from the project.
 */
trait TraitLog {

    /**
     * Logs a message.
     * @since  1.0.0
     */
    static public function log( $sMessage, $sFilePath ) {
        file_put_contents( $sFilePath, $sMessage, FILE_APPEND|LOCK_EX );
    }

}