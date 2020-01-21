<?php
namespace Embraceit\OscommerceToMagento\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Embraceit\OscommerceToMagento\Model\ResourceModel\ProductData as productResourceModel;

class ProductData extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(productResourceModel::class);
    }
}
