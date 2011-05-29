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
			$this->_encodeJson($data)
		);
	}

	/**
	 * Returns a JSON representation of a value
	 *
	 * Code borrowed from MediaWiki
	 *
	 * @param  $data
	 * @return void
	 */
	protected function _encodeJson($data)
	{
		// Some versions of PHP have a broken json_encode, see PHP bug
		// 46944. Test encoding an affected character (U+20000) to
		// avoid this.
		if ( !function_exists( 'json_encode' ) || $this->_isHtml || strtolower( json_encode( "\xf0\xa0\x80\x80" ) ) != '\ud840\udc00' ) {
			$this->_loadJsonService();
			$json = new Services_JSON();
			return $json->encode( $data, $this->_isHtml );
		} else {
			return json_encode( $data );
		}
	}

	private function _loadJsonService()
	{
		if (class_exists('Services_JSON')) return;

		global $fileDir;

		require($fileDir . '/library/XenApi/Services/JSON.php');
	}
}