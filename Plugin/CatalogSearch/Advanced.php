<?php

namespace CzoneTech\AjaxifiedCatalog\Plugin\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogSearch\Model\Advanced as ModelAdvanced;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Advanced
{
/**
     * Url factory
     *
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * Catalog search advanced
     *
     * @var ModelAdvanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Construct
     *
     * @param ModelAdvanced $catalogSearchAdvanced
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        ModelAdvanced $catalogSearchAdvanced,
        UrlFactory $urlFactory,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_urlFactory = $urlFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function aroundExecute(\Magento\CatalogSearch\Controller\Advanced\Result $subject, \Closure $method) {
        if ($subject->getRequest()->getParam('ajax') == 1) {
            try {
                $this->_catalogSearchAdvanced->addFilters($subject->getRequest()->getQueryValue());
                $resultsBlockHtml = $this->_resultPageFactory->create()->getLayout()->getBlock('search_result_list')->toHtml();

                return $this->_resultJsonFactory->create()->setData([
                    'success' => true,
                    'html' => [
                        'products_list' => $resultsBlockHtml
                    ]
                ]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $defaultUrl = $this->_urlFactory->create()
                    ->addQueryParams($this->getRequest()->getQueryValue())
                    ->getUrl('*/*/');
                $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
            }
        } else {
            return $method();
        }

    }

}