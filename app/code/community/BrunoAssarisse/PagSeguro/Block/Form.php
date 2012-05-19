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
 * PagSeguro Payment Form Block
 *
 */

class BrunoAssarisse_PagSeguro_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('brunoassarisse_pagseguro/form.phtml');
    }
    
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        return parent::_beforeToHtml();
    }

    /**
     * Retorna o singleton do PagSeguro
     *
     * @return BrunoAssarisse_Pagseguro_Model_Payment
     */
    public function getPagSeguro()
    {
        return Mage::getSingleton('brunoassarisse_pagseguro/payment');
    }
    
    /**
     * Retorna valor total do pedido a ser concluído
     *
     * @return float
     */
    public function getFinalValue()
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $totals = $quote->getTotals();
        return $totals['grand_total']->_data['value'];
    }
    
    protected function _prepareForm()
    {
        $pagseguro = $this->getPagSeguro();
        $helper = Mage::helper("brunoassarisse_pagseguro");
        
        $msgAdd = $pagseguro->getConfigData('msg_add');
        $installmentsAdd = $pagseguro->getConfigData('installments_add');
        $installmentsUpfront = $pagseguro->getConfigData('installments_upfront');
        
        $installments = $upfrontPrice = $upfrontDiscount = '';
        $finalValue = $this->getFinalValue();
        
        $installmentsShow = (boolean) ($pagseguro->getConfigData('installments_show') && $finalValue);
        $installmentsUpfrontShow = (boolean) ($installmentsUpfront && $installmentsAdd != 0);
        
        if ($installmentsShow) {
            $installments = $helper->calculateInstallments($finalValue, $installmentsAdd);
        
            if ($installmentsUpfrontShow) {
                list($upfrontPrice, $upfrontDiscount) = $helper->calculateUpfrontPrice($finalValue, $installmentsAdd);
            }
        }
        
        $this->addData(array(
            'message' => $msgAdd,
            'installments' => $installments,
            'upfront_price' => $upfrontPrice,
            'upfront_discount' => $upfrontDiscount,
            'show_message' => (boolean) $msgAdd,
            'show_installments' => $installmentsShow,
            'show_upfront_price' => $installmentsUpfrontShow,
            'show_form' => (boolean) ($msgAdd || $installmentsShow),
        ));
    }
    
}