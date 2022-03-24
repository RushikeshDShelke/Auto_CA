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
namespace Delhivery\Lastmile\Controller\Adminhtml\Awb;

class MassDelete extends \Delhivery\Lastmile\Controller\Adminhtml\Awb\MassAction
{
    /**
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return $this
     */
    protected function massAction(\Delhivery\Lastmile\Api\Data\AwbInterface $awb)
    {
        $this->awbRepository->delete($awb);
        return $this;
    }
}
