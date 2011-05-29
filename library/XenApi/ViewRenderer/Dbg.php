<?php

class XenApi_ViewRenderer_Dbg extends XenApi_ViewRenderer_Abstract
{
	/**
	 * Renders output of an error.
	 *
	 * @param string Text of the error to render
	 *
	 * @return string|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderError($errorText)
	{
		return '<pre>' . print_r($errorText, true) . '</pre>';
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
		return '<pre>' . print_r($data, true) . '</pre>';
	}
}