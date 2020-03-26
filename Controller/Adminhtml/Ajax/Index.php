<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */

namespace Embraceitechnologies\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceitechnologies\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Ajax controller for database connection check
 */

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    protected $modelExternalDb;
    protected $jsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
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
        if ($this->modelExternalDb->initDb()) {
            return $this->jsonFactory->create()->setData(['messageConnection' => 'Database Connected']);
        } else {
            return $this->jsonFactory->create()
                ->setData(['messageConnection' => 'Database Error:Please check your database credintials']);
        }
    }
}
