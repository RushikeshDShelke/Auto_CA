<?php

namespace Olegnax\MegaMenu\Controller\Adminhtml\Category\Image;

use Exception;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Agorae Adminhtml Category Image Upload Controller
 */
class Upload extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{

	/**
	 * Upload file controller action
	 *
	 * @return ResultInterface
	 */
	public function execute()
	{
		$imageId = $this->_request->getParam('param_name', 'ox_cat_image');
		try {
			$result = $this->imageUploader->saveFileToTmpDir($imageId);
			$result['cookie'] = [
				'name' => $this->_getSession()->getName(),
				'value' => $this->_getSession()->getSessionId(),
				'lifetime' => $this->_getSession()->getCookieLifetime(),
				'path' => $this->_getSession()->getCookiePath(),
				'domain' => $this->_getSession()->getCookieDomain(),
			];
		} catch (Exception $e) {
			$result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
		}
		return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
	}
}