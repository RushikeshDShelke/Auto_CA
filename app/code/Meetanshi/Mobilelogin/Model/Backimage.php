<?php

namespace Meetanshi\Mobilelogin\Model;

/**
 * Class Backimage
 * @package Meetanshi\Mobilelogin\Model
 */
class Backimage extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     *
     */
    const UPLOAD_DIR = 'mobilelogin/backimage/';

    /**
     * @return string
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * @return bool
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'png'];
    }
}
