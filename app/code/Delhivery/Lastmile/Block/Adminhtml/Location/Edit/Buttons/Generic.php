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
namespace Delhivery\Lastmile\Block\Adminhtml\Location\Edit\Buttons;

class Generic
{
    /**
     * Widget Context
     * 
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * Manage Location Repository
     * 
     * @var \Delhivery\Lastmile\Api\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
    ) {
        $this->context            = $context;
        $this->locationRepository = $locationRepository;
    }

    /**
     * Return Manage Location ID
     *
     * @return int|null
     */
    public function getLocationId()
    {
        try {
            return $this->locationRepository->getById(
                $this->context->getRequest()->getParam('location_id')
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
