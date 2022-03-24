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
namespace Delhivery\Lastmile\Block\Adminhtml\Pincode\Edit\Buttons;

class Generic
{
    /**
     * Widget Context
     * 
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * Manage Pincode Repository
     * 
     * @var \Delhivery\Lastmile\Api\PincodeRepositoryInterface
     */
    protected $pincodeRepository;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository
    ) {
        $this->context           = $context;
        $this->pincodeRepository = $pincodeRepository;
    }

    /**
     * Return Manage Pincode ID
     *
     * @return int|null
     */
    public function getPincodeId()
    {
        try {
            return $this->pincodeRepository->getById(
                $this->context->getRequest()->getParam('pincode_id')
            )->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
