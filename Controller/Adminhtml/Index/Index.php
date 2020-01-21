<?php
/**
 * Copyright © Khaysoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Framework\Message\ManagerInterface;

class Index extends Action
{
    private $scopeConfig;
    private $pageFactory;
    protected $modelExternalDb;
    protected $messageManager;
    
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        PageFactory $PageFactory,
        ModelExternalDb $modelExternalDb,
        ManagerInterface  $messageManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $PageFactory;
        $this->modelExternalDb=$modelExternalDb;
        $this->messageManager=$messageManager;
        parent::__construct($context);
    }
    public function execute()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/DbHostName');
        $database = $this->scopeConfig->getValue('firstsection/firstgroup/DbName');
        $user = $this->scopeConfig->getValue('firstsection/firstgroup/DbUserName');
        $dbpass = $this->scopeConfig->getValue('firstsection/firstgroup/DbPassword');
        $dbversion = $this->scopeConfig->getValue('firstsection/firstgroup/OscVersion');
        $resultPage = $this->pageFactory->create();
        $this->modelExternalDb->initDb();
        $resultPage->getLayout()->getBlock('dataimprortblock')->setDbHost($host);
        $resultPage->getLayout()->getBlock('dataimprortblock')->setDatabase($database);
        $resultPage->getLayout()->getBlock('dataimprortblock')->setDbUser($user);
        $resultPage->getLayout()->getBlock('dataimprortblock')->setDbPass($dbpass);
        $resultPage->getLayout()->getBlock('dataimprortblock')->setDbVersion($dbversion);
        return $resultPage;
    }
}
