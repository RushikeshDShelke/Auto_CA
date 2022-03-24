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
namespace Delhivery\Lastmile\Controller\Adminhtml;

abstract class Location extends \Magento\Backend\App\Action
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
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\LocationRepositoryInterface $locationRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->coreRegistry       = $coreRegistry;
        $this->locationRepository = $locationRepository;
        $this->resultPageFactory  = $resultPageFactory;
        parent::__construct($context);
    }
}
