<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Athlete2\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddOxGalleryVideoProductAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * @return \array[][]
     */
    protected function getFields(){
        return [
            Product::ENTITY => [
                'ox_gallery_video' => [
                    'apply_to' => '',
                    'backend' => '',
                    'comparable' => false,
                    'default' => null,
                    'filterable' => false,
                    'frontend' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'Video',
                    'input' => 'text',
                    'is_filterable_in_grid' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
					'label' => 'Video for Gallery',
					'description' => __('Add relative path to video file in media folder, without file type. Supported video formats: webm, mp4, ogg. Starting path is pub/media, so if your video placed in pub/media/video/yourvideo.mp4 then you should add following: video/yourvideo'),
                    'option' => ['values' => [""]],
                    'required' => false,
                    'searchable' => false,
                    'sort_order' => '10',
                    'source' => '',
                    'type' => 'varchar',
                    'unique' => false,
                    'used_in_product_listing' => false,
                    'user_defined' => true,
                    'visible' => true,
                    'visible_on_front' => false,
                ],
                'ox_gallery_video_index' => [
                    'apply_to' => '',
                    'backend' => '',
                    'comparable' => false,
                    'default' => 2,
                    'description' => __('Video will replace existing image in defined position. Image will be used as fallback.'),
                    'filterable' => false,
                    'frontend' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'Video',
                    'input' => 'text',
                    'is_filterable_in_grid' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'label' => 'Position in Gallery',
                    'option' => ['values' => ["2"]],
                    'required' => false,
                    'searchable' => false,
                    'sort_order' => '20',
                    'source' => '',
                    'type' => 'int',
                    'unique' => false,
                    'used_in_product_listing' => false,
                    'user_defined' => true,
                    'visible' => true,
                    'visible_on_front' => false,
                ],
				'ox_gallery_video_listing_hover' => [
					'apply_to' => '',
					'backend' => '',
					'comparable' => false,
					'default' => '0',
					'filterable' => false,
					'frontend' => '',
					'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
					'group' => 'Video',
					'input' => 'boolean',
					'label' => 'Show on Hover in product Listing',
					'required' => false,
					'searchable' => false,
					'sort_order' => '30',
					'source' => Boolean::class,
					'type' => 'int',
					'unique' => false,
					'used_in_product_listing' => true,
					'user_defined' => true,
					'visible' => true,
					'visible_on_front' => false,
				],
				'ox_gallery_video_stop_on_click' => [
					'apply_to' => '',
					'backend' => '',
					'comparable' => false,
					'default' => '1',
					'filterable' => false,
					'frontend' => '',
					'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
					'group' => 'Video',
					'input' => 'boolean',
					'label' => 'Stop Video on Click',
					'required' => false,
					'searchable' => false,
					'sort_order' => '40',
					'source' => Boolean::class,
					'type' => 'int',
					'unique' => false,
					'used_in_product_listing' => true,
					'user_defined' => true,
					'visible' => true,
					'visible_on_front' => false,
					'option' => ['values' => ["1"]],
				],
				'ox_gallery_video_autoplay' => [
					'apply_to' => '',
					'backend' => '',
					'comparable' => false,
					'default' => '1',
					'filterable' => false,
					'frontend' => '',
					'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
					'group' => 'Video',
					'input' => 'boolean',
					'label' => 'Autoplay Video',
					'required' => false,
					'searchable' => false,
					'sort_order' => '50',
					'source' => Boolean::class,
					'type' => 'int',
					'unique' => false,
					'used_in_product_listing' => true,
					'user_defined' => true,
					'visible' => true,
					'visible_on_front' => false,
					'option' => ['values' => ["1"]],
				],
				'ox_gallery_video_loop' => [
					'apply_to' => '',
					'backend' => '',
					'comparable' => false,
					'default' => '1',
					'filterable' => false,
					'frontend' => '',
					'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
					'group' => 'Video',
					'input' => 'boolean',
					'label' => 'Loop Video',
					'required' => false,
					'searchable' => false,
					'sort_order' => '60',
					'source' => Boolean::class,
					'type' => 'int',
					'unique' => false,
					'used_in_product_listing' => true,
					'user_defined' => true,
					'visible' => true,
					'visible_on_front' => false,
					'option' => ['values' => ["1"]],
				],
				'ox_gallery_video_controls' => [
					'apply_to' => '',
					'backend' => '',
					'comparable' => false,
					'default' => '0',
					'filterable' => false,
					'frontend' => '',
					'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
					'group' => 'Video',
					'input' => 'boolean',
					'label' => 'Show Video Controls',
					'required' => false,
					'searchable' => false,
					'sort_order' => '70',
					'source' => Boolean::class,
					'type' => 'int',
					'unique' => false,
					'used_in_product_listing' => true,
					'user_defined' => true,
					'visible' => true,
					'visible_on_front' => false,
				],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @return AddOxGalleryVideoProductAttribute|void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->getFields() as $entityTypeId => $fields) {
            foreach ($fields as $code => $attr) {
                $eavSetup->addAttribute($entityTypeId, $code, $attr);
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->getFields() as $entityTypeId => $fields) {
            foreach ($fields as $code => $attr) {
                $eavSetup->removeAttribute($entityTypeId, $code, $attr);
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}