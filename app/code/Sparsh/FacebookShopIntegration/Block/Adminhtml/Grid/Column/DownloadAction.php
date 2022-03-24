<?php
/**
 * Class DownloadAction
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Block\Adminhtml\Grid\Column;

/**
 * Class DownloadAction
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class DownloadAction extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add to column download link
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'getDownloadLink'];
    }

    /**
     * Return download csv link
     *
     * @param string                                 $value value
     * @param \Magento\Framework\Model\AbstractModel $row   row
     *
     * @return string
     */
    public function getDownloadLink($value, $row)
    {
        $fileNameArray = explode("/", $row->getData('message'));
        $fileName = "";
        if (isset($fileNameArray[4])) {
            $fileName = $fileNameArray[4];
        }

        if ($row->getData('generated_by') == 'cron') {
            return '';
        }

        return '<a href="'.$this->getUrl('*/csvlog/download', ['filename'=> $fileName]).'" title="'.__("Download CSV File").'">'. __("Download"). '</a>';
    }
}
