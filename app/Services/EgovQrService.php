<?php

namespace App\Services;

use App\Http\Requests\EgovQr\CheckRequest;
use App\Http\Requests\EgovQr\GenerateRequest;
use App\Http\Requests\EgovQr\GetAddressRequest;
use App\Models\SuzRequest;
use GuzzleHttp\Client;

class EgovQrService
{
    private string $address;
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->address = env('EGOV_SERVICE');
    }

    //TODO Пока предусмотренны только гет запросы.
    private function send($uri): array
    {
        $response = $this->client->get($uri);
        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        $data = json_decode($responseBody, true);
        $isJson = $data !== null && json_last_error() === JSON_ERROR_NONE;
        
        if (!$isJson) {
            $data = $responseBody;
        }

        return compact('statusCode', 'data');
    }

    public function generate(GenerateRequest $request): array
    {
        $data = $request->validated();

        $uri = $this->address . 'qr?iin=' . $data['iin'] . '&phone=' . $data['phone'] . '&suz_id=' . $data['suz_id'] . '&doc_type=public_agreement';
        $result = $this->send($uri);
        $html = $result['data'];
        $pattern = '/<svg[^>]*>(.*?)<\/svg>/s';
        preg_match($pattern, $html, $matches);
        
        if (isset($matches[1])) {
            $svgData = $matches[1];
            // Clean up the SVG data
            $svgData = str_replace(["\n", "\r", "\t"], '', $svgData);
            $svgData = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="400" height="400" viewBox="0 0 400 400">' . $svgData . '</svg>';
            // Output the SVG data
            $result['data'] = $svgData;
        } else {
            echo 'QR code data not found.';
        }
        
        return $result;
    }

    public function check(CheckRequest $request): array
    {
        $data = $request->validated();

        $uri = $this->address . 'api/get_data_by_suz_id?suz_id=' . $data['suz_id'];
        return $this->send($uri);
    }

    public function getAddress(GetAddressRequest $request): array
    {
        $data = $request->validated();
        $suz_request = SuzRequest::with(['town','street','house'])->find($data['suz_id']);
        $address = $suz_request->buildAddress();

        return compact('address');
    }
}
