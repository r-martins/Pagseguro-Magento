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
 * PagSeguro CMS Return Page Source
 *
 */

class BrunoAssarisse_PagSeguro_Model_Source_ReturnPage
{
	public function toOptionArray ()
	{
		$collection = Mage::getModel('cms/page')->getCollection();
		$pages = array();
		foreach ($collection as $page) {
			$pages[$page->getIdentifier()] = $page->getTitle();
		}
		return $pages;
	}

}