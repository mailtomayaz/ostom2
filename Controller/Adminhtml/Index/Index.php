<?php
/**
 * Copyright © Embraceit, Inc. All rights reserved.
 */

namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Index;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Main Controller for import
 */
class Index extends Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     * @var Embraceit\OscommerceToMagento\Model\ExternalDb
     */
    protected $modelExternalDb;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        PageFactory $PageFactory,
        ModelExternalDb $modelExternalDb,
        ManagerInterface $messageManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $PageFactory;
        $this->modelExternalDb = $modelExternalDb;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
