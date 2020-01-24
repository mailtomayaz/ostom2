<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceit\OscommerceToMagento\Block\Adminhtml;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Backend\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Datacheck block
 */
class Datacheck extends Template
{
    const HOST_NAME = 'Configure Host Name';
    const DATABASE_NAME = 'Configure Database';
    const USER_NAME = 'Configure User Name';
    const DATABAS_PASSWORD = 'Configure Password';
    const OSCOMMERCE_VERSION = 'Configure OsCommerce Version';
    const CATEGORY_IMAGE_PATH = 'Category Image Path';
    const PRODUCT_IMAGE_PATH='Product Image Path';
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
    protected $scopeConfig;
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
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->modelExternalDb = $modelExternalDb;
        $this->formKey = $formKey;
        $this->scopeConfig = $scopeConfig;
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
    /**
     * Attribute set information by id
     *
     * @param int $id
     * @return array
     */
    public function getAttributeSet($id)
    {
        return $this->modelExternalDb->getAtributeSet($id);
    }
    /**
     * Undocumented function
     *
     * @param string $option
     * @return array
     */
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
        $nCount = $this->modelExternalDb->getTotalCustomOptionCount();
        if ($nCount == null) {
            $nCount = 0;
        }
        return $nCount;
    }
    public function getProductImagePath()
    {
       // return $this->modelExternalDb->getImagePathProduct();

        $host = $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
        if ($host !== null) {
            return $host;
        } else {
            return self::PRODUCT_IMAGE_PATH;
        }
    }
    public function getCategoryImagePath()
    {
        //return $this->modelExternalDb->getImagePathCategory();
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
        if ($host !== null) {
            return $host;
        } else {
            return self::CATEGORY_IMAGE_PATH;
        }
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
    //public function
    public function getDatabaseHostName()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/DbHostName');
        if ($host !== null) {
            return $host;
        } else {
            return self::HOST_NAME;
        }
    }
    public function getDatabaseName()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/DbName');
        if ($host !== null) {
            return $host;
        } else {
            return self::DATABASE_NAME;
        }
    }

    public function getDatabaseUser()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/DbUserName');
        if ($host !== null) {
            return $host;
        } else {
            return self::USER_NAME;
        }
    }
    public function getDatabasePassword()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/DbPassword');
        if ($host !== null) {
            return $host;
        } else {
            return self::DATABAS_PASSWORD;
        }
    }
    public function getOsCommerceVersion()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/OscVersion');
        if ($host !== null) {
            return $host;
        } else {
            return self::OSCOMMERCE_VERSION;
        }
    }
}
