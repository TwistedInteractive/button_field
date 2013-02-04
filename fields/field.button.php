<?php
/**
 * Created by Symphony Extension Developer.
 * Part of 'Button field' extension.
 * 2013-02-04
 */

if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

Class FieldButton extends Field {
	

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->_name = __('Button');

		// Set defaults:
		$this->set('show_column', 'yes');
		$this->set('required', 'no');
		$this->set('location', 'sidebar');

		
	}

	/**
	 * Creation of the data table:
	 * @return mixed
	 */
	public function createTable() {
		return Symphony::Database()->query("
			CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `entry_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");
	}

	/**
	 * Display the publish panel for this field. The display panel is the
	 * interface shown to Authors that allow them to input data into this
	 * field for an `Entry`.
	 *
	 * @param XMLElement $wrapper
	 *	the XML element to append the html defined user interface to this
	 *	field.
	 * @param array $data (optional)
	 *	any existing data that has been supplied for this field instance.
	 *	this is encoded as an array of columns, each column maps to an
	 *	array of row indexes to the contents of that column. this defaults
	 *	to null.
	 * @param mixed $flagWithError (optional)
	 *	flag with error defaults to null.
	 * @param string $fieldnamePrefix (optional)
	 *	the string to be prepended to the display of the name of this field.
	 *	this defaults to null.
	 * @param string $fieldnamePostfix (optional)
	 *	the string to be appended to the display of the name of this field.
	 *	this defaults to null.
	 * @param integer $entry_id (optional)
	 *	the entry id of this field. this defaults to null.
	 */
	public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null) {
		// Assuming your entry has a 'value'-column in it's data table:

		if(!is_null($entry_id))
		{
			$value = General::sanitize($data['value']);

			$label = Widget::Label($this->get('label'));

			$action = str_replace('{$id}', $entry_id, $this->get('action'));

			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix,
				$this->get('title'), 'button', array('style'=>'display: block; padding: 5px 0; width: 100%; cursor: pointer;',
				'onclick'=>'window.open(\''.$action.'\');')));

			if($flagWithError != NULL) $wrapper->appendChild(Widget::Error($label, $flagWithError));
			else $wrapper->appendChild($label);
		}
	}

	public function displaySettingsPanel($wrapper, $errors = null)
	{
		parent::displaySettingsPanel($wrapper, $errors);
/*		$headers = $wrapper->getChildrenByName('header');
		$header = $headers[0];*/

		$group = new XMLElement('div');
		$group->setAttribute('class', 'two columns');

		$label = Widget::Label(__('Title'));
		$label->setAttribute('class', 'column');

		$label->appendChild(Widget::Input('fields['.$this->get('sortorder').'][title]', $this->get('title')));
		if(isset($errors['title'])) $group->appendChild(Widget::Error($label, $errors['title']));
		else $group->appendChild($label);

		$label = Widget::Label(__('Action'));
		$label->setAttribute('class', 'column');

		$label->appendChild(Widget::Input('fields['.$this->get('sortorder').'][action]', $this->get('action')));
		if(isset($errors['action'])) $group->appendChild(Widget::Error($label, $errors['action']));
		else $group->appendChild($label);

		$wrapper->appendChild($group);

		$div = new XMLElement('div', NULL, array('class' => 'two columns'));
		// $this->appendRequiredCheckbox($div);
		$this->appendShowColumnCheckbox($div);
		$wrapper->appendChild($div);

	}

	public function commit()
	{
		if(parent::commit())
		{
			return FieldManager::saveSettings($this->get('id'), array(
				'title' => $this->get('title'),
				'action' => $this->get('action')
			));
		}
	}

	public function prepareTableValue($data, $link = null, $entry_id = null)
	{
		$action = str_replace('{$id}', $entry_id, $this->get('action'));
		return new XMLElement('a', $this->get('title'), array('target'=>'_blank', 'href'=>$action));
	}

	/**
	 * Process the raw field data.
	 *
	 * @param mixed $data
	 *	post data from the entry form
	 * @param integer $status
	 *	the status code resultant from processing the data.
	 * @param string $message
	 *	the place to set any generated error message. any previous value for
	 *	this variable will be overwritten.
	 * @param boolean $simulate (optional)
	 *	true if this will tell the CF's to simulate data creation, false
	 *	otherwise. this defaults to false. this is important if clients
	 *	will be deleting or adding data outside of the main entry object
	 *	commit function.
	 * @param mixed $entry_id (optional)
	 *	the current entry. defaults to null.
	 * @return array
	 *	the processed field data.
	 */
	public function processRawFieldData($data, &$status, &$message=null, $simulate=false, $entry_id=null) {
		$status = self::__OK__;

		// Assuming your entry has a 'value'-column in it's data table:
		return array();
	}

	/**
	 * Append the formatted XML output of this field as utilized as a data source.
	 *
	 * @param XMLElement $wrapper
	 *	the XML element to append the XML representation of this to.
	 * @param array $data
	 *	the current set of values for this field. the values are structured as
	 *	for displayPublishPanel.
	 * @param boolean $encode (optional)
	 *	flag as to whether this should be html encoded prior to output. this
	 *	defaults to false.
	 * @param string $mode
	 *	 A field can provide ways to output this field's data. For instance a mode
	 *  could be 'items' or 'full' and then the function would display the data
	 *  in a different way depending on what was selected in the datasource
	 *  included elements.
	 * @param integer $entry_id (optional)
	 *	the identifier of this field entry instance. defaults to null.
	 */
	public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
		$wrapper->appendChild(new XMLElement($this->get('element_name'), ($encode ? General::sanitize($this->prepareTableValue($data, null, $entry_id)) : $this->prepareTableValue($data, null, $entry_id))));
	}


}
