<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Supplier;

use Cminds\Supplierfrontendproductuploader\Helper\Email;
use Cminds\Supplierfrontendproductuploader\Model\Service\ProductService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Cminds Supplierfrontendproductuploader approve product controller.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Approve extends Action
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
     * @var Email
     */
    private $email;

    /**
     * Product factory object.
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * Object constructor.
     *
     * @param Context         $context           Context object.
     * @param PageFactory     $resultPageFactory Page factory object.
     * @param ProductService  $productService    Product service object.
     * @param LoggerInterface $logger            Logger object.
     * @param Email $email
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductService $productService,
        LoggerInterface $logger,
        Email $email,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->productService = $productService;
        $this->logger = $logger;
        $this->email = $email;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
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
            $this->productService->approveProduct($productId);

            $product = $this->productFactory->create()->load($productId);

            $supplierId = $product->getCreatorId();
            $supplier = $this->customerFactory->create()->load($supplierId);

            $this->email->productApproved($supplier, $product);

            $this->messageManager->addSuccessMessage(
                __('Product has been approved.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Product has not been approved.')
            );
            $this->logger->critical($e);
        }

        return $this->_redirect("*/*/products");
    }
}
