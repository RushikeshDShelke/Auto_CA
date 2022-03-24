<?php

namespace Cminds\Marketplace\Controller\Supplier;

use Cminds\Marketplace\Model\Rating;
use Cminds\Marketplace\Model\Torate;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Ratelist extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry object.
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Customer session object.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Store manager object.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Customer factory object.
     *
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Transaction object.
     *
     * @var Transaction
     */
    protected $transaction;

    /**
     * Rating object.
     *
     * @var Rating
     */
    protected $rating;

    /**
     * Core resource object.
     *
     * @var ResourceConnection
     */
    protected $coreResource;

    /**
     * To rate object.
     *
     * @var Torate
     */
    protected $torate;

    /**
     * Result page factory object.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Object constructor.
     *
     * @param Context               $context            Context object.
     * @param Data                  $helper             Data helper object.
     * @param Registry              $coreRegistry       Core registry object.
     * @param CustomerSession       $customerSession    Customer session object.
     * @param StoreManagerInterface $storeManager       Store manager object.
     * @param CustomerFactory       $customerFactory    Customer factory object.
     * @param Transaction           $transaction        Transaction object.
     * @param Rating                $rating             Rating object.
     * @param ResourceConnection    $resourceConnection Resource connection object.
     * @param Torate                $torate             To rate object.
     */
    public function __construct(
        Context $context,
        Data $helper,
        Registry $coreRegistry,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        Transaction $transaction,
        Rating $rating,
        ResourceConnection $resourceConnection,
        Torate $torate,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->transaction = $transaction;
        $this->rating = $rating;
        $this->coreResource = $resourceConnection;
        $this->torate = $torate;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Dispatch request.
     *
     * @param RequestInterface $request Request object.
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->getActionFlag()->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }

    /**
     * Execute main controller logic.
     *
     * @return Page
     */
    public function execute()
    {
        $request = $this->getRequest();

        if ($request->getParams()) {
            $transaction = $this->coreResource->getConnection('default');
            $postData = $request->getParams();

            try {
                $loggedCustomer = $this->customerFactory->create()
                    ->load($this->customerSession->getId());
                $loggedCustomerId = $loggedCustomer->getId();

                $transaction->beginTransaction();

                foreach ($postData['ratings'] as $supplierId => $rate) {
                    $collection = $this->torate
                        ->getCollection()
                        ->addFieldToFilter(
                            'main_table.customer_id',
                            $loggedCustomerId
                        );

                    foreach ($collection as $item) {
                        $item->delete();
                    }

                    $this->rating
                        ->setSupplierId($supplierId)
                        ->setCustomerId($loggedCustomerId)
                        ->setRate($rate)
                        ->setCreatedOn(date('Y-m-d H:i:s'))
                        ->save();
                }

                $transaction->commit();
                $this->messageManager->addSuccess(__('Rate was added.'));
            } catch (LocalizedException $e) {
                $transaction->rollback();
            }
        }

        return $resultPage = $this->resultPageFactory->create();
    }
}
