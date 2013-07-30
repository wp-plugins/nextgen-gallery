<?php

class C_Resource_Minifier_Installer
{
	function get_registry()
	{
		return C_Component_Registry::get_instance();
	}

	function uninstall($hard=FALSE)
	{
		$manager = $this->get_registry()->get_utility('I_Resource_Manager');
		$manager->flush_cache();
	}
}
