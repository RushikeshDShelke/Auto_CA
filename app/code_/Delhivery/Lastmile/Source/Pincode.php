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
namespace Delhivery\Lastmile\Source;

class Pincode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Manage Pincode repository
     * 
     * @var \Delhivery\Lastmile\Api\PincodeRepositoryInterface
     */
    protected $pincodeRepository;

    /**
     * Search Criteria Builder
     * 
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter Builder
     * 
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Options
     * 
     * @var array
     */
    protected $options;

    /**
     * constructor
     * 
     * @param \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->pincodeRepository     = $pincodeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
    }

    /**
     * Retrieve all Manage Pincodes as an option array
     *
     * @return array
     * @throws StateException
     */
    public function getAllOptions()
    {
        if (empty($this->options)) {
            $options = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->pincodeRepository->getList($searchCriteria);
            foreach ($searchResults->getItems() as $pincode) {
                $options[] = [
                    'value' => $pincode->getPincodeId(),
                    'label' => $pincode->getDistrict(),
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
