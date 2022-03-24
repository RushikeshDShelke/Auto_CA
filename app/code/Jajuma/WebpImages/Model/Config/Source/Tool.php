<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Model\Config\Source;

class Tool implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'cwebp', 'label' => __('Cwebp')],
            ['value' => 'convert', 'label' => __('Imagemagick')],
            ['value' => 'gd', 'label' => __('GD')]
        ];
    }
}