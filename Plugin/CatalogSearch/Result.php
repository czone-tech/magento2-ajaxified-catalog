<?php
/**
 * Created by PhpStorm.
 * User: ashish
 * Date: 7/10/16
 * Time: 5:19 PM
 */

namespace CzoneTech\AjaxifiedCatalog\Plugin\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Result
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectManager;
    }

    public function aroundExecute(
        \Magento\CatalogSearch\Controller\Result\Index $subject,
        \Closure $method
    ){
        if($subject->getRequest()->getParam('ajax') == 1){
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
            /* @var $query \Magento\Search\Model\Query */
            $query = $this->_queryFactory->get();

            $query->setStoreId($this->_storeManager->getStore()->getId());

            $resultJson = $this->_resultJsonFactory->create();

            if ($query->getQueryText() != '') {
                if ($this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->isMinQueryLength()) {
                    $query->setId(0)->setIsActive(1)->setIsProcessed(1);
                } else {
                    $query->saveIncrementalPopularity();

                    if ($query->getRedirect()) {
                        $data = [
                            'success' => true,
                            'redirect_url' => $query->getRedirect()
                        ];
                        return $resultJson->setData($data);
                    }
                }

                $this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->checkNotes();
                $resultsBlockHtml = $this->_resultPageFactory->create()->getLayout()->getBlock('search.result')
                    ->toHtml();

                return $this->_resultJsonFactory->create()->setData(['success' => true, 'html' => [
                    'products_list' => $resultsBlockHtml
                ]]);
                return $data;
            } else {
                $data = [
                    'success' => true,
                    'redirect_url' => $this->_redirect->getRedirectUrl()
                ];
                return $resultJson->setData($data);
            }
        }else{
            return $method();
        }

    }

}