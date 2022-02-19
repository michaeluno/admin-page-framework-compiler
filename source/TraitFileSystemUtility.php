<?php

namespace AdminPageFrameworkCompiler;

use \Exception;
use \ZipArchive;

/**
 * Collections of utility methods which possibly can be used apart from the project.
 */
trait TraitFileSystemUtility {

    /**
     * @throws Exception
     */
    static public function unzip( $sZipFilePath ) {

        if ( ! class_exists( '\ZipArchive' ) ) {
            throw new Exception( "The zlib PHP extension is required to extract zip files." );
        }

        // Open the Zip file
        $_oZip = new ZipArchive;
        if( $_oZip->open( $sZipFilePath ) != "true" ) {
            throw new Exception( "Error :- Unable to open the Zip File" );
        }

        // Extract the Zip File 
        $_oZip->extractTo( dirname( $sZipFilePath ) );
        $_oZip->close();

    }    
    
    /**
     * @since  1.0.0
     * @return string
     */
    static public function getAbsolutePathFromRelative( $sPrefix, $sRelativePath ) {
        $sRelativePath  = preg_replace( "/^\.[\/\\\]/", '', $sRelativePath, 1 );    // removes the heading ./ or .\
        return rtrim( $sPrefix, '/\\' ) . '/' . ltrim( $sRelativePath,'/\\' );          // APSPATH has a trailing slash.
    }

    /**
     * Calculates the relative path from the given path.
     *
     * This function is used to generate a template path.
     *
     * @author Gordon
     * @see    http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php/2638272#2638272
     */
    static public function getRelativePath( $from, $to ) {

        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);

    }

    /**
     * @since  1.0.0
     * @return boolean     Returns TRUE on success or FALSE on failure.
     */
    static public function deleteDir( $sDirPath ) {
        if ( ! is_dir( $sDirPath ) ) {
            return false;
            // throw new InvalidArgumentException("$sDirPath must be a directory");
        }
        if ( substr( $sDirPath, strlen( $sDirPath ) - 1, 1 ) !== '/' ) {
            $sDirPath .= '/';
        }
        $_aFiles = ( array ) glob( $sDirPath . '*', GLOB_MARK );
        foreach( $_aFiles as $_sFilePath ) {
            if ( is_dir( $_sFilePath ) ) {
                self::deleteDir( $_sFilePath );
            } else {
                unlink( $_sFilePath );
            }
        }
        return @rmdir( $sDirPath );
    }

    /**
     * @return string
     */
    static public function getPathFormatted( $sPath ) {
        return rtrim( str_replace( '\\', '/', $sPath ), '/' );
    }

    /**
     * Writes a given string content to a file.
     * @remark If a file exists, it will be overwritten.
     * @param  string $sFilePath
     * @param  string $sData
     */
    static public function write( $sFilePath, $sData ) {
        // Make sure the parent directory exists.
        $_sDirPath = dirname( $sFilePath );
        if ( ! is_dir( $_sDirPath ) ) {
            mkdir( $_sDirPath, 0755, true );
        }
        file_put_contents( $sFilePath, $sData, LOCK_EX );   // Write to a file,
    }

    /**
     * @return string The created directory path. An empty string if not created.
     */
    static public function createTempDir( $sTempDirNamePrefix ) {
        $_sTempFilePath = tempnam( sys_get_temp_dir(), $sTempDirNamePrefix );
        if ( file_exists( $_sTempFilePath ) ) {
            unlink( $_sTempFilePath );
        }
        mkdir( $_sTempFilePath );
        if ( is_dir( $_sTempFilePath ) ) {
            return $_sTempFilePath;
        }
        return '';
    }

}