<?php
namespace Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\Link;

use Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\LinkEntityInterface;

class Url extends AbstractLink implements LinkEntityInterface
{
	const LINK_DATA_TYPE = 'url';

	/**
	 * Create or build link.
	 *
	 * @param array $data
	 *
	 * @return \Magento\Downloadable\Api\Data\LinkInterface
	 */
	public function buildLink(array $data)
	{
		return $this->createLink($data);   
	}
}
