<?php
class ReturnState
{
	public $status;
	public $additionalInfo;
	function __construct($status, $additionalInfo)
	{
		$this->status = $status;
		$this->additionalInfo = $additionalInfo;
	}
}
