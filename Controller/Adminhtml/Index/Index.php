<?php
/**
 * Copyright © Embraceit, Inc. All rights reserved.
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
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
