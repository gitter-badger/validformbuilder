<?php
/***************************
 * ValidForm Builder - build valid and secure web forms quickly
 * 
 * Copyright (c) 2009-2012, Felix Langfeldt <flangfeldt@felix-it.com>.
 * All rights reserved.
 * 
 * This software is released under the GNU GPL v2 License <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * 
 * @package    ValidForm
 * @author     Felix Langfeldt <flangfeldt@felix-it.com>
 * @copyright  2009-2012 Felix Langfeldt <flangfeldt@felix-it.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @link       http://code.google.com/p/validformbuilder/
 ***************************/
 
require_once('class.classdynamic.php');

/**
 * 
 * Element Class
 *
 * @package ValidForm
 * @author Felix Langfeldt
 * @version 0.2.2
 *
 */
class VF_Element extends ClassDynamic {
	protected $__id;
	protected $__name;
	protected $__label;
	protected $__tip;
	protected $__type;
	protected $__meta;
	protected $__labelmeta;
	protected $__hint;
	protected $__default;
	protected $__dynamic;
	protected $__dynamicLabel;
	protected $__requiredstyle;
	protected $__validator;
	protected $__targetfield = null;
	protected $__triggerfield = null;
	protected $__reservedMeta = array("tip", "hint", "default", "width", "height", "length", "start", "end", "path", "labelStyle", "labelClass", "labelRange", "valueRange", "dynamic", "dynamicLabel", "matchWith");

	public function __construct($name, $type, $label = "", $validationRules = array(), $errorHandlers = array(), $meta = array()) {
		if (is_null($validationRules)) $validationRules = array();
		if (is_null($errorHandlers)) $errorHandlers = array();
		if (is_null($meta)) $meta = array();

		// Set meta class
		$this->setClass($type, $meta);
		
		$labelMeta = (isset($meta['labelStyle'])) ? array("style" => $meta['labelStyle']) : array();
		if (isset($meta['labelClass'])) $labelMeta["class"] = $meta['labelClass'];
		
		$this->__id = (strpos($name, "[]") !== FALSE) ? $this->getRandomId($name) : $name;
		$this->__name = $name;
		$this->__label = $label;
		$this->__type = $type;
		$this->__meta = $meta;
		$this->__labelmeta = $labelMeta;
		$this->__tip = (array_key_exists("tip", $meta)) ? $meta["tip"] : NULL;
		$this->__hint = (array_key_exists("hint", $meta)) ? $meta["hint"] : NULL;
		$this->__default = (array_key_exists("default", $meta)) ? $meta["default"] : NULL;
		$this->__dynamic = (array_key_exists("dynamic", $meta)) ? $meta["dynamic"] : NULL;
		$this->__dynamicLabel = (array_key_exists("dynamicLabel", $meta)) ? $meta["dynamicLabel"] : NULL;
		
		$this->__validator = new VF_FieldValidator($name, $type, $validationRules, $errorHandlers, $this->__hint);		
	}

	protected function setClass($type, &$meta) {
		$strClass = "";
		switch ($type) {
			case VFORM_STRING:
			case VFORM_WORD:
			case VFORM_EMAIL:
			case VFORM_URL:
			case VFORM_SIMPLEURL:
			case VFORM_CUSTOM:	
			case VFORM_CURRENCY:
			case VFORM_DATE:
			case VFORM_NUMERIC:
			case VFORM_INTEGER:
			case VFORM_PASSWORD:
				$meta["class"] = (!isset($meta["class"])) ? "vf__text" : $meta["class"] . " vf__text";
				break;
			case VFORM_CAPTCHA:
				$meta["class"] = (!isset($meta["class"])) ? "vf__text_small" : $meta["class"] . " vf__text_small";
				break;
			case VFORM_HTML:
			case VFORM_CUSTOM_TEXT:
			case VFORM_TEXT:
				$meta["class"] = (!isset($meta["class"])) ? "vf__text" : $meta["class"] . " vf__text";
				break;
			case VFORM_FILE:
				$meta["class"] = (!isset($meta["class"])) ? "vf__file" : $meta["class"] . " vf__file";
				break;
			case VFORM_BOOLEAN:
				$meta["class"] = (!isset($meta["class"])) ? "vf__checkbox" : $meta["class"] . " vf__checkbox";
				break;
			case VFORM_RADIO_LIST:
			case VFORM_CHECK_LIST:
				$meta["class"] = (!isset($meta["class"])) ? "vf__radiobutton" : $meta["class"] . " vf__radiobutton";
				break;
			case VFORM_SELECT_LIST:
				if (!isset($meta["class"])) {
					if (!isset($meta["multiple"])) {
						$meta["class"] = "vf__one";
					} else {
						$meta["class"] = "vf__multiple";
					}
				} else {
					if (!isset($meta["multiple"])) {
						$meta["class"] .= " vf__one";
					} else {
						$meta["class"] .= " vf__multiple";
					}
				}
				break;
		}

		if (!empty($strClass)) {
			$meta["class"] = (isset($meta["class"])) ? $meta["class"] .= " " . $strClass : $strClass;
		}
	}
	
	public function toHtml($submitted = FALSE, $blnSimpleLayout = FALSE) {
		return "Field type not defined.";
	}
	
	public function setError($strError) {
		//*** Override the validator message.
		$this->__validator->setError($strError);
	}
	
	public function toJS() {
		return "alert('Field type not defined.');\n";
	}
	
	public function getRandomId($name) {
		$strReturn = $name;
		
		if (strpos($name, "[]") !== FALSE) {
			$strReturn = str_replace("[]", "_" . rand(100000, 900000), $name);
		} else {
			$strReturn = $name . "_" . rand(100000, 900000);
		}
		
		return $strReturn;
	}
	
	public function isValid() {
		return $this->__validator->validate();
	}
	
	public function isDynamic() {
		return ($this->__dynamic) ? true : false;
	}
	
	public function getDynamicCount() {
		return ValidForm::get($this->getName() . "_dynamic", 0);
	}
	
	public function getValue($intDynamicPosition = 0) {
		$varValue = NULL;
		
		if ($intDynamicPosition > 0) {
			$objValidator = $this->__validator;
			$objValidator->validate($intDynamicPosition);
			
			$varValue = $objValidator->getValidValue();
		} else {
			$varValue = $this->__validator->getValidValue();
		}
		
		return $varValue;
	}
	
	public function hasFields() {
		return FALSE;
	}

	/**
	 * Add javascript code for trigger fields. This code executed by the element's toJs() method.
	 */
	public function addTriggerJs($strId = null) {
		$strId = (!is_null($strId)) ? $strId : $this->__triggerfield->getId();
		return "objForm.addTrigger('{$this->__triggerfield->getId()}', '{$this->__id}');\n";
	}

	/**
	 * Link a field to this element. If the trigger field is selected / checked, this element will become enabled.
	 * @param vf_element $objField ValidForm Builder field element
	 */
	public function setTrigger($objField) {
		$this->__triggerfield = $objField;
	}

	/**
	 * Check if this element has a triggerfield.
	 * @return boolean True if a triggerfield is set, false if not.
	 */
	public function hasTrigger() {
		return is_object($this->__triggerfield);
	}

	/**
	 * If an element's name is updated, also update the name in it's corresponding validator.
	 * @param string $strName The new name
	 */
	public function setName($strName) {
		parent::setName($strName);
		if (is_object($this->__validator)) {
			$this->__validator->setFieldName($strName);
		}
	}
	
	protected function __getValue($submitted = FALSE) {
		$strReturn = NULL;
		
		if ($submitted) {
			if ($this->__validator->validate()) {
				$strReturn = $this->__validator->getValidValue();
			} else {
				$strReturn = $this->__validator->getValue();
			}		
		} else {
			if (!empty($this->__default)) {
				$strReturn = $this->__default;
			} else if (!empty($this->__hint)) {
				$strReturn = $this->__hint;
			}
		}
		
		return $strReturn;
	}

	protected function __getValidValue($submitted = false) {
		$varValidValue = $this->__validator->getValidValue();
		$varReturn = null;

		if (!is_null($varValidValue)) {
			echo "cool";
			return $varValidValue;
		} else {
			return $this->__getValue($submitted);
		}
	}
	
	protected function __getMetaString() {
		$strOutput = "";
		
		foreach ($this->__meta as $key => $value) {
			if (!in_array($key, $this->__reservedMeta)) {
				$strOutput .= " {$key}=\"{$value}\"";
			}
		}
		
		return $strOutput;
	}
	
	protected function __getLabelMetaString() {
		$strOutput = "";
		
		if (is_array($this->__labelmeta)) {
			foreach ($this->__labelmeta as $key => $value) {
				if (!in_array($key, $this->__reservedMeta)) {
					$strOutput .= " {$key}=\"{$value}\"";
				}
			}
		}
				
		return $strOutput;
	}

}

?>