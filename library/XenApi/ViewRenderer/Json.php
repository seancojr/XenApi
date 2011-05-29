<?php

class XenApi_ViewRenderer_Json extends XenApi_ViewRenderer_Abstract
{
	/**
	 * Returns any special mime types
	 *
	 * @return void
	 */
	protected function _getMimeType()
	{
		return 'application/json';
	}

	/**
	 * Renders output of an error.
	 *
	 * @param string Text of the error to render
	 *
	 * @return string|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderError($errorText)
	{
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput(array(
			'error' => $errorText
		));
	}

	/**
	 * Renders output of an message.
	 *
	 * @param string Text of the message to render
	 *
	 * @return string|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderMessage($message)
	{
		// TODO: Implement renderMessage() method.
	}

	public function renderData(array $data)
	{
		return $this->_format(
			XenForo_ViewRenderer_Json::jsonEncodeForOutput($data)
		);
	}
}