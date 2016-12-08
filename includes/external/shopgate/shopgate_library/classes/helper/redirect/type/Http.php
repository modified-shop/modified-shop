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
class Shopgate_Helper_Redirect_Type_Http implements Shopgate_Helper_Redirect_Type_TypeInterface
{

	/** @var Shopgate_Helper_Redirect_RedirectorInterface */
	private $redirector;

	/**
	 * @param Shopgate_Helper_Redirect_RedirectorInterface $redirector
	 */
	public function __construct(Shopgate_Helper_Redirect_RedirectorInterface $redirector)
	{
		$this->redirector = $redirector;
	}

	/**
	 * @return Shopgate_Helper_Redirect_RedirectorInterface
	 */
	public function getBuilder()
	{
		return $this->redirector;
	}

	/**
	 * @param string $manufacturer
	 *
	 * @return void
	 */
	public function loadBrand($manufacturer)
	{
		$this->redirector->redirectBrand($manufacturer);
	}

	/**
	 * @param int|string $categoryId
	 *
	 * @return void
	 */
	public function loadCategory($categoryId)
	{
		$this->redirector->redirectCategory($categoryId);
	}

	/**
	 * @param string $cmsPage
	 *
	 * @return void
	 */
	public function loadCms($cmsPage)
	{
		$this->redirector->redirectCms($cmsPage);
	}

	/**
	 * @return void
	 */
	public function loadDefault()
	{
		$this->redirector->redirectDefault();
	}

	/**
	 * @return void
	 */
	public function loadHome()
	{
		$this->redirector->redirectHome();
	}

	/**
	 * @param int|string $productId
	 *
	 * @return void
	 */
	public function loadProduct($productId)
	{
		$this->redirector->redirectProduct($productId);
	}

	/**
	 * @param string $query
	 *
	 * @return void
	 */
	public function loadSearch($query)
	{
		$this->redirector->redirectSearch($query);
	}
}
