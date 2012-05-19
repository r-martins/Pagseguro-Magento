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
 * PagSeguro Payment Info Block
 *
 */

class BrunoAssarisse_PagSeguro_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('brunoassarisse_pagseguro/info.phtml');
    }
    
    protected function _beforeToHtml()
    {
        $this->_prepareInfo();
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
    
    protected function _prepareInfo()
    {
        $pagseguro = $this->getPagSeguro();
        if (!$order = $this->getInfo()->getOrder()) {
            $order = $this->getInfo()->getQuote();
        }
        
        $transactionId = $this->getInfo()->getPagseguroTransactionId();
        $paymentMethod = $this->getInfo()->getPagseguroPaymentMethod();
        
        if ($paymentMethod == 'Boleto' AND $order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
            $paymentMethod .= ' (<a href="' . $pagseguro->getPagSeguroBoletoUrl($transactionId) . '" onclick="this.target=\'_blank\'">reemitir</a>)';
        }

        $this->addData(array(
            'show_paylink' => (boolean) !$transactionId && $order->getState() == Mage_Sales_Model_Order::STATE_NEW,
            'pay_url' => $pagseguro->getOrderPlaceRedirectUrl($order->getId()),
            'show_info' => (boolean) $transactionId,
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
        ));
    }
}