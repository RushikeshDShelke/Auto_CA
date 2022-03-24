<?php declare(strict_types=1);
/**
 * Copyright (c) 2021
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Olegnax\Carousel\Model\ResourceModel;


use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Carousel extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('olegnax_carousel_carousel', 'carousel_id');
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $data = $object->getData('identifier');
        if (empty($data)) {
            $data = $object->getData('Title');
        }
        $data = str_replace(' ', '_', strtolower($data));
        $object->setData('identifier', $data);

        if (!$this->getIsUniqueCarousel($object)) {
            throw new LocalizedException(
                __('A Carousel identifier with the same properties already exists.')
            );
        }

        return parent::_beforeSave($object);
    }

    private function getIsUniqueCarousel(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->where('cb.identifier = ?  ', $object->getData('identifier'));

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }
}

