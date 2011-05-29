<?php

/**
 * Print_r viewrenderer
 */
class XenApi_ViewRenderer_Dbg extends XenApi_ViewRenderer_Abstract
{
	/**
	 * Returns any special mime types
	 *
	 * @return void
	 */
	protected function _getMimeType()
	{
		return 'text/text';
	}

	/**
	 * Runs the data through print_r
	 *
	 * @param  $data
	 * @return mixed
	 */
	protected function _print($data)
	{
		return print_r($data, true);
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
		return $this->_print(array('error' => $errorText));
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

	/**
	 * Renders any data passed from the controller
	 *
	 * @abstract
	 * @param array $data
	 * @return void
	 */
	public function renderData(array $data)
	{
		return $this->_format($this->_print($data));
	}
}