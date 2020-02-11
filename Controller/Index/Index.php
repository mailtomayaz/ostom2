<?php
/**
 * Copyright © Khaysoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceit\OscommerceToMagento\Controller\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends Action
{

    private $scopeConfig;
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    public function execute()
    {
        $hostName=$this->scopeConfig->getValue('firstsection/firstgroup/DbHostName');
        $databaseName=$this->scopeConfig->getValue('firstsection/firstgroup/DbName');
        $databseUserName=$this->scopeConfig->getValue('firstsection/firstgroup/DbUserName');
        $databsePassword=$this->scopeConfig->getValue('firstsection/firstgroup/DbPassword');
        $osVersion=$this->scopeConfig->getValue('firstsection/firstgroup/OscVersion');
    }
}
