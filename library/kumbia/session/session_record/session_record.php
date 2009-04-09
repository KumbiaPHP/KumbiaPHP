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
 * @package Session
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase que actua como un ActiveRecord de Session
 *
 * @category Kumbia
 * @package Session
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
class SessionRecord {

  	private $records;
  	public $id;
  	public $persistent = true;

  	function create($arr=''){
  		if(!is_array($arr)) $arr = get_params(func_get_args());
  	  	if(!is_array($arr)&&!$this->id){
  	  	  	Flash::kumbia_error("Cannot save constant value on Session Record '$this->source'");
			return;
		}
		if(!is_array($arr)){
			$arr = array();
			foreach($this->get_attributes() as $at){
				$arr[$at] = $this->$at;
			}
		}
		$max = 0;
		if(is_array($this->records)){
			foreach($this->records as $r){
			  	if(!$max) $max = $r['id'];
			  	if($r['id']==$arr['id']){
			  	  	Flash::kumbia_error("Cannot insert duplicate id on Session Record '$this->source'");
			  	  	return;
			  	}
				if($r['id']>$max) $max = $r['id'];
			}
		}
		if(!$arr['id']) $arr['id'] = ++$max;
  	  	$rec = array();
		foreach($arr as $key => $value){
		  	if($this->is_attribute($key))
			 	$rec[$key] = $value;
			else {
			  	Flash::kumbia_error("Field $key is not defined on Session Record '$this->source' when creating");
			  	return;
			}
		}
		$this->records[] = $rec;
	}

	function save(){
	  	$record = false;
  	  	$n = 0;
		if(is_array($this->records)){
			foreach($this->records as $r){
		  		if(!$max) $max = $r['id'];
		  		if(!is_null($this->id)){
				  	if($r['id']==$this->id){
				  	  	$record = $n;
				  	}
				}
				if($r['id']>$max) $max = $r['id'];
				$n++;
			}
		}
		if(!$this->id) $this->id = ++$max;
		if($record===false){
		  	$rec = array();
		  	foreach($this->get_attributes() as $at){
				$rec[$at] = $this->$at;
			}
			$this->create($rec);
		} else {
		  	$this->update($id, $record);
		}

  	}

  	function update($id='', $n=''){
  	  	if($id==='') $id = $this->id;
  	  	if($n===''){
  	  	  	$n = 0;
			if(is_array($this->records)){
				foreach($this->records as $r){
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->records[$n][$at] = $this->$at;
						}
				  	  	return;
				  	}
					$n++;
				}
			}
		} else {
		  	if(isset($this->records[$n])){
			  	foreach($this->get_attributes() as $at){
					$this->records[$n][$at] = $this->$at;
				}
				return;
			}
		}
	}

	function delete($id=''){
	  	if($id!==''){
	  		$n = 0;
			foreach($this->records as $r){
			  	if($r['id']==$id){
					unset($this->records[$n]);
			  	}
				$n++;
			}
		} else {
		  	foreach($this->get_attributes() as $at){
			    $this->$at = '';
			}
			unset($this->records);
			$this->records = array();
		}

  	}

  	function is_attribute($att){
	    return in_array($att, $this->get_attributes());
	}

	function show(){ }

	function show_records(){
	  	print_r($this->records);
	}

  	function get_attributes(){
  	  	$atts = array();
	    foreach($this as $key => $t){
		  	if(!is_callable($this, $key)
			  &&($key!="source")
			  &&($key!="persistent")
			  &&!is_array($this->$key)) $atts[] = $key;
		}
		return $atts;
	}

	function find($id=''){
		if($id!=''){
		  	$n = 0;
		  	foreach($this->get_attributes() as $at){
			    $this->$at = '';
			}
		  	if(is_array($this->records)){
				foreach($this->records as $r){
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->$at = $this->records[$n][$at];
						}
						return $this->dump($this->records[$n][$at]);
				  	}
					$n++;
				}
			}
			return false;
		} else {
		  	$results = array();
		  	if(is_array($this->records)){
				foreach($this->records as $r){
					$results[] = $this->dump($r);
				}
			}
			return count($results) ? $results : array();
		}
	}

	function find_first($id=''){
		if($id!==''){
		  	$n = 0;
		  	if($this->records){
				foreach($this->records as $r){
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->$at = $this->records[$n][$at];
						}
						return $this->dump($this->records[$n][$at]);
				  	}
					$n++;
				}
			}
			return false;
		} else {
		  	if(is_array($this->records)){
				foreach($this->records as $r){
					return $this->dump($r);
				}
			}
		}
	}

	function dump($rec){
	  	$obj = clone $this;
	  	foreach($this->get_attributes() as $at){
		    $obj->$at = $rec[$at];
		}
		return $obj;
	}

}
