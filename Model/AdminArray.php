<?php
/**
 * Copyright © Khaysoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Embraceit\OscommerceToMagento\Model;

use Magento\Framework\Option\ArrayInterface;

class AdminArray implements ArrayInterface
{
   
  /**
   * Index action
   *
   * @return \Magento\Framework\Controller\Result\Redirect
   */
    public function toOptionArray()
    {
        $arrayValue=[
            ['value'=>'1.0.0','label'=>__('1.0.0')],
            ['value'=>'2.0.0','label'=>__('2.0.0')],
            ['value'=>'3.0.0','label'=>__('3.0.0')]
        ];
        return $arrayValue;
    }
}
