<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */

namespace Embraceitechnologies\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceitechnologies\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Ajax controller for product custom option import
 */
class Addcustomoption extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     * @var Embraceitechnologies\OscommerceToMagento\Model\ExternalDb
     */
    protected $modelExternalDb;
    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    protected $context;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ModelExternalDb $modelExternalDb,
        JsonFactory $jsonFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->modelExternalDb = $modelExternalDb;
        $this->jsonFactory = $jsonFactory;
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //ajax get request data
        $startLimit = $this->getRequest()->getParam('start_limit');
        $totalLimit = $this->getRequest()->getParam('total_limit');
        $status = $this->modelExternalDb->addCustomOption($startLimit, $totalLimit);
        return $this->jsonFactory->create()->setData(['importStatus' => $status]);
    }
}
