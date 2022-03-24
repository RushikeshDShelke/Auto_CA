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

class InlineEdit extends \Delhivery\Lastmile\Controller\Adminhtml\Location
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Manage Location repository
     * 
     * @var \Delhivery\Lastmile\Api\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Data object processor
     * 
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data object helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * JSON Factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Manage Location resource model
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Location
     */
    protected $locationResourceModel;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Delhivery\Lastmile\Model\ResourceModel\Location $locationResourceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Delhivery\Lastmile\Model\ResourceModel\Location $locationResourceModel
    ) {
        $this->dataObjectProcessor   = $dataObjectProcessor;
        $this->dataObjectHelper      = $dataObjectHelper;
        $this->jsonFactory           = $jsonFactory;
        $this->locationResourceModel = $locationResourceModel;
        parent::__construct($context, $coreRegistry, $locationRepository, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $locationId) {
            /** @var \Delhivery\Lastmile\Model\Location|\Delhivery\Lastmile\Api\Data\LocationInterface $location */
            $location = $this->locationRepository->getById((int)$locationId);
            try {
                $locationData = $postItems[$locationId];
                $this->dataObjectHelper->populateWithArray($location, $locationData, \Delhivery\Lastmile\Api\Data\LocationInterface::class);
                $this->locationResourceModel->saveAttribute($location, array_keys($locationData));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithLocationId($location, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithLocationId($location, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithLocationId(
                    $location,
                    __('Something went wrong while saving the Manage&#x20;Location.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Manage&#x20;Location id to error message
     *
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithLocationId(\Delhivery\Lastmile\Api\Data\LocationInterface $location, $errorText)
    {
        return '[Manage&#x20;Location ID: ' . $location->getId() . '] ' . $errorText;
    }
}
