<?php
namespace Craft\Soldoutorder\Plugin\Catalog\Model;

/**
 * Class Layer
 * @package Craft\Soldoutorder\Plugin\Catalog\Model\Layer
 */
class Layer
{
  /**
  * Sort items that are not salable last
  *
  * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
  */
  public function aroundgetProductCollection(
		\Magento\Catalog\Model\Layer $subject,
		callable $proceed
	) {
		$collection = $proceed();
		$collection->getSelect()->order('is_salable DESC');
		return $collection;
     }
}