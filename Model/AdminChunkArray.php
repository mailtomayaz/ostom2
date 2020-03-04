<?php
/**
 * Copyright © Embraceit, Inc. All rights reserved.
 */

namespace Embraceit\OscommerceToMagento\Model;

use Magento\Framework\Option\ArrayInterface;

/**
 * admin configuration chunk size array
 *
 */
class AdminChunkArray implements ArrayInterface
{

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function toOptionArray()
    {
        $arrayValue = [
            ['value' => '500', 'label' => __('500')],
            ['value' => '1000', 'label' => __('1000')],
            ['value' => '1500', 'label' => __('1500')],
        ];
        return $arrayValue;
    }
}
