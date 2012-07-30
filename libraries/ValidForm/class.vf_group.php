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
  
require_once('class.vf_element.php');

/**
 * 
 * Group Class
 * 
 * @package ValidForm
 * @author Felix Langfeldt
 * @version Release: 0.2.2
 *
 */
class VF_Group extends VF_Element {
	protected $__fields = array();

	public function toHtml($submitted = FALSE, $blnSimpleLayout = FALSE) {
		$blnError = ($submitted && !$this->__validator->validate()) ? TRUE : FALSE;
		
		$strClass = ($this->__validator->getRequired()) ? "vf__required" : "vf__optional";
		$strClass = ($blnError) ? $strClass . " vf__error" : $strClass;		
		$strOutput = "<div class=\"{$strClass}\">\n";
		
		if ($blnError) $strOutput .= "<p class=\"vf__error\">{$this->__validator->getError()}</p>";
		
		$strLabel = (!empty($this->__requiredstyle) && $this->__validator->getRequired()) ? sprintf($this->__requiredstyle, $this->__label) : $this->__label;
		if (!empty($this->__label)) $strOutput .= "<label{$this->__getLabelMetaString()}>{$strLabel}</label>\n";
		$strOutput .= "<fieldset class=\"vf__list\">\n";
		
		foreach ($this->__fields as $field) {
			$strOutput .= $field->toHtml($this->__getValue($submitted), $submitted);
		}
		
		$strOutput .= "</fieldset>\n";
		if (!empty($this->__tip)) $strOutput .= "<small class=\"vf__tip\">{$this->__tip}</small>\n";		
		$strOutput .= "</div>\n";
		
		return $strOutput;
	}
	
	public function toJS() {
		$strCheck = $this->__validator->getCheck();
		$strCheck = (empty($strCheck)) ? "''" : str_replace("'", "\\'", $strCheck);
		$strRequired = ($this->__validator->getRequired()) ? "true" : "false";
		$intMaxLength = ($this->__validator->getMaxLength() > 0) ? $this->__validator->getMaxLength() : "null";
		$intMinLength = ($this->__validator->getMinLength() > 0) ? $this->__validator->getMinLength() : "null";
		
		switch ($this->__type) {
			case VFORM_RADIO_LIST:
				$name = $this->__name;
				break;
			case VFORM_CHECK_LIST:
				$name = (strpos($this->__name, "[]") === FALSE) ? $this->__name . "[]" : $this->__name;
				break;
		}
		
		return "objForm.addElement('{$name}', '{$name}', {$strCheck}, {$strRequired}, {$intMaxLength}, {$intMinLength}, '" . addslashes($this->__validator->getFieldHint()) . "', '" . addslashes($this->__validator->getTypeError()) . "', '" . addslashes($this->__validator->getRequiredError()) . "', '" . addslashes($this->__validator->getHintError()) . "', '" . addslashes($this->__validator->getMinLengthError()) . "', '" . addslashes($this->__validator->getMaxLengthError()) . "');\n";
	}
	
	public function addField($label, $value, $checked = FALSE, $meta = array()) {
		switch ($this->__type) {
			case VFORM_RADIO_LIST:
				$type = "radio";
				$name = $this->__name;
				break;
			case VFORM_CHECK_LIST:
				$type = "checkbox";
				$name = (strpos($this->__name, "[]") === FALSE) ? $this->__name . "[]" : $this->__name;
				break;
		}
	
		$objField = new VF_GroupField($this->getRandomId($this->__name), $name, $type, $label, $value, $checked, $meta);
		array_push($this->__fields, $objField);
		
		return $objField;
	}
	
}

?>