<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Manejo de XML
 * 
 * @category   Kumbia
 * @package    XML 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class SimpleXMLResponse
{
    /**
     * Objeto XMLWrite
     *
     * @var Objeto
     */
    private $_xml = NULL;
    /**
     * Constructor
     *
     */
    public function __construct ()
    {
        if ($this->_xml == NULL) {
            $this->_xml = new XMLWriter();
        }
        $this->_xml->openMemory();
        $this->_xml->startDocument('1.0', 'UTF-8');
        $this->_xml->openURI('php://output');
        $this->_xml->setIndent(true);
        $this->_xml->startElement('response');
    }
    /**
     * Agrega un nodo a la salida XML
     * 
     * <code>
     * $xml->addNode(array('value' => 1, 'text' => 'Prueba', 'selected' => '0'));
     * $xml->addNode('value: 1', 'text: Prueba', 'selected: 0');
     * </code>
     * 
     * @param array $arr
     */
    public function add_node ($arr)
    {
        $this->_xml->startElement('row');
        if (! is_array($arr)) {
            $arr = Util::getParams(func_get_args());
        }
        foreach ($arr as $key => $value) {
            $this->_xml->writeAttribute($key, $value);
        }
        $this->_xml->endElement();
    }
    public function add_data ($content)
    {
        $this->_xml->startElement('data');
        $this->_xml->writeCData($content);
        $this->_xml->endElement();
    }
    /**
     * Imprime la salida XML
     *
     */
    public function out_response ()
    {
        header('Content-Type: text/xml');
        header('Pragma: no-cache');
        header('Expires: 0');
        $this->_xml->endElement(); // end <response>
        print $this->_xml->outputMemory(true);
    }
}