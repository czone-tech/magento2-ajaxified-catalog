<?php
/**
 * Created by PhpStorm.
 * User: ashish
 * Date: 4/5/16
 * Time: 5:22 PM
 */

namespace CzoneTech\AjaxifiedCatalog\Controller\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class View extends \Magento\Catalog\Controller\Category\View
{

    protected $_layerResolver;


    protected $_jsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context, $catalogDesign, $catalogSession, $coreRegistry, $storeManager,
            $categoryUrlPathGenerator, $resultPageFactory, $resultForwardFactory, $layerResolver, $categoryRepository);
        $this->_layerResolver = $layerResolver;
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * Category view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        $category = $this->_initCategory();
        if ($category) {
            $this->_layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->_catalogSession->setLastViewedCategoryId($category->getId());


            $page = $this->resultPageFactory->create();


            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $page->getConfig()->setPageLayout($settings->getPageLayout());
            }
            if ($category->getIsAnchor()) {
                $type = $category->hasChildren() ? 'layered' : 'layered_without_children';
            } else {
                $type = $category->hasChildren() ? 'default' : 'default_without_children';
            }

            if (!$category->hasChildren()) {
                // Two levels removed from parent.  Need to add default page type.
                $parentType = strtok($type, '_');
                $page->addPageLayoutHandles(['type' => $parentType]);
            }
            $page->addPageLayoutHandles(['type' => $type, 'id' => $category->getId()]);

            // apply custom layout update once layout is loaded
            $layoutUpdates = $settings->getLayoutUpdates();
            if ($layoutUpdates && is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $page->addUpdate($layoutUpdate);
                }
            }

            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($category))
                ->addBodyClass('category-' . $category->getUrlKey());

            if($this->getRequest()->getParam('ajax') == 1){
                return $this->_jsonFactory->create()->setData(['html' => $page]);
            }else{
                return $page;
            }

        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }

}