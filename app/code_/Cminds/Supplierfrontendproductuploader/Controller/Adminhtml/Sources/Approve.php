<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Sources;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryAdminUi\Model\Source\SourceHydrator as InventorySourceHydrator;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface as SourceModel;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Approve extends Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductUploaderInventory
     */
    private $productUploaderInventory;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceInterfaceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepositoryInterface;

    /**
     * @var InventorySourceHydrator
     */
    private $inventorySourceHydrator;

    /**
     * @param Context                      $context
     * @param SourceModel                  $sourceModel
     * @param ProductUploaderInventory     $productUploaderInventory
     * @param SourceInterfaceFactory       $sourceInterfaceFactory
     * @param sourceRepositoryInterface    $sourceRepositoryInterface
     * @param InventorySourceHydrator      $inventorySourceHydrator
     * @param LoggerInterface              $logger
     */
    public function __construct(
        Context $context,
        SourceModel $sourceModel,
        ProductUploaderInventory $productUploaderInventory,
        SourceInterfaceFactory $sourceInterfaceFactory,
        SourceRepositoryInterface $sourceRepositoryInterface,
        InventorySourceHydrator $inventorySourceHydrator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->sourceModel = $sourceModel;
        $this->messageManager = $context->getMessageManager();
        $this->productUploaderInventory = $productUploaderInventory;
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
        $this->sourceRepositoryInterface = $sourceRepositoryInterface;
        $this->inventorySourceHydrator = $inventorySourceHydrator;
        $this->logger = $logger;
    }

    public function execute()
    {
        $sourceId = $this->_request->getParam('id');
        $sourceModel = $this->sourceModel->load($sourceId);

        if (!$sourceModel) {
            $this->messageManager->addErrorMessage(__('Source not found.'));
            return $this->_redirect("*/*/index");
        }

        if ($this->productUploaderInventory->isSourceCodeUsed($sourceModel->getSourceCode())) {
            $this->messageManager->addErrorMessage(__('Source code is already in use.'));
            return $this->_redirect("*/*/index");
        }

        if (SourceModel::STATUS_PENDING === (int) $sourceModel->getStatus()
            || SourceModel::STATUS_REJECTED === (int) $sourceModel->getStatus()
        ) {
            try {
                // update source data
                $sourceModel->setStatus(SourceModel::STATUS_APPROVED);
                $sourceModel->save();

                // prepare source request data
                $this->_prepareSourceData($sourceModel);
                $this->_createSource();

                // $this->messageManager->addSuccessMessage(
                //     __('Suggested source was approved.')
                // );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Action failed.')
                );
                $this->logger->critical($e);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Source status is incompatible with selected action'));
        }

        return $this->_redirect("*/*/index");
    }

    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Supplierfrontendproductuploader::supplier_sources');
    }

    protected function _prepareSourceData(SourceModel $model)
    {
        $request = $this->_request;
        $sourceData = [];
        $sourceData['id_field_name'] = 'source_code';

        $itemsToAdd = [
            SourceModel::SOURCE_CODE,
            SourceModel::NAME,
            SourceModel::EMAIL,
            SourceModel::CONTACT_NAME,
            SourceModel::DESCRIPTION,
            SourceModel::LATITUDE,
            SourceModel::LONGITUDE,
            SourceModel::COUNTRY_ID,
            SourceModel::REGION_ID,
            SourceModel::CITY,
            SourceModel::POSTCODE,
            SourceModel::PHONE,
            SourceModel::FAX,
            SourceModel::REGION,
            SourceModel::STREET,
        ];

        $sourceData['enabled'] = 1;
        $sourceData['use_default_carrier_config'] = 1;
        $sourceData['carrier_codes'] = '';
        $sourceData['disable_source_code'] = true;

        foreach ($itemsToAdd as $code) {
            $sourceData[$code] = $model->getData($code);
        }

        $request->setPostValue('general', $sourceData);
        $request->setPostValue('form_key', 'or1eYs7K4PBsLbO1');
    }

    protected function _createSource()
    {
        $request = $this->_request;
        $requestData = $request->getPost()->toArray();
        $sourceCodeQueryParam = $request->getQuery(SourceInterface::SOURCE_CODE);
        try {
            $inventorySource = (null !== $sourceCodeQueryParam)
                ? $this->sourceRepositoryInterface->get($sourceCodeQueryParam)
                : $this->sourceInterfaceFactory->create();

            $inventorySource = $this->inventorySourceHydrator->hydrate($inventorySource, $requestData);

            $this->_eventManager->dispatch(
                'controller_action_inventory_populate_source_with_data',
                [
                    'request' => $request,
                    'source' => $inventorySource,
                ]
            );

            $this->sourceRepositoryInterface->save($inventorySource);

            $this->_eventManager->dispatch(
                'controller_action_inventory_source_save_after',
                [
                    'request' => $request,
                    'source' => $inventorySource,
                ]
            );

            $this->messageManager->addSuccessMessage(__('The Source has been saved.'));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The Source does not exist.'));
        } catch (\Magento\Framework\Validation\ValidationException $e) {
            foreach ($e->getErrors() as $localizedError) {
                $this->messageManager->addErrorMessage($localizedError->getMessage());
            }
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not save Source.'));
        }
    }
}
