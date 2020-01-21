<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceit\OscommerceToMagento\Block\Adminhtml;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Datacheck block
 */
class Datacheck extends Template
{
    /**
     * @var modelExternalDb
     */
    protected $modelExternalDb;
    /**
     * @var coreSession
     */
    protected $coreSession;
    
    /**
     * @var formKey
     */
    protected $formKey;

    /**
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param Embraceit\OscommerceToMagento\Model\ExternalDb    $modelExternalDb
     * @param Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param Magento\Framework\Data\Form\FormKey               $formKey
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ModelExternalDb $modelExternalDb,
        SessionManagerInterface $coreSession,
        FormKey $formKey,
        array $data = []
    ) {
        $this->modelExternalDb = $modelExternalDb;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Category count
     *
     * @return integer
     */
    public function getCategoryInfo()
    {
        return $this->modelExternalDb->getCategoryCount();
    }

    /**
     * Import categories from Oscommerce to magento
     *
     * @return string
     * @throws \Magento\Exception if a non-existing or not imported
     */
    public function getCategoryAdd()
    {

        return $this->modelExternalDb->addCategory();
    }
    /**
     * number of products in oscommerce database that need to import
     *
     * @return integer
     */
    public function getProductCount()
    {
        return $this->modelExternalDb->getTotalProductsCount();
    }
    /**
     * number of products in oscommerce database that need to import
     *
     * @return integer
     */
    public function getProductInfo()
    {
        return $this->modelExternalDb->queryProductsInfo();
    }
    public function getAttributeSet($id)
    {
        return $this->modelExternalDb->getAtributeSet($id);
    }
    public function getQueryCustomOptions($option)
    {
        return $this->modelExternalDb->queryCustomOptions($option);
    }

    public function getLanguageByID($id)
    {
        return $this->modelExternalDb->getLanguageById($id);
    }
    public function getProgressAjax()
    {
        return $this->modelExternalDb->getValue();
    }
    public function getUnsetProgressBar()
    {
        return $this->modelExternalDb->setValue(0);
    }

    public function getProductAtr()
    {
        return $this->modelExternalDb->queryCustomProductAttributes();
    }
    public function getAtributeByProductId($id)
    {
        return $this->modelExternalDb->queryGetCustomProductAttributesById($id);
    }
    public function getOptionDetial($id)
    {
        return $this->modelExternalDb->queryGetOptionDetial($id);
    }
    public function getOptionData($optionId, $productId)
    {
        return $this->modelExternalDb->queryGetOptionData($optionId, $productId);
    }
    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getCustomOptionCount()
    {
        return $this->modelExternalDb->getTotalCustomOptionCount();
    }
    public function getProductImagePath()
    {
        return $this->modelExternalDb->getImagePathProduct();
    }
    public function getCategoryImagePath()
    {
        return $this->modelExternalDb->getImagePathCategory();
    }
    // public function getAttributesetLang()
    // {
    //     return $this->modelExternalDb->getAttributeTranslationCount();
    // }
    public function getAttributeSets()
    {
        return $this->modelExternalDb->getAllAtrributeSet();
    }
    public function getAtributeByName()
    {
        return $this->modelExternalDb->getAttributeByName();
    }
}
