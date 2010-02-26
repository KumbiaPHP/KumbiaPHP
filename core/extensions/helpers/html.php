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
 * Helper para Tags Html
 * 
 * @category   KumbiaPHP
 * @package    Helpers 
 * @copyright  Copyright (c) 2005-2010 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Html
{
    /**
     * Alternador para tabla zebra
     *
     * @var boolean
     */
    protected static $_trClassAlternate = TRUE;

    /**
     * Metatags
     *
     * @var array
     */
    protected static $_metatags = array();

    /**
     * Enlaces de head
     *
     * @var array
     */
    protected static $_headLinks = array();

    /**
     * Crea un enlace en una Aplicacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function link ($action, $text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . PUBLIC_PATH . "$action\" $attrs>$text</a>";
    }
    /**
     * Crea un enlace en una Aplicacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action
     * @param string $text texto a mostrar
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function linkAction ($action, $text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" $attrs>$text</a>";
    }
    /**
     * Permite incluir una imagen
     *
     * @param string $src
     * @params string $alt
     * @param string | array $attrs atributos adicionales
     */
    public static function img ($src, $alt=NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<img src="' . PUBLIC_PATH . "img/$src\" alt=\"$alt\" $attrs/>";
    }
    
    /**
     * Aplica estilo zebra a una tabla.
     *
     * @param string $class class css
     * @param string | array $attrs
     * @param unknown_type $start
     */
    public static function trClass ($class, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        if(self::$_trClassAlternate){
            self::$_trClassAlternate = FALSE;
            return "<tr class='$class' $attrs>";
        } else {
            self::$_trClassAlternate = TRUE;
            return "<tr $attrs>";
        }
    }
    
    /**
     * Inicia el alternador de clase para tabla zebra
     *
     */
    public static function trClassStart ()
    {
        self::$_trClassAlternate = TRUE;
    }
    
    /**
     * Crea un metatag
     *
     * @param string $content contenido del metatag
     * @param string|array $attrs atributos
     */
    public static function meta($content, $attrs = NULL)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
    
        self::$_metatags[] = array('content' => $content, 'attrs' => $attrs);
    }
    
    /**
     * Incluye los metatags
     *
     * @return string
     */
    public static function includeMetatags()
    {
        $code = '';
        foreach(self::$_metatags as $meta) {
            $code .= "<meta content=\"{$meta['content']}\" {$meta['attrs']}/>" . PHP_EOL;
        }
        return $code;
    }

    /**
     * Crea una lista a partir de un array
     *
     * @param string $content contenido del metatag
     * @param string $type por defecto ul, y si no ol
     * @param string|array $attrs atributos 
     * @return string
     */
    public static function lists($array, $type = 'ul', $attrs = NULL)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $list = "<$type $attrs>".PHP_EOL;
        foreach($array as $item){
            $list .= "<li>$item</li>".PHP_EOL;
        }
        $list .= "</$type>".PHP_EOL;
        
        return $list;
    }
    
    /**
     * Incluye los CSS
     *
     * @return string
     */
    public static function includeCss()
    {
        $code = '';
        foreach(Tag::getCss() as $css) {
            $code .= '<link href="' . PUBLIC_PATH . "css/{$css['src']}.css\" rel=\"stylesheet\" type=\"text/css\" media=\"{$css['media']}\"/>" . PHP_EOL;
        }
        return $code;
    }
    
    /**
     * Enlaza un recurso externo
     *
     * @param string $href direccion url del recurso a enlazar
     * @param string|array $attrs atributos
     */
    public static function headLink($href, $attrs = NULL)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
    
        self::$_headLinks[] = array('href' => $href, 'attrs' => $attrs);
    }
    
    /**
     * Enlaza una accion
     *
     * @param string $action ruta de accion
     * @param string|array $attrs atributos
     */
    public static function headLinkAction($action, $attrs = NULL)
    {
        self::headLink(PUBLIC_PATH . $action, $attrs);
    }
    
    /**
     * Enlaza un recurso de la aplicacion
     *
     * @param string $resource ubicacion del recurso en public
     * @param string|array $attrs atributos
     */
    public static function headLinkResource($resource, $attrs = NULL)
    {
        self::headLink(PUBLIC_PATH . $resource, $attrs);
    }
    
    /**
     * Incluye los links para el head
     *
     * @return string
     */
    public static function includeHeadLinks()
    {
        $code = '';
        foreach(self::$_headLinks as $link) {
            $code .= "<link href=\"{$link['href']}\" {$link['attrs']}/>" . PHP_EOL;
        }
        return $code;
    }
}
