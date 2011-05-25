<?php

class XenApi_ControllerApi_Forum extends XenApi_ControllerApi_Abstract
{
	public function actionIndex()
	{
		return $this->responseData(array('foo' => 'bar'));
	}
}