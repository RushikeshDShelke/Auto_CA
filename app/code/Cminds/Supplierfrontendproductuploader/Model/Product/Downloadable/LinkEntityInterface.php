<?php 
namespace Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable;

use Magento\Downloadable\Api\Data\LinkInterface;

interface LinkEntityInterface 
{
	/**
	 * Save link entity into the database.
	 *
	 * @param LinkInterface $link
	 *
	 * @return LinkEntityInterface
	 */
	public function save(LinkInterface $link);
}
