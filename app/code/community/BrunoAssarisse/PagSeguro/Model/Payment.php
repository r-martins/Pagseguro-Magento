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
 * @category   UOL
 * @package    BrunoAssarisse_PagSeguro
 * @copyright  Copyright (c) 2010 Bruno Assarisse (www.assarisse.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Bruno Assarisse <bruno@assarisse.com.br>
 */

class BrunoAssarisse_PagSeguro_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    
    protected $_code  = 'brunoassarisse_pagseguro';
    protected $_formBlockType = 'brunoassarisse_pagseguro/form';
    protected $_infoBlockType = 'brunoassarisse_pagseguro/info';
    
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    protected $_canCapture = true;
    
    protected $_order = null;

    /**
     *  Retorna pedido
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     *  Associa pedido
     *
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $this->_order = $order;
        } elseif (is_numeric($order)) {
            $this->_order = Mage::getModel('sales/order')->load($order);
        } else {
            $this->_order = null;
        }
        return $this;
    }
    
    /**
     * log
     * 
     * Registra log de eventos/erros.
     * 
     * @param string $message
     * @param integer $level
     * @param string $file
     * @param bool $forceLog
     */
    public function log($message, $level = null, $file = '', $forceLog = false) {
        Mage::log("PAGSEGURO - " . $message, $level, $file, $forceLog);
    }
    
    /**
     * getPagSeguroUrl
     * 
     * Retorna a URL de pagamento do PagSeguro
     * 
	 * @return string
     */ 
    public function getPagSeguroUrl()
    {
        return 'https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx';
    }
    
    /**
     * getPagSeguroNPIUrl
     * 
     * Retorna a URL de Notificação de Pagamento Instantâneo do PagSeguro
     * 
	 * @return string
     */ 
    public function getPagSeguroNPIUrl()
    {
        return 'https://pagseguro.uol.com.br/pagseguro-ws/checkout/NPI.jhtml';
    }
    
    /**
     * getPagSeguroBoletoUrl
     * 
     * Retorna a URL para emissão de boleto do PagSeguro
     * 
	 * @param string $transactionId ID da transação PagSeguro
     * 
	 * @return string
     */ 
    public function getPagSeguroBoletoUrl($transactionId, $escapeHtml = true)
    {
        $url = 'https://pagseguro.uol.com.br/checkout/imprimeBoleto.jhtml?resizeBooklet=n&code=' . $transactionId;
        if ($escapeHtml) {
            $url = Mage::helper("brunoassarisse_pagseguro")->escapeHtml($url);
        }
        return $url;
    }
    
	/**
	 * getOrderPlaceRedirectUrl
     * 
     * Cria a URL de redirecionamento ao PagSeguro, utilizando
     * o ID do pedido caso este seja informado
	 *
	 * @param int $orderId     ID pedido
	 *
	 * @return string
	 */
    public function getOrderPlaceRedirectUrl($orderId = 0)
	{
	   $params = array();
       $params['_secure'] = true;
       
	   if ($orderId != 0 && is_numeric($orderId)) {
	       $params['order_id'] = $orderId;
	   }
       
        return Mage::getUrl($this->getCode() . '/pay/redirect', $params);
    }
    
	/**
	 * createRedirectForm
     * 
     * Cria o formulário de redirecionamento ao PagSeguro
	 *
	 * @return string
     * 
     * @uses $this->getCheckoutFormFields()
	 */
    public function createRedirectForm()
    {
    	$form = new Varien_Data_Form();
        $form->setAction($this->getPagSeguroUrl())
            ->setId('pagseguro_checkout')
            ->setName('pagseguro_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        
        $fields = $this->getCheckoutFormFields();
        foreach ($fields as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        
        $submit_script = 'document.getElementById(\'pagseguro_checkout\').submit();';
        
		$html  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$html .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="pt-BR">';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Language" content="pt-br" />';
		$html .= '<meta name="language" content="pt-br" />';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
		$html .= '<style type="text/css">';
		$html .= '* { font-family: Arial; font-size: 16px; line-height: 34px; text-align: center; color: #222222; }';
		$html .= 'small, a, a:link:visited:active, a:hover { font-size: 13px; line-height: normal; font-style: italic; }';
		$html .= 'a, a:link:visited:active { font-weight: bold; text-decoration: none; }';
		$html .= 'a:hover { font-weight: bold; text-decoration: underline; color: #555555; }';
		$html .= '</style>';
		$html .= '</head>';
		$html .= '<body onload="' . $submit_script . '">';
        $html .= 'Você será redirecionado ao <strong>PagSeguro</strong> em alguns instantes.<br />';
        $html .= '<small>Se a página não carregar, <a href="#" onclick="' . $submit_script . ' return false;">clique aqui</a>.</small>';
        $html .= $form->toHtml();
        $html .= '</body></html>';

        return utf8_decode($html);
        
    }
    
	/**
	 * getCheckoutFormFields
     * 
     * Gera os campos para o formulário de redirecionamento ao Pagseguro
	 *
	 * @return array
	 *
	 * @uses $this->getOrder()
	 */
    public function getCheckoutFormFields()
    {
        $order = $this->getOrder();
        
        // Utiliza endereço de cobrança caso produto seja virtual/para download
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        
        // Resgata CEP
        $cep = preg_replace('@[^\d]@', '', $address->getPostcode());
        
        // Dados de endereço
        if ($this->getConfigData('custom_address_model', $order->getStoreId())) {
            $endereco = $address->getStreet(1);
            $numero = $address->getStreet(2);
            $complemento = $address->getStreet(3);
            $bairro = $address->getStreet(4);
        } else {
            list($endereco, $numero, $complemento) = $this->trataEndereco($address->getStreet(1));
            $bairro = $address->getStreet(2);
        }
        
        // Formata o telefone
        list($ddd, $telefone) = $this->trataTelefone($address->getTelephone());
        
        // Monta os dados para o formulário
        $sArr = array(
                //'encoding'          => 'utf-8',
                'email_cobranca'    => $this->getConfigData('email_cobranca', $order->getStoreId()),
                'Tipo'              => "CP",
                'Moeda'             => "BRL",
                'ref_transacao'     => $order->getRealOrderId(),
                'cliente_nome'      => $address->getFirstname() . ' ' . $address->getLastname(),
                'cliente_cep'       => $cep,
                'cliente_end'       => $endereco,
                'cliente_num'       => $numero,
                'cliente_compl'     => $complemento,
                'cliente_bairro'    => $bairro,
                'cliente_cidade'    => $address->getCity(),
                'cliente_uf'        => $address->getRegionCode(),
                'cliente_pais'      => $address->getCountry(),
                'cliente_ddd'       => $ddd,
                'cliente_tel'       => $telefone,
                'cliente_email'     => $order->getCustomerEmail(),
                );
        
        
        $i = 1;
        $items = $order->getAllVisibleItems();
        
		$shipping_amount = $order->getBaseShippingAmount();
        $tax_amount = $order->getBaseTaxAmount();
        $discount_amount = $order->getBaseDiscountAmount();
        
        $priceGrouping = $this->getConfigData('price_grouping', $order->getStoreId());
        $shippingPrice = $this->getConfigData('shipping_price', $order->getStoreId());
        
        if ($priceGrouping) {
            
            $order_total = $order->getBaseSubtotal() + $tax_amount + $discount_amount;
            if ($shippingPrice == 'grouped') {
                $order_total += $shipping_amount;
            }
            $item_descr = $order->getStoreName(2) . " - Pedido " . $order->getRealOrderId();
            $item_price = $this->formatNumber($order_total);
            $sArr = array_merge($sArr, array(
                'item_descr_'.$i   => substr($item_descr, 0, 100),
                'item_id_'.$i      => $order->getRealOrderId(),
                'item_quant_'.$i   => 1,
                'item_valor_'.$i   => $item_price,
            ));
            $i++;
                
        } else {
            
            if ($items) {
                foreach ($items as $item) {
                    $item_price = 0;
                    $item_qty = $item->getQtyOrdered() * 1;
                    if ($children = $item->getChildrenItems()) {
                        foreach ($children as $child) {
                            $item_price += $child->getBasePrice() * $child->getQtyOrdered() / $item_qty;
                        }
                        $item_price = $this->formatNumber($item_price);
                    }
                    if (!$item_price) {
        				$item_price = $this->formatNumber($item->getBasePrice());
                    }
                    $sArr = array_merge($sArr, array(
                        'item_descr_'.$i   => substr($item->getName(), 0, 100),
                        'item_id_'.$i      => substr($item->getSku(), 0, 100),
                        'item_quant_'.$i   => $item_qty,
                        'item_valor_'.$i   => $item_price,
                    ));
                    $i++;
                }
            }
            
            if ($tax_amount > 0) {
                $tax_amount = $this->formatNumber($tax_amount);
                $sArr = array_merge($sArr, array(
                    'item_descr_'.$i   => "Taxa",
                    'item_id_'.$i      => "taxa",
                    'item_quant_'.$i   => 1,
                    'item_valor_'.$i   => $tax_amount,
                ));
                $i++;
            }
                
            if ($discount_amount != 0) {
                $discount_amount = $this->formatNumber($discount_amount);
                if (preg_match("/^1\.[23]/i", Mage::getVersion())) {
                    $discount_amount = -$discount_amount;
                }
                $sArr = array_merge($sArr, array(
                    'extras'   => $discount_amount,
                ));
            }
                
        }
        
        if ($shipping_amount > 0) {
            $shipping_amount = $this->formatNumber($shipping_amount);
            switch ($shippingPrice) {
                case 'grouped':
                    if ($priceGrouping) {
                        break;
                    }
                case 'product':
                    // passa o valor do frete como um produto
                    $sArr = array_merge($sArr, array(
                        'item_descr_'.$i   => substr($order->getShippingDescription(), 0, 100),
                        'item_id_'.$i      => "frete",
                        'item_quant_'.$i   => 1,
                        'item_valor_'.$i   => $shipping_amount,
                    ));
                    $i++;
                    break;
                    
                case 'separated':
                default:
                    // passa o valor do frete separadamente
                    $sArr = array_merge($sArr, array('item_frete_1' => $shipping_amount));
                    
            }
        }
        
        $rArr = array();
        foreach ($sArr as $k => $v) {
            // troca caractere '&' por 'e'
            $value =  str_replace("&", "e", $v);
            $rArr[$k] =  $value;
        }
        
        return $rArr;
    }

	/**
	 * _confirma
	 *
	 * Faz a parte Server-Side, verificando os dados junto ao PagSeguro
	 *
	 * @param array $post Dados vindos no POST do PagSeguro
	 *
	 * @return boolean
	 */
	protected function _confirma($post) 
	{
        $resp = '';
		$confirma = false;
        
        $post['encoding'] = 'utf-8';
        $post['Comando'] = 'validar';
		$post['Token']   = $this->getConfigData('token', $this->getOrder()->getStoreId());
        
		if (!empty($post)) {
            
            $client = new Zend_Http_Client($this->getPagSeguroNPIUrl());
            
            if ($this->getConfigData('use_curl', $this->getOrder()->getStoreId())) {
                $adapter = new Zend_Http_Client_Adapter_Curl();
                $client->setAdapter($adapter);
                $adapter->setConfig(array(
                    'timeout' => 30,
                    'curloptions' => array(
                        CURLOPT_SSL_VERIFYPEER => false
                    )
                ));
            }
            
            try {
                   
                $client->setMethod(Zend_Http_Client::POST);
                $client->setParameterPost($post);
                
                $content = $client->request();
                $resp = $content->getBody();
            
            } catch (Exception $e) {
                $this->log("ERRO: " . $e->getMessage());
            }
            
            $confirma = (strcmp($resp, 'VERIFICADO') == 0);
            
		}
        $this->log("Resposta de confirmacao: $resp");
		return $confirma;
	}

	/**
	 * retornoPagSeguro
	 *
	 * Verifica e autentica os dados recebidos e, em caso de sucesso,
     * chama a funcão de processamento do pedido
	 *
	 * @param array $post      Array contendo os posts do PagSeguro
     * 
	 * @return bool
	 *
	 * @uses $this->_confirma()
	 * @uses $this->processPagSeguroNPI()
	 */
	function retornoPagSeguro($post)
	{
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($post['Referencia']);
        
		$confirma = $this->_confirma($post);

		if ($confirma) {
		    $this->log("Confirmacao efetuada");
			$itens = array (
					'VendedorEmail', 'TransacaoID', 'Referencia', 'TipoFrete',
					'ValorFrete', 'Anotacao', 'DataTransacao', 'TipoPagamento',
					'StatusTransacao', 'CliNome', 'CliEmail', 'CliEndereco',
					'CliNumero', 'CliComplemento', 'CliBairro', 'CliCidade',
					'CliEstado', 'CliCEP', 'CliTelefone', 'NumItens', 'Extras',
					);
                    
			foreach ($itens as $item) {
				if (!isset($post[$item])) {
					$post[$item] = '';
				}
				if (in_array($item, array('ValorFrete', 'Extras'))) {
					$post[$item] = str_replace(',', '.', $post[$item]);
				}
			}
            
			$total = 0;
			for ($i = 1; $i <= $post['NumItens']; $i++) {
				$total += $this->convertNumber($post["ProdValor_{$i}"]) * $post["ProdQuantidade_{$i}"];
			}
            
			$total += $this->convertNumber($post['ValorFrete']);
            
            if (preg_match("/^-/i", $post['Extras'])) {
			    $total -= $this->convertNumber($post['Extras']);
            } else {
                $total += $this->convertNumber($post['Extras']);
			}
            
            $this->processPagSeguroNPI($post['StatusTransacao'], $post["TransacaoID"], $post["TipoPagamento"], $total);
		} else {
		    $this->log("Confirmacao nao efetuada");
		}
        return $confirma;
	}

	/**
	 * processPagSeguroNPI
     * 
     * Processa informações recebidas e atualiza o pedido
	 *
	 * @param string $status Situação do pagamento
	 * @param string $transacaoID ID da transação no PagSeguro
	 * @param string $tipoPagamento Tipo de pagamento utilizado
	 * @param float $valorTotal Valor do pagamento
     * 
     * @uses $this->getOrder()
	 */
    public function processPagSeguroNPI($status, $transacaoID, $tipoPagamento, $valorTotal)
    {
        $order = $this->getOrder();
        
        $this->log("Pedido #" . $order->getRealOrderId() . ": $status");
        
        if ($order->getId()) {
	    
	    if ($order->getPayment()->getMethod() == $this->getCode()) {
        
		$valorPedido = (float) $order->getBase_grand_total();
		if (function_exists('bccomp')) {
		    // Compara números com ponto flutuante, com 2 casas decimais e retorna 0 caso sejam iguais
		    $valoresCoincidentes = bccomp($valorPedido, $valorTotal, 2);
		} else {
		    $valoresCoincidentes = (number_format($valorPedido, 2, '.', '') == number_format($valorTotal, 2, '.', '')) ? 0 : 1;
		}
		
		if ($valoresCoincidentes == 0) {
		    
		    // Atualiza informações da transação
		    $order->getPayment()->setPagseguroTransactionId(utf8_encode($transacaoID));
		    $order->getPayment()->setPagseguroPaymentMethod(utf8_encode($tipoPagamento));
		    $order->getPayment()->save();
		    
		    $changeTo = "";
			    
		    // Verificando o Status passado pelo PagSeguro
		    if (in_array(strtolower(trim($status)), array('completo', 'aprovado'))) {
			if ($order->canUnhold()) {
			    $order->unhold();
			}
			if ($order->canInvoice()) {
			    $changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
			    
			    $invoice = $order->prepareInvoice();
			    $invoice->register()->pay();
			    $invoice_msg = utf8_encode(sprintf('Pagamento confirmado (%s). Transa&ccedil;&atilde;o PagSeguro: %s', $tipoPagamento, $transacaoID));
			    $invoice->addComment($invoice_msg, true);
			    $invoice->sendEmail(true, $invoice_msg);
			    $invoice->setEmailSent(true);
			    
			    Mage::getModel('core/resource_transaction')
			       ->addObject($invoice)
			       ->addObject($invoice->getOrder())
			       ->save();
			    $comment = utf8_encode(sprintf('Fatura #%s criada.', $invoice->getIncrementId(), $tipoPagamento));
			    $order->setState($changeTo, true, $comment, $notified = true);
			    $this->log("Fatura criada");
			} else {
			    // Lógica para quando a fatura não puder ser criada
			    $this->log("Fatura nao criada");
			}
		    } else {
			// Não está completa, vamos processar...
			
			if (in_array(strtolower(trim($status)), array('cancelado', 'devolvido'))) {
			    
			    if (strtolower(trim($status)) == 'devolvido') {
				$order_msg = "Pagamento devolvido.";
				$comment_add = true;
				foreach ($order->getAllStatusHistory() as $status) {
				    if (strpos($status->getComment(), $order_msg) !== false) {
					$comment_add = false;
					break;
				    }
				}
				if ($comment_add) {
				    if (method_exists($order, "addStatusHistoryComment")) {
					$order->addStatusHistoryComment($order_msg, false)->setIsCustomerNotified(true);
				    } elseif (method_exists($order, "addStatusToHistory")) {
					$order->addStatusToHistory($order->getStatus(), $order_msg, true);
				    }
				}
			    } else {
				$order_msg = "Pagamento cancelado.";
			    }
			    
			    // Pedido cancelado
			    if ($order->canUnhold()) {
				$order->unhold();
			    }
			    if ($order->canCancel()) {
				$changeTo = Mage_Sales_Model_Order::STATE_CANCELED;
				$order->getPayment()->setMessage($order_msg);
				$order->cancel();
			    }
			    
			} else {
			    
			    // Em espera/análise/aguardando pagamento(boleto)
			    if ($order->canHold()) {
				$changeTo = Mage_Sales_Model_Order::STATE_HOLDED;
				$comment = utf8_encode($status . ' - ' . $tipoPagamento);
				$order->setHoldBeforeState($order->getState());
				$order->setHoldBeforeStatus($order->getStatus());
				$order->setState($changeTo, true, $comment, $notified = false);
			    }
			    
			}
			
		    }
		    
		    if ($changeTo != "") {
			$this->log("Status do pedido atualizado: " . $order->getState());
		    }
		    $order->save();
		    
		} else {
		    $this->log("ERRO: O valor recebido nao coincide com o armazenado (Valor do pedido: $valorPedido / Valor recebido: $valorTotal)");
		}
	    } else {
		$this->log("ERRO: Pedido nao efetuado com este metodo de pagamento.");
	    }
	} else {
            $this->log("ERRO: Pedido nao encontrado.");
        }
    }
    
	/**
	 * trataTelefone
	 *
	 * @param string $tel   Telefone a ser tratado
	 *
	 * @return array
	 */
    function trataTelefone($tel)
    {
        $numeros = preg_replace('/\D/','', $tel);
        $tel     = substr($numeros, sizeof($numeros)-9);
        $ddd     = substr($numeros, sizeof($numeros)-11,2);
        return array($ddd, $tel);
    }
    
	/**
	 * dados
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
     * Retorna dados auxiliares de acordo com o argumento passado,
     * que podem ser:
     * - 'complementos'
     * - 'brasilias'
     * - 'naobrasilias'
     * - 'sems'
     * - 'numeros'
     * - 'semnumeros'
	 *
	 * @param string $v   Código para escolha do retorno
	 *
	 * @return array
	 */
    function dados($v) {
        $dados = array();
        $dados['complementos'] = array("casa", "ap", "apto", "apart", "frente", "fundos", "sala", "cj");
        $dados['brasilias'] = array("bloco", "setor", "quadra", "lote");
        $dados['naobrasilias'] = array("av", "avenida", "rua", "alameda", "al.", "travessa", "trv", "praça", "praca");
        $dados['sems'] = array("sem ", "s.", "s/", "s. ", "s/ ");
        $dados['numeros'] = array('n.º', 'nº', "numero", "num", "número", "núm", "n");
        $dados['semnumeros'] = array();
        foreach ($dados['numeros'] as $n)
          foreach ($dados['sems'] as $s)
            $dados['semnumeros'][] = "$s$n";
        return $dados[$v];
    }
    
	/**
	 * ehBrasilia
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser analisado
	 *
	 * @return bool
	 */
    function ehBrasilia($end) {
        $brasilias = $this->dados('brasilias');
        $naobrasilias = $this->dados('naobrasilias');
        $brasilia = false;
        foreach ($brasilias as $b)
          if (strpos(strtolower($end),$b) != false)
            $brasilia = true;
        if ($brasilia)
          foreach ($naobrasilias as $b)
            if (strpos(strtolower($end),$b) != false)
              $brasilia = false;
        return $brasilia;
    }
    
	/**
	 * buscaReversa
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
     * Encontra o primeiro caractere númerico dentre os últimos 10 da string informada
     * e retorna a string separada na posição localizada
	 *
	 * @param string $texto   Texto a ser procurado
	 *
	 * @return array
	 */
    function buscaReversa($texto) {
        $encontrar = substr($texto, -10);
        for ($i = 0; $i < 10; $i++) {
          if (is_numeric(substr($encontrar, $i, 1))) {
            return array(
                substr($texto, 0, -10+$i),
                substr($texto, -10+$i)
                );
          }
        }
    }
    
	/**
	 * tiraNumeroFinal
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $endereco   Endereço a ser tratado
	 *
	 * @return string
	 */
    function tiraNumeroFinal($endereco) {
        $numeros = $this->dados('numeros');
        foreach ($numeros as $n)
          foreach (array(" $n"," $n ") as $N)
          if (substr($endereco, -strlen($N)) == $N)
            return substr($endereco, 0, -strlen($N));
        return $endereco;
    }
    
	/**
	 * separaNumeroComplemento
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $n   Número a ser tratado
	 *
	 * @return array
	 */
    function separaNumeroComplemento($n) {
        $semnumeros = $this->dados('semnumeros');
        $n = $this->endtrim($n);
        foreach ($semnumeros as $sn) {
          if ($n == $sn)return array($n, '');
          if (substr($n, 0, strlen($sn)) == $sn)
            return array(substr($n, 0, strlen($sn)), substr($n, strlen($sn)));
        }
        $q = preg_split('/\D/', $n);
        $pos = strlen($q[0]);
        return array(substr($n, 0, $pos), substr($n,$pos));
    }
    
	/**
	 * brasiliaSeparaComplemento
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser tratado
	 *
	 * @return array
	 */
    function brasiliaSeparaComplemento($end) {
        $complementos = $this->dados('complementos');
        foreach ($complementos as $c)
          if ($pos = strpos(strtolower($end), $c))
            return array(substr($end, 0 ,$pos), substr($end, $pos));
        return array($end, '');
    }
    
	/**
	 * trataEndereco
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser tratado
	 *
	 * @return array
	 */
    function trataEndereco($end) {
        $numeros = $this->dados('numeros');
        $complementos = $this->dados('complementos');
        if ($this->ehBrasilia($end)) {
          $numero = 's/nº';
          list($endereco, $complemento) = $this->brasiliaSeparaComplemento($end);
        } else {
          $endereco = $end;
          $numero = 's/nº';
          $complemento = '';
          $quebrado = preg_split('/[-,]/', $end);
          if (sizeof($quebrado) == 3){
            list($endereco, $numero, $complemento) = $quebrado;
          } elseif (sizeof($quebrado) == 2) {
            list($endereco, $numero) = $quebrado;
          } else {
            list($endereco, $numero) = $this->buscaReversa($end);
          }
          $endereco = $this->tiraNumeroFinal($endereco);
          if ($complemento == '')
            list($numerob,$complemento) = $this->separaNumeroComplemento($numero);
        }
        return array($this->endtrim($endereco), $this->endtrim($numero), $this->endtrim($complemento));
    }

	/**
     * convertNumber
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
	 * Converte número para padrão numérico
	 *
	 * @param string|int|double $number Numero que deseja converter
	 * 
	 * @return double
	 */
	function convertNumber ($number)
	{
		$number = preg_replace('/\D/', '', $number) / 100;
		return (double) str_replace(',', '.', $number);
	}

	/**
     * formatNumber
     * 
	 * Formata número para envio ao PagSeguro
	 *
	 * @param int|double $number Numero que deseja converter
	 * 
	 * @return int
	 */
	function formatNumber ($number)
	{
		return sprintf('%.2f', (double) $number) * 100;
	}

	/**
     * endtrim
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
	 * Remove caracteres e espaços desnecessários
	 *
	 * @param string|int|double $e Texto que deseja alterar
	 * 
	 * @return string
	 */
    function endtrim($e){
        return preg_replace('/^\W+|\W+$/', '', $e);
    }
}
