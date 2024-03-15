<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class SoapClientController extends Controller
{
    // The url of WSDL to get catalog information
    private $catalog_wsdl;

    public function __construct()
    {
        $this->catalog_wsdl = env('CATALOG_WSDL');
    }

    /**
     * Shows list of functions of remote web-service.
     * @throws SoapFault
     */
    public function showFunctions()
    {
        echo "Functions of <a href='" . $this->catalog_wsdl . "' target='_blank'>" . $this->catalog_wsdl . "</a><br>";
        $client = new SoapClient($this->catalog_wsdl);
        foreach ($client->__getFunctions() as $key => $row) {
            $func = explode(" ", $row)[0];
            $func = str_replace("Response", "", $func);
            echo $key + 1 . ". <a href='run/" . $func . "' target='_blank'>" . $func . "</a><br>";
        }
    }

    /**
     * Runs function that passed in request as soap-request.
     * @param Request $request
     * Can send parameters.
     */
    public function runFunction(Request $request)
    {
        $functionName = last(explode("/", $request->getPathInfo()));
        $params = Input::all();
        try {
            $client = new SoapClient($this->catalog_wsdl);
            $response = $client->__soapCall($functionName, array($params));
            dd($response);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    // id_equipment_type = alma_equipment_type
    public function getEquipmentKitsType()
    {
        try {
            $table = 'alma_equipment_type';
            $client = new SoapClient($this->catalog_wsdl);
            $response = $client->__soapCall('getEquipmentKitsType', array());
            $data = $response ? json_decode(json_encode($response->Response->elements->element), true) : array();
            if (!empty($data)) {
                DB::table($table)->truncate();
                $arr = array();
                foreach ($data as &$row) {
                    $row = array_values($row);
                    $arr[] = array(
                        "id_equipment_type" => $row[0],
                        "v_name" => $row[1],
                        "v_mnemonic" => $row[2]
                    );
                }
                DB::table($table)->insert($arr);
                $successText = "getEquipmentKitsType function successfully completed.";
                echo $successText;
                Log::notice($successText);
            } else {
                $failText = "getEquipmentKitsType function failed. Empty data from web-service or request was failed.";
                echo $failText;
                Log::notice($failText);
            }
        } catch (\Throwable $e) {

            echo "Some error occured. Read more in log files.";
            Log::error($e->getMessage());
            exit;
        }
    }

    /**
     */
    public function getServiceAddress()
    {
        try {
            $params = Input::all();
            $client = new SoapClient($this->catalog_wsdl);
            $response = $client->__soapCall('getServiceAddress', array($params));
            dd($response);
        } catch (\Throwable $th) {
            echo "Some error occured. Read more in log files.";
            Log::error($th->getMessage());
            exit;
        }
    }
}
