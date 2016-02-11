<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Building Block object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponding  Building Block Access class.
*/

class ilObjBuildingBlockPoolListGUI extends ilObjectPluginListGUI {
	/**
	* This is probably more of a hack, since this functions responsibility nothing has to do with GUI properties, as it would seem.
	*/
	public function initType() {
		$this->setType("xbbp");
	}

	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass() {
		return "ilObjBuildingBlockGUI";
	}

	/**
	* Get commands
	*/
	function initCommands() {
		return array(array("permission" => "read",
							"cmd" => "showContent",
							"default" => true
						)
				);
	}

	/**
	* Get item properties
	*
	* @return        array                array of property arrays:
	*                                "alert" (boolean) => display as an alert property (usually in red)
	*                                "property" (string) => property name
	*                                "value" (string) => property value
	*/
	function getProperties() {
		global $lng, $ilUser;

		$props = array();

		$this->plugin->includeClass("class.ilObjBuildingBlockPoolAccess.php");
		if (!ilObjBuildingBlockPoolAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->txt("status"),
			"value" => $this->txt("offline"));
		}

		return $props;
	}
}