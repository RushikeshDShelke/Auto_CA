<?php
/**
 * Class Save
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;

/**
 * Class Save
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory
     */
    protected $mappingFactory;

    /**
     * @var \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping
     */
    protected $mappingResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory $mappingFactory
     * @param \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping $mappingResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory $mappingFactory,
        \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping $mappingResource,
        \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping\CollectionFactory $mappingCollectionFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->mappingFactory = $mappingFactory;
        $this->mappingResource = $mappingResource;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Attribute Mapping save action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                $mapping = $this->mappingFactory->create();
                $mappedFacebookAttr = $this->mappingCollectionFactory->create()
                    ->addFieldToFilter('facebook_attribute', $data['facebook_attribute'])
                    ->addFieldToFilter('entity_id', ['neq' => $data['entity_id']]);
                if (count($mappedFacebookAttr) > 0) {
                    $this->dataPersistor->set('mapping_data', $data);
                    $this->messageManager->addErrorMessage(__('This Facebook attribute is already mapped.'));
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $data['entity_id']]);
                }
                if (isset($data['entity_id'])) {
                    $this->mappingResource->load($mapping, $data['entity_id']);
                }
                $mapping->setFacebookAttribute($data['facebook_attribute']);
                $mapping->setMagentoAttribute($data['magento_attribute']);
                $this->mappingResource->save($mapping);

                $this->messageManager->addSuccessMessage(__('Attributes mapped successfully'));
                $this->dataPersistor->clear('mapping_data');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'entity_id' => $mapping->getEntityId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->dataPersistor->set('mapping_data', $data);
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
                return $resultRedirect->setPath('facebook_shop_integration/mapping/edit', ['entity_id' => $data['entity_id']]);
            }
        }
    }
}
