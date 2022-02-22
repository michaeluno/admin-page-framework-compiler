<?php
namespace AdminPageFrameworkCompiler\FixerHelper;
/**
 * 
 */
class VirtualFileInfo extends \SplFileInfo {
    
    public $sPathVirtual = '';

    public function __construct( $sPathVirtual ) {
        $this->sPathVirtual = $sPathVirtual;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getRealPath();
    }

    /**
     * @return string
     */
    public function getRealPath() {
        return $this->sPathVirtual;
    }

    /**
     * @return int
     */
    public function getATime() {
        return 0;
    }

    /**
     * @param null|string $suffix
     * @return string
     */
    public function getBasename( $suffix = null ) {
        return $this->getFilename();
    }

    /**
     * @return int
     */
    public function getCTime() {
        return 0;
    }

    /**
     * @return string
     */
    public function getExtension() {
        return '.php';
    }

    /**
     * @param  null|string $sClassName
     */
    public function getFileInfo( $sClassName = null ) {
        throw new \BadMethodCallException( sprintf( 'Method "%s" is not implemented.', __METHOD__ ) );
    }

    /**
     * @return string
     */
    public function getFilename() {
        return basename( $this->sPathVirtual );
    }

    /**
     * @return int
     */
    public function getGroup() {
        return 0;
    }

    /**
     * @return int
     */
    public function getInode() {
        return 0;
    }

    /**
     * @return string
     */
    public function getLinkTarget() {
        return '';
    }

    /**
     * @return int
     */
    public function getMTime() {
        return 0;
    }

    /**
     * @return int
     */
    public function getOwner() {
        return 0;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->sPathVirtual;
        // return '';
    }

    /**
     * @param  string|null $sClassName
     */
    public function getPathInfo( $sClassName = null ) {
        throw new \BadMethodCallException( sprintf( 'Method "%s" is not implemented.', __METHOD__ ) );
    }

    /**
     * @return string
     */
    public function getPathname() {
        return $this->getFilename();
    }

    /**
     * @return int
     */
    public function getPerms() {
        return 0;
    }

    /**
     * @return int
     */
    public function getSize() {
        return 0;
    }

    /**
     * @return string
     */
    public function getType() {
        return 'file';
    }

    /**
     * @return bool
     */
    public function isDir() {
        return false;
    }

    /**
     * @return bool
     */
    public function isExecutable() {
        return false;
    }

    /**
     * @return bool
     */
    public function isFile() {
        return true;
    }

    /**
     * @return bool
     */
    public function isLink() {
        return false;
    }

    /**
     * @return bool
     */
    public function isReadable() {
        return true;
    }

    /**
     * @return bool
     */
    public function isWritable() {
        return false;
    }

    /**
     * @param  string $sOpenMode
     * @param  false  $bUseIncludePath
     * @param  null   $context
     */
    public function openFile( $sOpenMode = 'r', $bUseIncludePath = false, $context = null ) {
        throw new \BadMethodCallException( sprintf( 'Method "%s" is not implemented.', __METHOD__ ) );
    }

    /**
     * @param null|string $sClassName
     */
    public function setFileClass( $sClassName = null ) {}

    /**
     * @param null|string $sClassName
     */
    public function setInfoClass( $sClassName = null ) {}
    
}