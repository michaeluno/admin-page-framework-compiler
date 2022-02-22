<?php
namespace AdminPageFrameworkCompiler\FixerHelper;

/**
 *
 */
class VirtualVariableStreamWrapper {

    private $iPosition;
    private $sVarName;

    static public $aData = [];

    public function stream_open( $sPath, $sMode, $options, &$opened_path ) {
        $this->sVarName  = $this->getVarName( $sPath );
        $this->iPosition = 0;
        return true;
    }

    public function stream_read( $iCount ) {
        if ( ! isset( self::$aData[ $this->sVarName ] ) ) {
            return null;
        }
        $_iPos  =& $this->iPosition;
        $_sRead = substr( self::$aData[ $this->sVarName ], $_iPos, $iCount );
        $_iPos += strlen( $_sRead );

        return $_sRead;
    }

    public function stream_write( $data ) {
        $v =& self::$aData[ $this->sVarName ];
        $l = strlen( $data );
        $p =& $this->iPosition;
        $v = substr( $v, 0, $p ) . $data . substr( $v, $p += $l );
        return $l;
    }

    public function stream_tell() {
        return $this->iPosition;
    }

    public function stream_eof() {
        if ( ! isset( self::$aData[ $this->sVarName ] ) ) {
            return false;
        }

        return $this->iPosition >= strlen( self::$aData[ $this->sVarName ] );
    }

    public function stream_seek( $offset, $whence ) {
        $l = strlen( self::$aData[ $this->sVarName ] );
        $p =& $this->iPosition;
        switch ( $whence ) {
            case SEEK_SET:
                $newPos = $offset;
                break;
            case SEEK_CUR:
                $newPos = $p + $offset;
                break;
            case SEEK_END:
                $newPos = $l + $offset;
                break;
            default:
                return false;
        }
        $ret = ( $newPos >= 0 && $newPos <= $l );
        if ( $ret ) {
            $p = $newPos;
        }

        return $ret;
    }

    public function mkdir() {
        return true;
    }

    /**
     * Called when the path is checked with file_exists(), is_writable() etc.
     * @param  string $sPath
     * @param  integer $iFlags
     * @return array|false
     */
    public function url_stat( $sPath, $iFlags ) {
        if ( ! $this->endsWith( $sPath, '.virtual.php' ) ) {
            return false;
        }
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0100755,  // 0100000 + 0755 so that is_writable() yields true
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => isset( self::$aData[ $this->sVarName ] ) ? strlen( self::$aData[ $this->sVarName ] ) : 0,
            'atime'   => time(),
            'mtime'   => time(),
            'ctime'   => time(),
            'blksize' => 0,
            'blocks'  => 0,
        ];
    }

    public function stream_stat() {
        return array();
    }

    /**
     * @param  int $iOption
     * @param  int $arg1
     * @param  int|null $arg2
     * @return bool
     */
    public function stream_set_option( $iOption, $arg1, $arg2 = null ) {
        if ( ! isset( self::$aData[ $this->sVarName ] ) ) {
            return false;
        }
        if ( STREAM_OPTION_BLOCKING === $iOption ) {
            return stream_set_blocking( self::$aData[ $this->sVarName ], $arg1 );
        }
        if ( STREAM_OPTION_READ_TIMEOUT === $iOption ) {
            return stream_set_timeout( self::$aData[ $this->sVarName ], $arg1, $arg2 );
        }
        return stream_set_write_buffer( self::$aData[ $this->sVarName ], $arg2 ) === 0;
    }

    /**
     * @param string $sPath
     * @return bool
     */
    public function unlink( $sPath ) {
        $_bSet = isset( self::$aData[ $this->getVarName( $sPath ) ] );
        unset( self::$aData[ $this->getVarName( $sPath ) ] );
        return $_bSet;
    }

    // Utilities
    public function getVarName( $sPath ) {
        return parse_url( $sPath, PHP_URL_HOST );
    }

    function endsWith( $haystack, $needle ) {
        return substr_compare( $haystack, $needle, - strlen( $needle ) ) === 0;
    }

}