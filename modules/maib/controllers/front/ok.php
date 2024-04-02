<?php

require_once __DIR__ . "/../../src/MaibAuthRequest.php";
require_once __DIR__ . "/../../src/MaibApiRequest.php";
require_once __DIR__ . "/../../src/MaibSdk.php";

class_alias("MaibEcomm\MaibSdk\MaibAuthRequest", "MaibAuthRequest");
class_alias("MaibEcomm\MaibSdk\MaibApiRequest", "MaibApiRequest");

class MaibOkModuleFrontController extends ModuleFrontController {
    public function postProcess()
    {
        exit('ok');
    }
}