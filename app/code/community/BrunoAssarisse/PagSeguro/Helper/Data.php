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
 * PagSeguro Payment Data Helper
 *
 */

class BrunoAssarisse_PagSeguro_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const PARCEL_MAX_VALUE = 5;

    /**
     * Escapa entidades HTML.
     * Função criada para compatibilidade com versões mais antigas do Magento.
     *
     * @param   mixed $data
     * @param   array $allowedTags
     * @return  string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        $core_helper = Mage::helper('core');
        if (method_exists($core_helper, "escapeHtml")) {
            return $core_helper->escapeHtml($data, $allowedTags);
        } elseif (method_exists($core_helper, "htmlEscape")) {
            return $core_helper->htmlEscape($data, $allowedTags);
        } else {
            return $data;
        }
        
    }
    
    /**
     * Calcula preço da parcela desejada, de acordo com o valor informado e até
     * quantas parcelas sem juros são disponibilizadas. Retorna um array com o
     * valor total, o valor da parcela e uma mensagem extra.
     */
    public function calculateRate($valor_original, $parcelas_sem_juros = 0, $intervalos = 1, $recalcula = false, $juros = 0.0199) {
    
        $parcelas = $intervalos;
        if ($parcelas_sem_juros > 1 and $parcelas <= $parcelas_sem_juros) {
            $parcelas = $parcelas_sem_juros;
        }
    
        if ($juros > 1) {
            $juros /= 100;
        }
    
        $msg_extra = "";
    
        $valor_total = $valor_original;
        if ($intervalos == 1 and $parcelas_sem_juros < 1) {
            $valor_parcela = $valor_original;
            $msg_extra = "Sem juros";
        } else {
            if ($parcelas <= $parcelas_sem_juros or $parcelas_sem_juros < 1) {
                if ($parcelas_sem_juros > 1) {
                    $msg_extra = "Sem juros";
                }
            } else {
                if ($juros == 0) {
                    $valor_parcela = $valor_original / $intervalos;
                } else {
                    if ($recalcula) {
                        $valor_parcela = ($valor_original * $juros) / (1 - pow(1 / (1 + $juros), $parcelas_sem_juros));
                        $valor_total = $valor_parcela * $parcelas_sem_juros;
                    }
                    $parcelas -= $parcelas_sem_juros;
                }
            }
            if ($juros != 0 and ($recalcula or $intervalos > $parcelas_sem_juros)) {
                $valor_parcela = ($valor_total * $juros) / (1 - pow(1 / (1 + $juros), $parcelas));
                $valor_total = $valor_parcela * $parcelas;
            }
            $valor_parcela = $valor_total / $intervalos;
        }
    
        return array($valor_total, $valor_parcela, $msg_extra);
    }
    
    
    /**
     * Calcula preço à vista com desconto, de acordo com o valor informado e até
     * quantas parcelas sem juros são disponibilizadas. Retorna um array com o
     * valor e a porcentagem de desconto.
     */
    public function calculateUpfrontPrice($valor_original, $parcelas_sem_juros, $juros = 0.0199) {
    
        if (preg_match("/^[-+]?[0-9]{1,3}(\.[0-9]{3})*(,[0-9]*)?$/", $valor_original)) {
            $valor_original = str_replace(".", "", $valor_original);
            $valor_original = str_replace(",", ".", $valor_original);
        }
    
        if ($juros > 1) {
            $juros /= 100;
        }
    
        $valor_a_vista = $valor_original;
        if ($parcelas_sem_juros >= 2 and $juros != 0) {
    
            $valor_parcela = $valor_a_vista / $parcelas_sem_juros;
            $valor_total = ($valor_parcela * (1 - pow(1 / (1 + $juros), $parcelas_sem_juros))) / $juros;
    
            $valor_a_vista = $valor_total;
        }
        
        $desconto = ceil((1 - $valor_a_vista / $valor_original) * 100);
        
        $valor_a_vista = number_format($valor_a_vista, 2, ",", "");
    
        return array($valor_a_vista, $desconto);
    }
    
    /**
     * Calcula planos de parcelamento de acordo com o valor e o número de parcelas
     * sem juros a serem exibidas.
     */
    public function calculateInstallments($valor_total_orig, $parcelas_sem_juros = 0, $recalcula = false, $juros = 0.0199, $parcelas_max = 18) {
    
        $installments = array();
    
        if (preg_match("/^[-+]?[0-9]{1,3}(\.[0-9]{3})*(,[0-9]*)?$/", $valor_total_orig)) {
            $valor_total_orig = str_replace(".", "", $valor_total_orig);
            $valor_total_orig = str_replace(",", ".", $valor_total_orig);
        }
    
        if ($parcelas_max < 1) {
            $parcelas_max = 1;
        }
    
        for ($parcels = 1; $parcels <= $parcelas_max; $parcels++) {
    
            list($valor_total, $valor_parcela, $msg_extra) = $this->calculateRate($valor_total_orig, $parcelas_sem_juros, $parcels, $recalcula, $juros);
            
            if ($parcels > 1 and $valor_parcela < self::PARCEL_MAX_VALUE) {
                break;
            }
    
            $valor_parcela = number_format($valor_parcela, 2, ",", "");
            $valor_total = number_format($valor_total, 2, ",", "");
            
            $installments[] = array(
                'valor_parcela' => $valor_parcela,
                'valor_total' => $valor_total,
                'msg_extra' => $msg_extra,
            );
        }
    
        return $installments;
    }
    
    /**
     * Retorna o menor valor de parcela sem juros possível,
     * de acordo o número máximo de parcelas sem juros.
     */
    public function getMinParcelWithoutRate($valor_total_orig, $parcelas_sem_juros = 0, $recalcula = false, $juros = 0.0199) {
        
        $minParcelValue = 0;
        $parcels = 1;
        
        if ($valor_total_orig > self::PARCEL_MAX_VALUE) {
            
            for (; $parcels <= $parcelas_sem_juros; $parcels++) {
                list($valor_total, $valor_parcela) = $this->calculateRate($valor_total_orig, $parcelas_sem_juros, $parcels, $recalcula, $juros);
                if ($parcels > 1 and $valor_parcela < self::PARCEL_MAX_VALUE) {
                    break;
                } else {
                    $minParcelValue = $valor_parcela;
                }
            }
            $parcels--;
            
        }
        
        $minParcelValue = number_format($minParcelValue, 2, ",", "");
        
        return array($minParcelValue, $parcels);
    }
    
    public function ceiling($value, $precision = 0) {
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
    }
    
}