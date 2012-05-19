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
 * PagSeguro Paylink Block
 *
 */

class BrunoAssarisse_PagSeguro_Block_Paylink extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('brunoassarisse_pagseguro/paylink.phtml');
    }

    /**
     * Retorna modelo do pedido atual
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
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
     * Verifica se o botão de pagamento deve ser mostrado
     *
     * @return boolean
     */
    public function isShowPaylink()
    {
        $order = $this->getOrder();
        return (bool) ($order->getPayment()->getMethod() == $this->getPagSeguro()->getCode() AND $order->getState() == Mage_Sales_Model_Order::STATE_NEW);
    }

    /**
     * Retorna URL de pagamento
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        return $this->getPagSeguro()->getOrderPlaceRedirectUrl($this->getOrder()->getId());
    }
}