<?php

class AngelType_model_test extends PHPUnit_Framework_TestCase {

	private $angle_id = null;
	private $angle_id2 = null;

	public function create_AngelType()
	{
		$this->angle_id = AngelType_create('test', true, 'test', true);
		$this->angle_id2 = AngelType_create('test1', true, 'test', true);
	}

	public function test_AngelType(){
	
		$this->create_AngelType();
		$Angeltype = AngelType($this->angle_id);
 		$this->assertNotFalse($Angeltype);
    	$this->assertNotNull($Angeltype);
	}
}	
?>
