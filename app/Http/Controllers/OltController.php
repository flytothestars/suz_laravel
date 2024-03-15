<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use Error;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OltController extends Controller
{
    public function getLogin(Request $request, $id_house)
    {
        $osns = $request->json()->all();
        $osn = null;

        foreach ($osns as $key => $value) {
            $equipment = Kit::where('v_serial', $value[0])->first()->equipments;
            $type = $equipment->first()->type;
            $typeName = $type['v_name'];

            if ($typeName == 'ONT') {
                $osn = $value[0];
                break;
            }
        }

        if (!$osn) return response(['error' => 'Нет ни одного ont!'], 400);

        $client = new Client();

        try {
            Log::channel('jphone')->info($id_house . ' - ' . $osn);

            $response = $client->request('GET', env('OLT_SERVICE')."api/getLogin/{$id_house}/{$osn}");
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();

            Log::channel('jphone')->info([json_decode($content)]);
            // Process the response data as needed
        } catch (RequestException $e) {
            // Handle any exceptions or errors that occurred during the request
            Log::error('guzzle exception', ['data' => $e->getTraceAsString()]);
            $statusCode = $e->getCode();
            $content = $e->getMessage();
        }

        return response(['response' => $content], $statusCode);
    }

    public function checkOltsPage(Request $request)
    {
        return view('check-olts.index');
    }
    public function checkOlts(Request $request) {

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $fileData = Excel::toArray([], $file);

            $client = new Client();

            $data = [];

            foreach ($fileData[0] as $k => $v) {
                if(isset($v[0]) && isset($v[1])) {
                    $data[$v[0]] = $v[1];
                }
            }

            $request = [
                'form_params' => $data,
            ];

            try {
                $response = $client->request('POST', env('OLT_SERVICE') . 'api/checkOlts', $request);
                $content = $response->getBody()->getContents();

                $data = json_decode($content);

                return view('check-olts.index', compact('data'));

            } catch (RequestException $e) {
                // Handle any exceptions or errors that occurred during the request
                Log::error('guzzle exception', ['data' => $e->getTraceAsString()]);
            }
        }

        return view('check-olts.index')->with('Файл не был загружен.');
    }
}
