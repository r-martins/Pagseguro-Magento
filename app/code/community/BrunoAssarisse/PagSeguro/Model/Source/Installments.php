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
 * PagSeguro Installments Source
 *
 */

class BrunoAssarisse_PagSeguro_Model_Source_Installments
{
	public function toOptionArray ()
	{
		$options = array();
        
        $options['0'] = Mage::helper('adminhtml')->__('Desativado (1x)');
        $options['2'] = Mage::helper('adminhtml')->__('Até 2x');
        $options['3'] = Mage::helper('adminhtml')->__('Até 3x');
        $options['4'] = Mage::helper('adminhtml')->__('Até 4x');
        $options['5'] = Mage::helper('adminhtml')->__('Até 5x');
        $options['6'] = Mage::helper('adminhtml')->__('Até 6x');
        $options['7'] = Mage::helper('adminhtml')->__('Até 7x');
        $options['8'] = Mage::helper('adminhtml')->__('Até 8x');
        $options['9'] = Mage::helper('adminhtml')->__('Até 9x');
        $options['10'] = Mage::helper('adminhtml')->__('Até 10x');
        $options['11'] = Mage::helper('adminhtml')->__('Até 11x');
        $options['12'] = Mage::helper('adminhtml')->__('Até 12x');
        $options['13'] = Mage::helper('adminhtml')->__('Até 13x');
        $options['14'] = Mage::helper('adminhtml')->__('Até 14x');
        $options['15'] = Mage::helper('adminhtml')->__('Até 15x');
        $options['16'] = Mage::helper('adminhtml')->__('Até 16x');
        $options['17'] = Mage::helper('adminhtml')->__('Até 17x');
        $options['18'] = Mage::helper('adminhtml')->__('Até 18x');
        
		return $options;
	}

}