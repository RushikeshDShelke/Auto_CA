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

class Save extends \Delhivery\Lastmile\Controller\Adminhtml\Location
{
    /**
     * Manage Location factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory
     */
    protected $locationFactory;

    /**
     * Data Object Processor
     * 
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;


    /**
     * Data Persistor
     * 
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory $locationFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Model\UploaderPool $uploaderPool
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory $locationFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->locationFactory     = $locationFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->dataPersistor       = $dataPersistor;
        parent::__construct($context, $coreRegistry, $locationRepository, $resultPageFactory);
    }

    /**
     * run the action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Delhivery\Lastmile\Api\Data\LocationInterface $location */
        $location = null;
        $postData = $this->getRequest()->getPostValue();
        $data = $postData;
        $id = !empty($data['location_id']) ? $data['location_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $location = $this->locationRepository->getById((int)$id);
            } else {
                unset($data['location_id']);
                $location = $this->locationFactory->create();
            }
            $this->dataObjectHelper->populateWithArray($location, $data, \Delhivery\Lastmile\Api\Data\LocationInterface::class);
            $this->locationRepository->save($location);
            $this->messageManager->addSuccessMessage(__('You saved the Manage&#x20;Location'));
            $this->dataPersistor->clear('delhivery_lastmile_location');
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('delhivery_lastmile/location/edit', ['location_id' => $location->getId()]);
            } else {
                $resultRedirect->setPath('delhivery_lastmile/location');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('delhivery_lastmile_location', $postData);
            $resultRedirect->setPath('delhivery_lastmile/location/edit', ['location_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the Manage&#x20;Location'));
            $this->dataPersistor->set('delhivery_lastmile_location', $postData);
            $resultRedirect->setPath('delhivery_lastmile/location/edit', ['location_id' => $id]);
        }
        return $resultRedirect;
    }

    /**
     * @param string $type
     * @return \Delhivery\Lastmile\Model\Uploader
     * @throws \Exception
     */
}
