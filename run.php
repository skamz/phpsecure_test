<?php

//Это тест уязвимостей вызываемых через call_user_func

class CallbacksFilter {

	private array $callbacks;

	public function registerCallback($name, $function) {
		$this->callbacks[$name] = $function;
		$testValue = $this->callbacks[$name];
	}

	public function doCallback($name, $param1, $param2) {
		$this->callbacks[$name]($param1, $param2);
	}

	public function doCallbackCallUserFunc($name, $param1, $param2) {
		call_user_func($this->callbacks[$name], $param1, $param2);
	}

	public function doCallbackCallArray($name, $param1, $param2) {
		call_user_func_array($this->callbacks[$name], [$param1, $param2]);
	}

	public function doCallbackCallArrayV2($name, ...$args) {
		call_user_func_array($this->callbacks[$name], $args);
	}

}

function testFuncName($str, $param) {
	//echo "Call testFuncName with params";
	//print_r(func_get_args());
	mysqli_query($conn, $param);
}

function testFuncNameTrue($str, $para){
	return true;
}

$callBacksObj = new CallbacksFilter();
//$callBacksObj->registerCallback("test", "testFuncNameTrue"); // если безопасная функция регистрируется первой, то уязвимости как бы нет. В данном случае функция переопределяется
$callBacksObj->registerCallback("test", "testFuncName");
//$callBacksObj->registerCallback("test", "testFuncNameTrue"); // если безопасная функция регистрируется последней, то уязвимости нет

$callBacksObj->doCallback("test", "a", "g"); // clean
$callBacksObj->doCallback("test2", "a", $_GET["q"]); // clean
$callBacksObj->doCallback("test", "a", $_GET["q"]); // SQLInjection
$callBacksObj->doCallback("dfkjhkjh", "a", $_POST["t"]); // clean
$callBacksObj->doCallbackCallUserFunc("dfkjhkjh", "a", $_POST["t"]); // clean
$callBacksObj->doCallbackCallArray("test", "a", "b"); // clean
$callBacksObj->doCallbackCallUserFunc("test", "a", $_POST["t"]); // SQLInjection
$callBacksObj->doCallbackCallArray("test", "a", $_GET["name"]); // SQLInjection
$callBacksObj->doCallbackCallArray("test3", "a", $_GET["name"]); // clean
$callBacksObj->doCallbackCallArrayV2("test", "a", $_GET["name"]); // todo add implement ...$args
