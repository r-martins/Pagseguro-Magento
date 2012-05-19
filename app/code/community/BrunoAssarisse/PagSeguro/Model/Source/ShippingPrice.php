<?php
/**
 * PagSeguro Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   payment
 * @package    BrunoAssarisse_PagSeguro
 * @copyright  Copyright (c) 2010 Bruno Assarisse (www.assarisse.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Bruno Assarisse <bruno@assarisse.com.br>
 */
/**
 * PagSeguro Shipping Price Source
 *
 */

class BrunoAssarisse_PagSeguro_Model_Source_ShippingPrice
{
	public function toOptionArray ()
	{
		$options = array();
        
        $options['separated'] = Mage::helper('adminhtml')->__('Separado');
        $options['product'] = Mage::helper('adminhtml')->__('Como produto');
        $options['grouped'] = Mage::helper('adminhtml')->__('Agrupado');
        
		return $options;
	}

}