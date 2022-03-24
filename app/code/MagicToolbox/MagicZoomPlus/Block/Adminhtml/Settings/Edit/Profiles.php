<?php

namespace MagicToolbox\MagicZoomPlus\Block\Adminhtml\Settings\Edit;

use Magento\Backend\Block\Widget\Tabs;

/**
 * Tabs profiles
 */
class Profiles extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $activeTab = 'product';
        $profiles = [
            'default' => 'Defaults',
            'product' => 'Product page',
            'category' => 'Category page'
        ];

        foreach ($profiles as $id => $title) {
            $this->addTab(
                $id,
                [
                    'label' => __($title),
                    'title' => __($title),
                    'content' => $this->getLayout()->createBlock(
                        'MagicToolbox\MagicZoomPlus\Block\Adminhtml\Settings\Edit\Tab\Config',
                        $this->getNameInLayout().'.'.$id.'_tab',
                        ['data' => ['profile-id' => $id]]
                    )->toHtml(),
                    'class' => 'magictoolbox-'.$id.'-tab',
                    'active' => ($id == $activeTab)
                ]
            );
        }

        //NOTE: promo section for Sirv extension
        $this->addTab(
            'promo',
            [
                'label' => __('CDN and Image Processing'),
                'title' => __('CDN and Image Processing'),
                'content' => $this->getLayout()->createBlock(
                    'MagicToolbox\MagicZoomPlus\Block\Adminhtml\Settings\Edit\Tab\Promo',
                    $this->getNameInLayout().'.promo_tab',
                    ['data' => ['profile-id' => 'promo']]
                )->toHtml(),
                'class' => 'magictoolbox-promo-tab',
                'active' => false
            ]
        );

        return parent::_prepareLayout();
    }
}
