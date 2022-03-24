<?php
/**
 * Class Delete
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
 * Class Delete
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory
     */
    protected $mappingModel;

    /**
     * @var \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping
     */
    protected $mappingResource;

    /**
     * Delete constructor.
     *
     * @param Action\Context $context
     * @param \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory $mappingModel
     * @param \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping $mappingResource
     */
    public function __construct(
        Action\Context $context,
        \Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMappingFactory $mappingModel,
        \Sparsh\FacebookShopIntegration\Model\ResourceModel\FacebookShopAttributeMapping $mappingResource
    ) {
        $this->mappingModel = $mappingModel;
        $this->mappingResource = $mappingResource;
        parent::__construct($context);
    }

    /**
     * Delete Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('entity_id');
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            foreach ($ids as $id) {
                $mapping = $this->mappingModel->create();
                $this->mappingResource->load($mapping, $id);
                $this->mappingResource->delete($mapping);
            }
            $this->messageManager->addSuccessMessage(
                __('Attribute mapping has been deleted successfully.')
            );
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
        $this->messageManager->addErrorMessage(
            __('We can\'t find attribute to delete.')
        );
        return $resultRedirect->setPath('*/*/');
    }
}
