<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Controller\Adminhtml\Location;

abstract class MassAction extends \Magento\Backend\App\Action
{
    /**
     * Manage Location repository
     * 
     * @var \Delhivery\Lastmile\Api\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * Mass Action filter
     * 
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Manage Location collection factory
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Action success message
     * 
     * @var string
     */
    protected $successMessage;

    /**
     * Action error message
     * 
     * @var string
     */
    protected $errorMessage;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory $collectionFactory
     * @param string $successMessage
     * @param string $errorMessage
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory $collectionFactory,
        $successMessage,
        $errorMessage
    ) {
        $this->locationRepository = $locationRepository;
        $this->filter             = $filter;
        $this->collectionFactory  = $collectionFactory;
        $this->successMessage     = $successMessage;
        $this->errorMessage       = $errorMessage;
        parent::__construct($context);
    }

    /**
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @return mixed
     */
    abstract protected function massAction(\Delhivery\Lastmile\Api\Data\LocationInterface $location);

    /**
     * execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $location) {
                $this->massAction($location);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $collectionSize));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $this->errorMessage);
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('delhivery_lastmile/*/index');
        return $redirectResult;
    }
}
