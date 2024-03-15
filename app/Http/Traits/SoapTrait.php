<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use SoapClient;

trait SoapTrait
{
    private $path = __DIR__ . "/../../../storage/logs/";

    /**
     * Отмена заявки
     * @param $params
    */
    public function CancelFlowOut($params)
    {
        $directory = $this->path ."cancelflowout/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CancelFlowOut', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - cancelFlow - " .
                json_encode($response). " - " . $params['id_flow'] . "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - cancelFlow - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - cancelFlow - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function CloseFlowConnect($params)
    {
        $directory = $this->path ."closeflowconnect/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowConnect', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowConnect - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowConnect - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowConnect - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function CloseFlowRepair($params)
    {
        Log::info('paramsOfSoap',['data' => $params]);
        $directory = $this->path ."closeflowrepair/" . date('Y-m-d');

        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }

        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowRepair', array($params));

            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowRepair - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowRepair - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);

            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;

            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowRepair - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function CloseFlowBlockDebt($params)
    {
        $directory = $this->path ."closeflowblockdebt/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowBlockDebt', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlockDebt - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlockDebt - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlockDebt - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }
    public function CloseFlowBlock($params)
    {
        $directory = $this->path ."closeflowblock/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowBlock', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlock - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlock - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowBlock - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function CloseFlowUnblock($params)
    {
        $directory = $this->path ."closeflowunblock/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowUnblock', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowUnblock - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowUnblock - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowUnblock - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function CloseFlowChangeProduct($params)
    {
        $directory = $this->path ."closeflowchangeproduct/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowChangeProduct', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeProduct - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeProduct - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeProduct - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }
    public function CloseFlowChangeTech($params)
    {
        $directory = $this->path ."closeflowchangetech/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowChangeTech', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeTech - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeTech - " .
                json_encode($response)  . " - " . $params['id_flow']. "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowChangeTech - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }
    public function CloseFlowDissolution($params)
    {
        $directory = $this->path ."closeflowdissolution/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            file_put_contents($directory.'/env.log', config('app.fw_wsdl') . "\n", FILE_APPEND);
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowDissolution', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowDissolution - " .
                json_encode($params). "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowDissolution - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowDissolution - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }
    public function CloseFlowIntake($params)
    {
        $directory = $this->path ."closeflowintake/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('CloseFlowIntake', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowIntake - " .
                json_encode($params) . "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowIntake - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - CloseFlowIntake - " .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    /**
     * Назначение заявки
     * @param $params
     */
    public function SetDate($params)
    {
        $directory = $this->path ."setdate/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('SetDate', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - SetDate - " .
                json_encode($params) . "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - SetDate - " . "[" . $params['id_flow'] . "]" .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - SetDate - " . "[" . $params['id_flow'] . "]" .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    /**
     * Отложение заявки - postpone
     * @param $params
    */
    public function MoveToDelayedFW($params)
    {
        $directory = $this->path ."movetodelayedfw/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('MoveToDelayedFW', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - MoveToDelayedFW - " . $params['id_flow'] . "]" .
                json_encode($response) . " - " . $params['dt_delayed'] . "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - MoveToDelayedFW - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - MoveToDelayedFW - " . $params['id_flow'] . "]" .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }
    /**
     * Перенос из Отложено
     * @param $params
    */
    public function MoveFromDelayedFW($params)
    {
        $directory = $this->path ."movefromdelayedfw/" . date('Y-m-d');
        if(!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
        try
        {
            $client = new SoapClient(config('app.fw_wsdl'));
            $response = $client->__soapCall('MoveFromDelayedFW', array($params));
            file_put_contents($directory . "/soap_params.log", "[" . date("Y-m-d H:i:s") . "] - MoveFromDelayedFW - " . $params['id_flow'] . "]" .
                json_encode($response) . "\n", FILE_APPEND);
            file_put_contents($directory . "/response.log", "[" . date("Y-m-d H:i:s") . "] - MoveFromDelayedFW - " .
                json_encode($response) . " - " . $params['id_flow'] . "\n", FILE_APPEND);
            $soapResponse = collect();
            $soapResponse->code = $response->return->code;
            $soapResponse->message = $response->return->message;
            return $soapResponse;
        }
        catch(\Throwable $th)
        {
            file_put_contents($directory . "/exceptions.log", "[" . date("Y-m-d H:i:s") . "] - MoveFromDelayedFW - " . $params['id_flow'] . "]" .
                $th->getMessage() . "\n", FILE_APPEND);
            return $this->returnFail();
        }
    }

    public function returnFail()
    {
        $soapResponse = collect();
        $soapResponse->code = 500;
        $soapResponse->message = 'Произошла системная ошибка при отправке запроса в Forward.';

        return $soapResponse;
    }
}
