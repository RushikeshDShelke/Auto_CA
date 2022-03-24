<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Supplier;

use Cminds\Supplierfrontendproductuploader\Model\Product;
use Cminds\Supplierfrontendproductuploader\Model\Service\ProductService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cminds Supplierfrontendproductuploader disapprove product controller.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Disapprove extends Action
{
    /**
     * Authorization level of a basic admin session.
     */
    const ADMIN_RESOURCE = 'Cminds_Supplierfrontendproductuploader::supplier_products';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Product service object.
     *
     * @var ProductService
     */
    protected $productService;

    /**
     * Logger object.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Object constructor.
     *
     * @param Context        $context           Context object.
     * @param PageFactory    $resultPageFactory Page factory object.
     * @param ProductService $productService    Product service object.
     * @param LoggerInterface $logger            Logger object.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductService $productService,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->productService = $productService;
        $this->logger = $logger;
    }

    /**
     * Dispatch request.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $productId = $this->_request->getParam('id');

        try {
            $this->productService->disapproveProduct($productId);

            $this->messageManager->addSuccessMessage(
                __('Product has been disapproved.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Product has not been disapproved.')
            );
            $this->logger->critical($e);
        }

        return $this->_redirect("*/*/products");
    }
}
