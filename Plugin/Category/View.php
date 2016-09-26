<?php
/**
 * Copyright © 2016 CzoneTechnologies. All rights reserved.
 * For support requests, contact
 * ashish@czonetechnologies.com
 */

namespace CzoneTech\AjaxifiedCatalog\Plugin\Category;


use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\Page;

class View
{
    protected $_resultJsonFactory;

    public function __construct(JsonFactory $resultJsonFactory){
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    public function aroundExecute(\Magento\Catalog\Controller\Category\View $subject, \Closure
    $method){
        $response = $method();
        if($response instanceof Page){
            if($subject->getRequest()->getParam('ajax') == 1){

                $subject->getRequest()->getQuery()->set('ajax', null);
                $requestUri = $subject->getRequest()->getRequestUri();
                $requestUri = preg_replace('/(\?|&)ajax=1/', '', $requestUri);
                $subject->getRequest()->setRequestUri($requestUri);

                //$ajaxParam = $subject->getRequest()->getQuery('ajax');
                //$requestUri = $subject->getRequest()->getRequestUri();

                $productsBlockHtml = $response->getLayout()->getBlock('category.products')
                    ->toHtml();
                $leftNavBlockHtml = $response->getLayout()->getBlock('catalog.leftnav')
                    ->toHtml();
                return $this->_resultJsonFactory->create()->setData(['success' => true, 'html' => [
                    'products_list' => $productsBlockHtml,
                    'filters' => $leftNavBlockHtml
                ]]);
            }
        }
        return $response;
    }
}