<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Olegnax\Athlete2\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Xml\Parser;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Model\DynamicStyle\Generator as DynamicStyleGenerator;
use Olegnax\BannerSlider\Model\Group;
use Olegnax\BannerSlider\Model\Slides;
use Olegnax\Carousel\Model\Carousel;
use Olegnax\Carousel\Model\Slide;
use Olegnax\Core\Plugin\Backend\Magento\Cms\Model\Wysiwyg\Validator;

class Import extends Action
{

    const ADMIN_RESOURCE = 'Olegnax_Athlete2::import';
    const DEMO_DIR = 'code/Olegnax/Athlete2/Demos';

    protected $_filesystem;
    protected $_storeManager;
    /**
     * Dynamic Style generator
     *
     * @var DynamicStyleGenerator
     */
    protected $_DynamicStyleGenerator;
    private $_demo;
    private $_replace;
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var Parser
     */
    protected $_parser;

    /**
     * Constructor
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Parser $parser,
        DynamicStyleGenerator $generator,
        Registry $coreRegistry
    ) {
        $this->_filesystem = $filesystem;
        $this->_parser = $parser;
        $this->_DynamicStyleGenerator = $generator;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $subDir = $this->getRequest()->getParam('subdir', '');
        $this->_demo = $this->getRequest()->getParam('demo');
        $this->_replace = !(bool)$this->getRequest()->getParam('notreplace', '');
        if (!empty($this->_demo) && $this->demoFileExists($this->_demo, $subDir)) {
            if ($this->demoIsReadable($this->_demo, $subDir)) {
                $demoPath = $this->getDemoPath($this->_demo, $subDir);
                try {
                    $xmlArray = $this->_parser->load($demoPath)->xmlToArray();
                    if (is_array($xmlArray) && !empty($xmlArray) && isset($xmlArray['root'])) {
                        foreach ($xmlArray['root'] as $key => $value) {
                            $methodName = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($key))));
                            if (method_exists($this, $methodName)) {
                                call_user_func([$this, $methodName], $value, $this->_replace);
                            }
                        }
                        $this->clearCache();
                        $this->messageManager->addSuccess(__('%1 was successfully imported.', $this->convertString($this->_demo)));
                    }
                } catch (Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                $this->messageManager->addError(__('Cannot import this Demo.'));
            }
        } else {
            $this->messageManager->addError(__('This Demo no longer exists.'));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    protected function demoFileExists($demoId, $subDir = '')
    {
        $demoPath = $this->getDemoPath($demoId, $subDir);

        return file_exists($demoPath);
    }

    protected function getDemoPath($demoId, $subDir = '')
    {
        if (!empty($subDir)) {
            $subDir = $subDir . DIRECTORY_SEPARATOR;
        } else {
            $subDir = '';
        }
        return $this->getAbsolutePath(self::DEMO_DIR) . DIRECTORY_SEPARATOR . $subDir . $demoId . '.xml';
    }

    protected function getAbsolutePath($path)
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath($path);
    }

    protected function demoIsReadable($demoId, $subDir = '')
    {
        $demoPath = $this->getDemoPath($demoId, $subDir);

        return is_readable($demoPath);
    }

    public function clearCache()
    {
        /** @var TypeListInterface $cacheTypeList */
        $cacheTypeList = $this->_loadObject(TypeListInterface::class);
        /** @var Pool $CacheFrontendPool */
        $CacheFrontendPool = $this->_loadObject(Pool::class);
        $types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice',
        ];
        foreach ($types as $type) {
            $cacheTypeList->cleanType($type);
        }
        foreach ($CacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    public function convertString($demoId)
    {
        return ucwords(strtolower(str_replace(['-', '_', '\\', '/'], ' ', $demoId)));
    }

    public function setConfig($demoContent)
    {
        if (is_array($demoContent) && !empty($demoContent)) {
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            /** @var Factory $config */
            $config = $this->_loadObject(Factory::class);
            foreach ($demoContent as $section => $groups) {
                $configData = [
                    'section' => $section,
                    'website' => $website,
                    'store' => $store,
                    'groups' => $this->_prepareGroup($groups, $section),
                ];
                $config->create(['data' => $configData])->save();

                $this->_eventManager->dispatch('admin_system_config_save', [
                    'configData' => $configData,
                    'request' => $this->getRequest(),
                ]);
            }
        }
    }

    public function _prepareGroup($content, $section)
    {
        $groups = [];
        if (!empty($content)) {
            foreach ($content as $group_name => $group_fields) {
                $fields = [];
                if (!empty($group_fields)) {
                    foreach ($group_fields as $field => $value) {
                        if (null === $value) {
                            $value = '';
                        }
                        if (!is_string($value)) {
                            continue;
                        }
                        if (preg_match('/\.[a-z0-9]{3,4}$/i', $value)) {
                            $this->_setConfigImage([$section, $group_name, $field], $value);
                        }
                        $fields[$field] = ['value' => $value];
                    }
                }
                $groups[$group_name] = [
                    'fields' => $fields,
                ];
            }
        }

        return $groups;
    }

    private function _setConfigImage($fieldPath, $value)
    {
        $store = (int)$this->getRequest()->getParam('store', 0);
        $website = (int)$this->getRequest()->getParam('website', 0);
        /** @var ConfigInterface $config */
        $config = $this->_loadObject(ConfigInterface::class);
        if (is_array($fieldPath)) {
            $fieldPath = implode('/', $fieldPath);
        }
        if ($website) {
            $config->saveConfig($fieldPath, $value, ScopeInterface::SCOPE_WEBSITES, $website);
        } elseif ($store) {
            $config->saveConfig($fieldPath, $value, ScopeInterface::SCOPE_STORES, $store);
        } else {
            $config->saveConfig($fieldPath, $value);
        }
    }

    public function setBlocks($demoContent, $replace = true)
    {
        if (is_array($demoContent) && isset($demoContent['item']) && !empty($demoContent['item'])) {
            if (isset($demoContent['item']['identifier'])) {
                $demoContent = [$demoContent['item']];
            } else {
                $demoContent = $demoContent['item'];
            }
            /** @var Block $model */
            $model = $this->_loadObject(Block::class);
            $this->_coreRegistry->register(Validator::VARIABLE_TO_DISABLE, true);
            foreach ($demoContent as $item) {
                if (!array_key_exists('identifier', $item) || empty($item['identifier'])) {
                    continue;
                }

                $identifier = $item['identifier'];
                $collection = $model->getCollection()->addFieldToFilter('identifier', $identifier)->load();
                if ($replace) {
                    if ($collection->count()) {
                        foreach ($collection as $_block) {
                            $_block->delete();
                        }
                    }
                } else {
                    $index = 1;
                    while ($collection->count()) {
                        $index++;
                        $identifier = $item['identifier'] . '-' . $index;
                        $collection = $model->getCollection()->addFieldToFilter('identifier', $identifier)->load();
                    }
                    $item['identifier'] = $identifier;
                }

                $model->setData($item)->setIsActive(1)->setStores([0])->save();
            }
            $this->_coreRegistry->unregister(Validator::VARIABLE_TO_DISABLE);
        }
    }

    public function setPages($demoContent, $replace = true)
    {
        if (is_array($demoContent) && isset($demoContent['item']) && !empty($demoContent['item'])) {
            if (isset($demoContent['item']['identifier'])) {
                $demoContent = [$demoContent['item']];
            } else {
                $demoContent = $demoContent['item'];
            }
            /** @var Page $model */
            $model = $this->_loadObject(Page::class);
            $this->_coreRegistry->register(Validator::VARIABLE_TO_DISABLE, true);
            foreach ($demoContent as $item) {
                if (!array_key_exists('identifier', $item) || empty($item['identifier'])) {
                    continue;
                }

                $identifier = $item['identifier'];
                $collection = $model->getCollection()->addFieldToFilter('identifier', $identifier)->load();
                if ($replace) {
                    if ($collection->count()) {
                        foreach ($collection as $_block) {
                            $_block->delete();
                        }
                    }
                } else {
                    $index = 1;
                    while ($collection->count()) {
                        $index++;
                        $identifier = $item['identifier'] . '-' . $index;
                        $collection = $model->getCollection()->addFieldToFilter('identifier', $identifier)->load();
                    }
                    $item['identifier'] = $identifier;
                }

                $model->setData($item)->setIsActive(1)->setStores([0])->save();
            }
            $this->_coreRegistry->unregister(Validator::VARIABLE_TO_DISABLE);
        }
    }

    public function setBannersliders($demoContent)
    {
        if (is_array($demoContent) && isset($demoContent['item']) && !empty($demoContent['item'])) {
            if (isset($demoContent['item']['group_name'])) {
                $demoContent = [$demoContent['item']];
            } else {
                $demoContent = $demoContent['item'];
            }
            /** @var Group $model */
            $model = $this->_loadObject(Group::class);
            /** @var Slides $model2 */
            $model2 = $this->_loadObject(Slides::class);

            foreach ($demoContent as $item) {
                if (!array_key_exists('group_name', $item)) {
                    continue;
                }
                $groupCollection = $model->getCollection()->addFieldToFilter('identifier', $item['identifier'])->load();
                if (!empty($groupCollection)) {
                    foreach ($groupCollection as $_group) {
                        $slidesCollection = $model2->getCollection()->addFieldToFilter('slide_group', $_group->getId())->load();
                        if (!empty($slidesCollection)) {
                            foreach ($slidesCollection as $_slides) {
                                $_slides->delete();
                            }
                        }
                        $_group->delete();
                    }
                }

                $slides = [];
                if (array_key_exists('slides', $item)) {
                    if (!empty($item['slides']) && array_key_exists('item', $item['slides'])) {
                        $slides = $item['slides']['item'];
                    }
                    unset($item['slides']);
                }
                $current_model = $model->setData($item)->save();
                if (!empty($slides) && $group_id = $current_model->getId()) {
                    foreach ($slides as $_item) {
                        $model2->setData($_item)->setData('status', 1)->setData('store_id', 0)->setData('slide_group', $group_id)->save();
                    }
                }
            }
        }
    }

    public function setCarousels($demoContent)
    {
        if (is_array($demoContent) && isset($demoContent['item']) && !empty($demoContent['item'])) {
            if (isset($demoContent['item']['title'])) {
                $demoContent = [$demoContent['item']];
            } else {
                $demoContent = $demoContent['item'];
            }
            /** @var Carousel $model */
            $model = $this->_loadObject(Carousel::class);
            /** @var Slide $model2 */
            $model2 = $this->_loadObject(Slide::class);

            foreach ($demoContent as $item) {
                if (!array_key_exists('title', $item)) {
                    continue;
                }
                $carouselCollection = $model->getCollection()->addFieldToFilter('identifier', $item['identifier'])->load();
                if (!empty($carouselCollection)) {
                    foreach ($carouselCollection as $carousel) {
                        $slidesCollection = $model2->getCollection()->addFieldToFilter('carousel', $carousel->getIdentifier())->load();
                        if (!empty($slidesCollection)) {
                            foreach ($slidesCollection as $slides) {
                                $slides->delete();
                            }
                        }
                        $carousel->delete();
                    }
                }

                $slides = [];
                if (array_key_exists('slides', $item)) {
                    if (!empty($item['slides']) && array_key_exists('item', $item['slides'])) {
                        if (array_key_exists('carousel', $item['slides']['item'])) {
                            $slides[] = $item['slides']['item'];
                        } else {
                            $slides = $item['slides']['item'];
                        }

                    }
                    unset($item['slides']);
                }
                $current_model = $model->setData($item)->save();
                if (!empty($slides)) {
                    foreach ($slides as $_item) {
                        $model2->setData($_item)->setData('store_id', 0)->save();
                    }
                }
            }
        }
    }

}
