<?php

use App\Enums\DayTimeEnum;
use App\Models\MaterialLimitStatistic;
use App\Models\MaterialStory;
use App\Models\RequestRouteList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $enc = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) .
            mb_substr($string, 1, mb_strlen($string, $enc), $enc);
    }
}

if (!function_exists('validateDate')) {
    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status)
    {
        $class = 'default';
        switch ($status) {
            case 'Новый':
                $class = 'primary';
                break;
            case 'Выполнено':
                $class = 'success';
                break;
            case 'Назначено':
                $class = 'warning';
                break;
            case 'Отложено':
                $class = 'info';
                break;
            case 'Отменено':
                $class = 'cancel';
                break;
        }
        return $class;
    }
}

if (!function_exists('convertToPlanTime')) {
    /**
     * @param string $time Time in string, e.g. 8:30
     * @return integer $plan_time
     */
    function convertToPlanTime($time)
    {
        if (date("H:i", strtotime($time)) < date("H:i", strtotime("13:00"))) {
            $plan_time = 0;
        } else {
            $plan_time = 1;
        }
        return $plan_time;
    }
}

if (!function_exists('reverseInteger')) {
    function reverseInteger($num)
    {
        $revnum = 0;
        while ($num > 1) {
            $rem = $num % 10;
            $revnum = ($revnum * 10) + $rem;
            $num = ($num / 10);
        }
        return (int)$revnum;
    }
}


if (!function_exists('cyr2lat')) {
    function cyr2lat($string)
    {
        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];
        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];
        $string = str_replace($cyr, $lat, $string);
        return $string;
    }
}

if (!function_exists('yesterday')) {
    function yesterday(): Carbon
    {
        return Carbon::yesterday();
    }
}

if (!function_exists('checkBinding')) {
    function checkBinding(array $data): array
    {
        $result = [];

        if (isset($data['clientMaterial'])) {
            foreach ($data['clientMaterial'] as $material) {
                $materialStoryCount = MaterialStory::where('request_id', $data['request_id'])
                    ->where('material_id', $material->material_id)
                    ->count();

                $result[] = [
                    'id' => $material->id,
                    'bindedMaterialAmount' => $materialStoryCount
                ];
            }
        }

        return $result;
    }
}

if (!function_exists('setPlanDate')) {
    function setPlanDate($req)
    {
        $request_route_list_row = RequestRouteList::select("time")->where("request_id", $req->id)->first();
        $time = '';
        if ($request_route_list_row) {
            $time = $request_route_list_row->time;
        }
        if ($time != '' && $time != '0000-00-00 00:00:00') {
            $req->dt_plan_date = $time;
        } else {
            $req->dt_plan_date .= " " . DayTimeEnum::description($req->n_plan_time);
        }

        return $req->dt_plan_date;
    }
}

if (!function_exists('setPlanDayTime')) {
    function setPlanDayTime($req): string
    {
        $req->n_plan_time = DayTimeEnum::description($req->n_plan_time);
        return $req->n_plan_time;
    }
}

if (!function_exists('getStatisticData')) {
    function getStatisticData(): array
    {
        $material_limits = MaterialLimitStatistic::with('material')
            ->whereDate('created_at', yesterday())
            ->get()
            ->groupBy('request_id');

        $statisticData = [];

        foreach ($material_limits as $key => $limits) {
            foreach ($limits as $limit) {
                $statisticData[$key][$limit->material->name][] = [
                    'limits' => $limit,
                ];
            }
            $statisticData[$key]['titles'] = join(',', array_keys($statisticData[$key]));
        }

        return $statisticData;
    }
}
function getModelOwner($model)
{
    $owner = null;
    if ($model->owner_id) {
        $user = User::find($model->owner_id);
        if ($user) {
            $owner = $user;
        } else {
            $owner = $model->owner_id;
        }
    }
    return $owner;
}

/**
 * Decode JSON value if it is set, otherwise return null.
 *
 * @param mixed $value
 * @return mixed|null
 */
function decodeJson($value)
{
    return isset($value) ? json_decode($value) : null;
}

function decodeJsonOrNull($value)
{
    return ($value && $value !== '') ? json_decode($value) : null;
}

/**
 * Check if either equipments or kits are present and have a count greater than 0.
 *
 * @param mixed $equipments
 * @param mixed $kits
 * @return bool
 */
function shouldProcessData($equipments, $kits): bool
{
    return ((isset($equipments) && count($equipments) > 0) || (isset($kits) && count($kits) > 0));
}


/**
 * Update user and client materials.
 *
 * @param int $materialId
 * @param int $installerId
 * @param int $quantity
 * @param string $contract
 * @return void
 */
function updateMaterials(int $materialId, int $installerId, int $quantity, string $contract)
{
    $inStory = DB::table('materials_story')
        ->where('material_id', $materialId)
        ->where('owner_id', $contract)
        ->where('qty', $quantity)
        ->get();

    if (!empty($inStory)) {
        DB::table('user_material')->where('material_id', $materialId)->where('user_id', $installerId)->decrement('qty', $quantity);
        $row = DB::table('client_material')->where('material_id', $materialId)->where('contract', $contract)->first();

        if ($row) {
            DB::table('client_material')->where('material_id', $materialId)->where('contract', $contract)->increment('qty', $quantity);
        } else {
            DB::table('client_material')->insert([
                'material_id' => $materialId,
                'contract' => $contract,
                'qty' => $quantity
            ]);
        }
    }
}

function insertMaterialStory($material, $materials_qty, $installer, $request_id, $suzRequest, $key, $type = 1): bool // 1 - выдаем, 2 - забираем
{
    $inStory = DB::table('materials_story')
        ->where('material_id', $material)
        ->where('owner_id', $suzRequest->v_contract)
        ->where('qty', $materials_qty[$key])
        ->get();

    if (empty($inStory)) return false;

    return DB::table('materials_story')->insert([
        'material_id' => $material,
        'owner_id' => $type === 1 ? $suzRequest->v_contract : $installer[$key],
        'author_id' => $installer[$key],
        'qty' => $materials_qty[$key],
        'request_id' => $request_id,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'id_flow' => $suzRequest->id_flow,
        'from' => $type === 1 ? $installer[$key] : $suzRequest->v_contract
    ]);
}


function generateRandomCaptcha($length = 6): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha = '';

    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $captcha;
}
