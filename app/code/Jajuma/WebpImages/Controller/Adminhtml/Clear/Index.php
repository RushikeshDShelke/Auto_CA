<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Jajuma\WebpImages\Controller\Adminhtml\Clear;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Jajuma\WebpImages\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->helper->clearWebp() == 'nowebpFolder') {
            $this->messageManager->addSuccessMessage(__('WebP Image Cache is empty'));
        } elseif ($this->helper->clearWebp()) {
            $this->messageManager->addSuccessMessage(__('All WebP images have been removed (please also clear FPC to recreate WebP images)'));
        } else {
            $this->messageManager->addErrorMessage(__('Can not remove the webp_image folder (please check folder permissions)'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}

