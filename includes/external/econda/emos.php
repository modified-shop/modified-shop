<?php
/*
Copyright (c) 2004, 2005 ECONDA GmbH Karlsruhe
All rights reserved.

ECONDA GmbH
Haid-und-Neu-Str. 7
76131 Karlsruhe
Tel. +49 (721) 6635726
Fax +49 (721) 66499070
info@econda.de
www.econda.de

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
   3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


*/

/** PHP Helper Class to construct a ECONDA Monitor statement for the later
* inclusion in a HTML/PHP Page.
*/
class EMOS{

	/**
	* the EMOS statement consists of 3 parts
	* 1.   the inScript :<code><script type="text/javascript" src="emos2.js"></script>
	* 2,3. a part before and after this inScript (preScript/postScript)</code>
	*/
	var $preScript="";

	/**
	* Here we store the call to the js bib
	*/
	var $inScript="";

	/**
	* if we must put something behind the call to the js bin we put it here
	*/
	var $postScript="";

	/** path to the empos2.js script-file */
	var $pathToFile= "econda";

	/** Name of the script-file */
	var $scriptFileName="emos32_xtc.js";

	/** if we use pretty print, we will set the lineseparator or tab here */
	var $br="";
	var $tab="";


	/** Constructor
	* Sets the path to the emos2.js js-bib and prepares the later calls
	*
	* @param $pathToFile The path to the js-bib (/opt/myjs)
	* @param $scriptFileName If we want to have annother Filename than
	*          emos2.js you can set it here
	*/
	function EMOS($pathToFile="econda/", $scriptFileName="emos32_xtc.js"){
		$this->pathToFile = DIR_WS_CATALOG.DIR_WS_EXTERNAL.$pathToFile;
		$this->scriptFileName = $scriptFileName;
		$this->prepareInScript();

	}

	/** switch on pretty printing of generated code. If not called, the output
	* will be in one line of html.
	*/
	function prettyPrint(){
		$this->br.="\n";
		$this->tab.="\t";
	}

	/** Concatenates the current command and the $inScript */
	function appendInScript($stringToAppend){
		$this->inScript.= $stringToAppend;
	}

	/** Concatenates the current command and the $proScript */
	function appendPreScript($stringToAppend){
		$this->preScript.= $stringToAppend;
	}

	/** Concatenates the current command and the $postScript */
	function appendPostScript($stringToAppend){
		$this->postScript.= $stringToAppend;
	}

	/** sets up the inScript Part with Initialisation Params */
	function prepareInScript(){
		$this->inScript .= "<script type=\"text/javascript\" "
		."src=\"".$this->pathToFile.$this->scriptFileName."\">"
		."</script>".$this->br;
	}

	/** returns the whole statement */
	function toString(){
		return $this->preScript.$this->inScript.$this->postScript;
	}

	/** constructs a emos anchor tag */
	function getAnchorTag($title="",$rel="",$rev=""){
		$anchor = "<a name=\"emos_name\" "
		."title=\"$title\" "
		."rel=\"$rel\" "
		."rev=\"$rev\"></a>$this->br";
		return $anchor;
	}

	/** adds a anchor tag for content tracking
	* <a name="emos_name" title="content" rel="$content" rev=""></a>
	*/
	function addContent($content){
		$this->appendPreScript($this->getAnchorTag("content",$content));
	}

	/** adds a anchor tag for orderprocess tracking
	* <a name="emos_name" title="orderProcess" rel="$processStep" rev=""></a>
	*/
	function addOrderProcess($processStep){
		$this->appendPreScript($this->getAnchorTag("orderProcess",$processStep));
	}

	/** adds a anchor tag for search tracking
	* <a name="emos_name" title="search" rel="$queryString" rev="$numberOfHits"></a>
	*/
	function addSearch($queryString,$numberOfHits){
		$this->appendPreScript($this->getAnchorTag("search",$queryString,$numberOfHits));
	}

	/** adds a anchor tag for registration tracking
	* The userid gets a md5() to fullfilll german datenschutzgesetz
	* <a name="emos_name" title="register" rel="$userID" rev="$result"></a>
	*/
	function addRegister($userID,$result){
		$this->appendPreScript($this->getAnchorTag("register",md5($userID),$result));
	}


	/** adds a anchor tag for login tracking
	*The userid gets a md5() to fullfilll german datenschutzgesetz
	* <a name="emos_name" title="login" rel="$userID" rev="$result"></a>
	*/
	function addLogin($userID,$result){
		$this->appendPreScript($this->getAnchorTag("login",md5($userID),$result));
	}

	/** adds a anchor tag for contact tracking
	* <a name="emos_name" title="scontact" rel="$contactType" rev=""></a>
	*/
	function addContact($contactType){
		$this->appendPreScript($this->getAnchorTag("scontact",$contactType));
	}

	/** adds a anchor tag for download tracking
	* <a name="emos_name" title="download" rel="$downloadLabel" rev=""></a>
	*/
	function addDownload($downloadLabel){
		$this->appendPreScript($this->getAnchorTag("download",$downloadLabel));
	}

	/** constructs a emosECPageArray of given $event type
	* @param $item a instance of class EMOS_Item
	* @param $event Type of this event ("add","c_rmv","c_add")
	*/
	function getEmosECPageArray($item, $event){
		$out = "";
		$out .= "<script type=\"text/javascript\">$this->br"
		."<!--$this->br"
		."$this->tab var emosECPageArray = new Array();$this->br"
		."$this->tab emosECPageArray['event'] = '$event';$this->br"
		."$this->tab emosECPageArray['id'] = '$item->productID';$this->br"
		."$this->tab emosECPageArray['name'] = '".urlencode($item->productName)."';$this->br"
		."$this->tab emosECPageArray['preis'] = '$item->price';$this->br"
		."$this->tab emosECPageArray['group'] = '".urlencode($item->productGroup)."';$this->br"
		."$this->tab emosECPageArray['anzahl'] = '$item->quantity';$this->br"
		."$this->tab emosECPageArray['var1'] = '$item->variant1';$this->br"
		."$this->tab emosECPageArray['var2'] = '$item->variant2';$this->br"
		."$this->tab emosECPageArray['var3'] = '$item->variant3';$this->br"
		."// -->$this->br"
		."</script>$this->br";
		return $out;
	}

	/** constructs a emosBillingPageArray of given $event type */
	function addEmosBillingPageArray($billingID="",$customerNumber="",
	$total=0,
	$country="",
	$cip="",
	$city=""){
		$out = $this->getEmosBillingArray($billingID,$customerNumber,
		$total,
		$country,
		$cip,
		$city, "emosBillingPageArray");
		$this->appendPreScript( $out);
	}

	/** gets a emosBillingArray for a given ArrayName */
	function getEmosBillingArray($billingID="", $customerNumber="", $total=0,
	$country="",
	$cip="",
	$city="", $arrayName=""){

		/******************* prepare data *************************************/
		/* md5 the customer id to fullfill requirements of german datenschutzgeesetz */
		$customerNumber = md5($customerNumber);

		/* get a / separated location stzring for later drilldown */
		$ort = "";
		if($country){
			$ort.="$country/";
		}
		if($cip){
			$ort.=substr($cip,0,1)."/".substr($cip,0,2)."/";
		}
		if($city){
			$ort.="$city/";
		}
		if($cip){
			$ort.=$cip;
		}

		/******************* get output** *************************************/
		/* get the real output of this funktion */
		$out = "";
		$out .= "<script type=\"text/javascript\">$this->br"
		."<!--$this->br"
		."$this->tab var $arrayName = new Array();$this->br"
		."$this->tab $arrayName"."['0'] = '$billingID';$this->br"
		."$this->tab $arrayName"."['1'] = '$customerNumber';$this->br"
		."$this->tab $arrayName"."['2'] = '$ort';$this->br"
		."$this->tab $arrayName"."['3'] = '$total';$this->br"
		."// -->$this->br"
		."</script>$this->br";
		return $out;
	}

	/** adds a emosBasket Page Array to the preScript */
	function addEmosBasketPageArray($basket){
		$out = $this->getEmosBasketPageArray($basket, "emosBasketPageArray");
		$this->appendPreScript( $out);
	}

	/** returns a emosBasketArray of given Name */
	function getEmosBasketPageArray($basket, $arrayName){
		$out="";
		$out .= "<script type=\"text/javascript\">$this->br"
		."<!--$this->br"
		."var $arrayName = new Array();$this->br";
		$count = 0;
		foreach( $basket as $item){
			$out .= $this->br;
			$out .= "$this->tab $arrayName"."[$count]=new Array();$this->br";
			$out .= "$this->tab $arrayName"."[$count][0]='$item->productID';$this->br";
			$out .= "$this->tab $arrayName"."[$count][1]='".urlencode($item->productName)."';$this->br";
			$out .= "$this->tab $arrayName"."[$count][2]='$item->price';$this->br";
			$out .= "$this->tab $arrayName"."[$count][3]='".urlencode($item->productGroup)."';$this->br";
			$out .= "$this->tab $arrayName"."[$count][4]='$item->quantity';$this->br";
			$out .= "$this->tab $arrayName"."[$count][5]='$item->variant1';$this->br";
			$out .= "$this->tab $arrayName"."[$count][6]='$item->variant2';$this->br";
			$out .= "$this->tab $arrayName"."[$count][7]='$item->variant3';$this->br";
			$count++;
		}
		$out .= "// -->$this->br"
		."</script>$this->br";

		return $out;
	}

	/** adds a detailView to the preScript */
	function addDetailView($item){
		$this->appendPreScript( $this->getEmosECPageArray($item, "view"));
	}

	/** adds a removeFromBasket to the preScript */
	function removeFromBasket($item){
		$this->appendPreScript( $this->getEmosECPageArray($item, "c_rmv"));
	}

	/** adds a addToBasket to the preScript */
	function addToBasket($item){
		$this->appendPreScript( $this->getEmosECPageArray($item, "c_add"));
	}


}

/** global Functions */

function getEmosECEvent($item, $event){
	$out="";
	$out .= "emos_ecEvent('$event',"
	."'$item->productID',"
	."'".urlencode($item->productName)."',"
	."'$item->price',"
	."'".urlencode($item->productGroup)."',"
	."'$item->quantity',"
	."'$item->variant1'"
	."'$item->variant2'"
	."'$item->variant3');";
	return $out;
}

function getEmosViewEvent($item){
	return getEmosECEvent($item, "view");
}

function getEmosAddToBasketEvent($item){
	return getEmosECEvent($item, "c_add");
}

function getRemoveFromBasketEvent($item){
	return getEmosECEvent($item, "c_rmv");
}

function getEmosBillingEventArray($billingID="",$customerNumber="",	$total=0,
$country="",$cip="",$city=""){
	$b = new EMOS();
	return $b->getEmosBillingArray($billingID,$customerNumber,$total,
	$country,$cip,$city, "emosBillingArray");
}

function getEMOSBasketEventArray($basket){
	$b = new EMOS();
	return $b->getEmosBasketArray($basket,"emosBasketArray");
}


/** A Class to hold products as well a basket items
* If you want to track a product view, set the quantity to 1.
* For "real" basket items, the quantity should be given in your
* shopping systems basket/shopping cart.
*
* Purpose of this class:
* This class provides a common subset of features for most shopping systems
* products or basket/cart items. So all you have to do is to convert your
* products/articles/basket items/cart items to a EMOS_Items. And finally use
* the functionaltiy of the EMOS class.
* So for each shopping system we only have to do the conversion of the cart/basket
* and items and we can (hopefully) keep the rest of code.
*
* Shopping carts:
*	A shopping cart / basket is a simple Array[] of EMOS items.
*	Convert your cart to a Array of EMOS_Items and your job is nearly done.
*/
class EMOS_Item{
	/** unique Identifier of a product e.g. article number */
	var $productID="NULL";
	/** the name of a product */
	var $productName="NULL";
	/** the price of the product, it is your choice wether its gross or net */
	var $price="NULL";
	/** the product group for this product, this is a drill down dimension
	* or tree-like structure
	* so you might want to use it like this:
	* productgroup/subgroup/subgroup/product
	*/
	var $productGroup="NULL";
	/* the quantity / number of products viewed/bought etc.. */
	var $quantity="NULL";
	/** variant of the product e.g. size, color, brand ....
	* remember to keep the order of theses variants allways the same
	* decide which variant is which feature and stick to it
	*/
	var $variant1="NULL";
	var $variant2="NULL";
	var $variant3="NULL";
}


?>