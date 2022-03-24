<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Message;

// use Magento\Framework\App\Action\Action;
use Magento\Catalog\Controller\Product as ProductController;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Cminds\Supplierfrontendproductuploader\Helper\Data\Proxy as DataHelper;

/**
 * Action class called via AJAX; Generates message + triggers message section update on product page
 */

class Refresh extends ProductController
{
    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Cminds\Supplierfrontendproductuploader\Helper\Data\Proxy
     */
    protected $helper;

    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    public function __construct(
        Context $context,
        Registry $registry,
        DataHelper $dataHelper,
        Session $customerSession,
        ManagerInterface $messageManager
    )
    {
        $this->_coreRegistry = $registry;
        $this->helper = $dataHelper;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;

        return parent::__construct($context);
    }


    /**
     * @return json encoded message
     */
    public function execute()
    {
        $product = $this->_initProduct();
        $isPreview = false;
        if( $this->customerSession->isLoggedIn()
            && $product->getCreatorId() == $this->helper->getSupplierId() )
        {
            // $this->messageManager->addError(__('This is preview mode!'));
            $this->messageManager->addNotice(__('This is preview mode!'));
            $isPreview = true;
        }

        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['success'=>true, 'preview' => $isPreview ])
        );
    }
}
