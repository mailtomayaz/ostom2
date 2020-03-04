<?php
/**
 * Copyright © Embraceit, Inc. All rights reserved.
 */

namespace Embraceit\OscommerceToMagento\Model;

use Magento\Framework\Option\ArrayInterface;

/**
 * admin configuration oscommerce versions array
 *
 */
class AdminArray implements ArrayInterface
{

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function toOptionArray()
    {
        $arrayValue = [
            ['value' => '1.0.0', 'label' => __('1.0.0')],
            // ['value' => '2.0.0', 'label' => __('2.0.0')],
            // ['value' => '3.0.0', 'label' => __('3.0.0')],
        ];
        return $arrayValue;
    }
}
