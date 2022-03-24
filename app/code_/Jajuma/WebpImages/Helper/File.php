<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Helper;

class File extends \Magento\Framework\Filesystem\Io\File
{
    public function setIwd($iwd)
    {
        $this->_iwd = $iwd;
    }

    public function setCwd($cwd)
    {
        $this->_cwd = $cwd;
    }
}
