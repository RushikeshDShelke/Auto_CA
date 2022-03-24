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
namespace Delhivery\Lastmile\Controller\Adminhtml\Pincode;

class InlineEdit extends \Delhivery\Lastmile\Controller\Adminhtml\Pincode
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Manage Pincode repository
     * 
     * @var \Delhivery\Lastmile\Api\PincodeRepositoryInterface
     */
    protected $pincodeRepository;

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
     * Manage Pincode resource model
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Pincode
     */
    protected $pincodeResourceModel;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Delhivery\Lastmile\Model\ResourceModel\Pincode $pincodeResourceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Delhivery\Lastmile\Model\ResourceModel\Pincode $pincodeResourceModel
    ) {
        $this->dataObjectProcessor  = $dataObjectProcessor;
        $this->dataObjectHelper     = $dataObjectHelper;
        $this->jsonFactory          = $jsonFactory;
        $this->pincodeResourceModel = $pincodeResourceModel;
        parent::__construct($context, $coreRegistry, $pincodeRepository, $resultPageFactory);
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

        foreach (array_keys($postItems) as $pincodeId) {
            /** @var \Delhivery\Lastmile\Model\Pincode|\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode */
            $pincode = $this->pincodeRepository->getById((int)$pincodeId);
            try {
                $pincodeData = $postItems[$pincodeId];
                $this->dataObjectHelper->populateWithArray($pincode, $pincodeData, \Delhivery\Lastmile\Api\Data\PincodeInterface::class);
                $this->pincodeResourceModel->saveAttribute($pincode, array_keys($pincodeData));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPincodeId($pincode, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPincodeId($pincode, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPincodeId(
                    $pincode,
                    __('Something went wrong while saving the Manage&#x20;Pincode.')
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
     * Add Manage&#x20;Pincode id to error message
     *
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPincodeId(\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode, $errorText)
    {
        return '[Manage&#x20;Pincode ID: ' . $pincode->getId() . '] ' . $errorText;
    }
}
