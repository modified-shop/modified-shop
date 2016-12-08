<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class Shopgate_Helper_Redirect_Type_Js implements Shopgate_Helper_Redirect_Type_TypeInterface
{
	/** @var Shopgate_Helper_Redirect_JsScriptBuilderInterface */
	private $jsBuilder;

	/**
	 * @param Shopgate_Helper_Redirect_JsScriptBuilderInterface $jsBuilder
	 */
	public function __construct(Shopgate_Helper_Redirect_JsScriptBuilderInterface $jsBuilder)
	{
		$this->jsBuilder = $jsBuilder;
	}

	/**
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function getBuilder()
	{
		return $this->jsBuilder;
	}

	/**
	 * @param string $manufacturer
	 *
	 * @return string
	 */
	public function loadBrand($manufacturer)
	{
		return $this->jsBuilder->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND,
			array('brand_name' => $manufacturer)
		);
	}

	/**
	 * @param int|string $categoryId
	 *
	 * @return string
	 */
	public function loadCategory($categoryId)
	{
		return $this->jsBuilder->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY,
			array('category_uid' => $categoryId)
		);
	}

	/**
	 * @param string $cmsPage
	 *
	 * @return string
	 */
	public function loadCms($cmsPage)
	{
		return $this->jsBuilder->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS,
			array('page_uid' => $cmsPage)
		);
	}

	/**
	 * @return string
	 */
	public function loadDefault()
	{
		return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
	}

	/**
	 * @return string
	 */
	public function loadHome()
	{
		return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME);
	}

	/**
	 * @param int|string $productId
	 *
	 * @return string
	 */
	public function loadProduct($productId)
	{
		return $this->jsBuilder->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT,
			array('product_uid' => $productId)
		);
	}

	/**
	 * @param string $query
	 *
	 * @return string
	 */
	public function loadSearch($query)
	{
		return $this->jsBuilder->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH,
			array('search_query' => $query)
		);
	}
}
