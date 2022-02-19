<?php

namespace AdminPageFrameworkCompiler;

use \Exception;

/**
 * Collections of utility methods which possibly can be used apart from the project.
 */
trait TraitDownload {

    /**
     * Downloads a file from the given url.
     * @since  1.0.0
     * @throws Exception
     */
    static public function download( $sURL, $sFilePath ) {

        // The cURL extension is required.
        if ( ! function_exists( 'curl_init' ) ) {
            throw new Exception( 'To download a file, the cURL PHP extension needs to be installed. You are using PHP ' . PHP_VERSION . '.' );
        }

        // Create the directory if not exists.
        $_sDirPath = dirname( $sFilePath );
        if ( ! is_dir( $_sDirPath ) ) {
            mkdir( $_sDirPath, 0755, true );
        }

        // Remove the existing file.
        if ( file_exists( $sFilePath ) ) {
            unlink( $sFilePath );
        }

        $sURL = self::getRedirectedURL( $sURL );

        $_hZipResource = fopen( $sFilePath , "w" );

        // Get The Zip File From Server
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $sURL );
        curl_setopt( $ch, CURLOPT_FAILONERROR, true );
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_BINARYTRANSFER,true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_FILE, $_hZipResource );
        $page = curl_exec( $ch );
        if( ! $page ) {
            $_sMessage = "Download Error : " . curl_error( $ch );
            curl_close( $ch );
            throw new Exception( $_sMessage );
        }
        curl_close( $ch );
        return true;
    }

    /**
     * Returns the final destination of redirected URL.
     *
     * @since   1.0.0
     */
    static public function getRedirectedURL( $sURL ) {

        $ch = curl_init( $sURL );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
        curl_setopt( $ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.7 '
            . '(KHTML, like Gecko) Chrome/7.0.517.41 Safari/534.7'  // imitate chrome
        );
        curl_setopt( $ch, CURLOPT_NOBODY, true ); // HEAD request only (faster)
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // don't echo results
        curl_exec( $ch );
        $_sFinalURL = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL ); // get last URL followed
        curl_close($ch);

        return $_sFinalURL ? $_sFinalURL : $sURL;

    }

}