<?php
/**
 *
 */

namespace Olegnax\Carousel\Model\Slide\Source;

use Magento\Framework\Data\Collection;
use Magento\Framework\Option\ArrayInterface;
use Olegnax\Carousel\Model\ResourceModel\Carousel\CollectionFactory;

class Carousel implements ArrayInterface
{

    protected $collection;

    public function __construct(
        CollectionFactory $collection
    ) {
        $this->collection = $collection->create()->addFieldToSelect('*')->setOrder('title', Collection::SORT_ORDER_ASC);
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    public function toOptionArray()
    {

        $options = [];
        foreach ($this->collection as $carousel) {
            $options[] = [
                'value' => $carousel->getIdentifier(),
                'label' => $carousel->getTitle()
            ];
        }

        return $options;
    }

}