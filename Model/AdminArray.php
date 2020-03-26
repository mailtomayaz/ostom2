<?php
/**
 * Copyright © Embraceit, Inc. All rights reserved.
 */

namespace Embraceitechnologies\OscommerceToMagento\Model;

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
            // ['value' => '1.0.0', 'label' => __('1.0.0')],
            ['value' => '2.3.4.1', 'label' => __('2.3.4.1')],
            ['value' => '2.3.3.3', 'label' => __('2.3.3.3')],
            ['value' => '2.3.3.2', 'label' => __('2.3.3.2')],
            ['value' => '2.3.3.1', 'label' => __('2.3.3.1')],
        ];
        return $arrayValue;
    }
}
