<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbia.org/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category Kumbia
 * @package Flickr
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Functional Flickr Search using RSS
 * You need to have enabled PHP DOM Extension
 *
 * @category Kumbia
 * @package Flickr
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

class Flickr  {

	/**
	 * Direccion del RSS donde se consulta
	 *
	 * @var string
	 */
	public $rss;

	/**
	 * Clave para realizar la busqueda
	 *
	 * @var string
	 */
	public $key;

	/**
	 * Constructor de la clase Flickr
	 *
	 */
	public function __construct($key=''){
		$this->key = $key;
	}

	/**
	 * Obtener las fotos de Flickr
	 *
	 * @param string $tags
	 * @param integer $num
	 * @return array
	 */
	public function photos($tags, $num=1){
  	  	$thumbs = array();
	    $docXML = new DOMDocument();
		$this->rss = file_get_contents("http://www.flickr.com/services/feeds/photos_public.gne?tags=$tags&format=rss_200");
		$docXML->loadXML($this->rss);
		$i = 0;
		foreach ($docXML->getElementsByTagNameNS('http://search.yahoo.com/mrss/', '*') as $element) {
			if($element->localName=="thumbnail"){
				$thumbs[$i]['thumb'] = $element->getAttribute("url");
				$i++;
			}
			if($element->localName=="content"){
				$thumbs[$i]['content'] = $element->getAttribute("url");
			}
		}
		return $thumbs;
	}

}
