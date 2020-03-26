<?php
/**
 * Copyright © Khaysoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceitechnologies\OscommerceToMagento\Controller\Index;

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
        $hostName=$this->scopeConfig->getValue('firstsection/firstgroup/dbHostName');
        $databaseName=$this->scopeConfig->getValue('firstsection/firstgroup/osDbName');
        $databseUserName=$this->scopeConfig->getValue('firstsection/firstgroup/dbUserName');
        $databsePassword=$this->scopeConfig->getValue('firstsection/firstgroup/dbPassword');
        $osVersion=$this->scopeConfig->getValue('firstsection/firstgroup/oscVersion');
    }
}
