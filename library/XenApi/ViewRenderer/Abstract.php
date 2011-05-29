<?php

/**
 * For tracking purposes
 */
abstract class XenApi_ViewRenderer_Abstract extends XenForo_ViewRenderer_Abstract
{
	protected $_needsContainer = false;

	abstract public function renderData(array $data);

	/*
	 * Unused in API-Land
	 */
	final public function renderUnrepresentable() {return '';}
	final public function renderView($viewName, array $params = array(), $templateName = '', XenForo_ControllerResponse_View $subView = null) {return '';}
	final public function renderContainer($contents, array $params = array()) {return '';}
}
 
