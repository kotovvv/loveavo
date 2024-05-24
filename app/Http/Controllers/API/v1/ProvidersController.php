<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Hash;
use App\Models\Lid;
use App\Models\Log;
use App\Models\Import;


class ProvidersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    if (session()->has('office_id')) {
      $office_id = session()->get('office_id');

      return Provider::when($office_id > 0, function ($query) use ($office_id) {
        return $query->where('office_id', 'REGEXP', '[^0-9]' . $office_id . '[^0-9]');
      })->orderBy('name', 'ASC')->get();
    }
  }

  public function getall()
  {
    // return Provider::all();
    if (session()->has('office_id')) {
      $office_id = session()->get('office_id');

      return Provider::when($office_id > 0, function ($query) use ($office_id) {
        return $query->where('office_id', 'REGEXP', '[^0-9]' . $office_id . '[^0-9]');
      })->get();
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function status_provider(Request $request)
  {
    // $request->validate([
    //     'provider_id' => 'required',
    // ]);
    $req = $request->all();
    $provider_id = $req['provider_id'];
    $datefrom = $req['datefrom'];
    $dateto = $req['dateto'];
    $cur_date = date('Y-m-d');
    $return = [];
    if (isset($provider_id)) {

      $sql = "SELECT DISTINCT CAST(`start` AS DATE) 'start' FROM `imports` WHERE `provider_id` = " . $provider_id . " AND (CAST(`start` AS DATE) BETWEEN '" . $datefrom . "' AND '" . $dateto . "')";
      $a_dateadd = DB::select(DB::raw($sql));
      $sql = "SELECT COUNT(id) n FROM `lids` WHERE `provider_id` = '" . $provider_id . "'";
      $all_lids = DB::select(DB::raw($sql));
      $return['all'] = $all_lids;
      if ($a_dateadd) {

        foreach ($a_dateadd as $dateadd) {
          $sql = "SELECT s.`color`,s.`name`, COUNT(`status_id`) n FROM `logs` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE `status_id` > 0 AND `lid_id` IN (SELECT id FROM `lids` WHERE `provider_id` = " . $provider_id . " AND CAST(`created_at` AS DATE ) = '" . $dateadd->start . "') GROUP BY `status_id` ORDER BY s.`order` ASC ";

          $a_statuses = DB::select(DB::raw($sql));
          $return['allstatuses'][] = ['date' => $dateadd->start, 'statuses' => $a_statuses];
        }

        //$sql = "select *,count(*) n from (SELECT DISTINCT  s.`color`,s.`name`,tel FROM `logs` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE `status_id` > 0 AND CAST(l.`created_at` AS DATE ) BETWEEN '" . $datefrom . "' AND '" . $dateto . "' AND `lid_id` IN (SELECT id FROM `lids` WHERE `provider_id` = ". $provider_id . " )  ORDER BY s.`order` ASC ) t1 group by name";
        $sql = "SELECT *,COUNT(*) n FROM (SELECT * FROM (SELECT DISTINCT  s.`color`,s.`name`,tel FROM `logs` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE `status_id` > 0 AND CAST(l.`created_at` AS DATE )  BETWEEN '" . $datefrom . "' AND '" . $dateto . "' AND `lid_id` IN (SELECT id FROM `lids` WHERE `provider_id` = " . $provider_id . " ) ORDER BY l.`created_at` DESC) t1 GROUP BY t1.tel) t2 GROUP BY t2.name";


        $a_statuses = DB::select(DB::raw($sql));
        $return['allstatuses'][] = ['date' => 'result', 'statuses' => $a_statuses];
      }
    }

    return $return;
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|max:255',
    ]);

    $data = $request->all();
    $data['related_users_id'] = json_encode($data['related_users_id']);
    $data['office_id'] = json_encode($data['office_id']);

    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    if (isset($data['id']) && $data['id'] > 0) {

      unset($data['created_at']);
      $data['updated_at'] = Now();
      if (Provider::where('id', $data['id'])->update($data)) {
      }
    } else {
      $provider = Provider::create($data);
      $provider->save();
    }
    $name_key = Provider::select('id', 'name', 'tel')->get();
    $sql = 'TRUNCATE TABLE `apikeys`';
    DB::select(DB::raw($sql));
    $sql = "INSERT INTO `apikeys` (`id`,`name`,`api_key`) VALUES ";
    $i = 0;
    foreach ($name_key as $key) {
      $z = $i == 0 ? '' : ',';
      $sql .= $z . "('" . $key['id'] . "','" . $key['name'] . "','" . $key['tel'] . "')";
      $i++;
    }
    DB::select(DB::raw($sql));
    if (!isset($data['id'])) {
      return response()->json([
        "status" => true,
        "provider" => $provider
      ])->setStatusCode(200);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $provider = Provider::find($id);
    if (!$provider) {
      return response()->json([
        "status" => false,
        "message" => "provider not found"
      ])->setStatusCode(404);
    }

    return $provider;
  }

  // Get all lids for provider
  public function pieAll($id)
  {
    $sql = "SELECT `status_id`,s.`name`,s.`color`,COUNT(`status_id`) hm FROM `lids` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE `provider_id` = '" . (int) $id . "' GROUP BY `status_id` ORDER BY s.order ASC";
    $providerAllLids = DB::select(DB::raw($sql));

    $labels = [];
    $backgroundColor = [];
    $data = [];
    foreach ($providerAllLids as $row) {
      $labels[] = $row->name;
      $backgroundColor[] = $row->color;
      $data[] = $row->hm;
    }

    $sql = "SELECT CAST(MIN(`created_at`) AS DATE) start_date FROM `lids` WHERE `provider_id` = " . (int) $id;
    $res = DB::select(DB::raw($sql));
    $start_date = $res[0]->start_date;

    return response()->json([
      "status" => 'ok',
      "statuses" => $providerAllLids,
      "labels" => $labels,
      "backgroundColor" => $backgroundColor,
      "data" => $data,
      "start_date" => $start_date
    ])->setStatusCode(200);
  }



  // Get lids for time provider
  public function pieTime($id, $start_day, $stop_day)
  {
    $sql = "SELECT `status_id`,s.`name`,s.`color`,COUNT(`status_id`) hm FROM `lids` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE l.status_id != 10 AND `provider_id` = " . (int) $id . " AND CAST(l.`created_at` AS DATE) BETWEEN '" . $start_day . "' AND '" . $stop_day . "' GROUP BY `status_id` ORDER BY s.order ASC";
    $providerTimeLidsNoDep = DB::select(DB::raw($sql));
    $sql = "SELECT `status_id`,s.`name`,s.`color`,COUNT(`status_id`) hm FROM `lids` l RIGHT JOIN `depozits` d ON (d.`lid_id` = l.`id`) LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE l.status_id = 10 AND `provider_id` = " . (int) $id . " AND CAST(l.`created_at` AS DATE) BETWEEN '" . $start_day . "' AND '" . $stop_day . "' GROUP BY `status_id` ORDER BY s.order ASC";
    $providerTimeLidsDep = DB::select(DB::raw($sql));

    $providerTimeLids = array_merge($providerTimeLidsNoDep, $providerTimeLidsDep);

    $labels = [];
    $backgroundColor = [];
    $data = [];
    foreach ($providerTimeLids as $row) {
      $labels[] = $row->name;
      $backgroundColor[] = $row->color;
      $data[] = $row->hm;
    }

    return response()->json([
      "status" => 'ok',
      "statuses" => $providerTimeLids,
      "labels" => $labels,
      "backgroundColor" => $backgroundColor,
      "data" => $data,
    ])->setStatusCode(200);
  }

  // Get lids for time provider
  public function getDataTime($id, $start_day, $stop_day)
  {
    $sql = "SELECT l.id, l.`name`, l.`tel`, l.`email`, cast(l.`created_at` as date) created_at, cast(l.`updated_at` as date) updated_at, l.`status_id`, s.`name` status_name, s.`color` FROM `lids` l LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE l.`status_id` != 10 AND `provider_id` = " . (int) $id . " AND CAST(l.`created_at` AS DATE) BETWEEN '" . $start_day . "' AND '" . $stop_day . "'  ORDER BY s.order ASC";
    $providerTimeLids = DB::select(DB::raw($sql));

    $sql = "SELECT l.id, l.`name`, l.`tel`, l.`email`, cast(l.`created_at` as date) created_at, cast(l.`updated_at` as date) updated_at, l.`status_id`, s.`name` status_name, s.`color` FROM `lids` l RIGHT JOIN `depozits` d ON (d.`lid_id` = l.`id`) LEFT JOIN `statuses` s ON (s.`id` = l.`status_id`) WHERE l.`status_id` = 10 AND `provider_id` = " . (int) $id . " AND CAST(l.`created_at` AS DATE) BETWEEN '" . $start_day . "' AND '" . $stop_day . "'  ORDER BY s.order ASC";
    $providerDepozitTimeLids = DB::select(DB::raw($sql));

    $providerTimeLids = array_merge($providerTimeLids, $providerDepozitTimeLids);
    return response()->json([
      "status" => 'ok',
      "data" => $providerTimeLids,
    ])->setStatusCode(200);
  }

  public function historyLid($id)
  {
    $sql = "SELECT l.id, CAST(l.`created_at` AS DATE) date, s.`name` status,s.`color` color FROM `logs` l LEFT JOIN `statuses` s ON (l.`status_id` = s.`id`) WHERE `lid_id` = " . (int) $id . " AND s.`name` IS NOT NULL ORDER BY DATE ASC";
    $history = DB::select(DB::raw($sql));

    return response()->json([
      "status" => 'ok',
      "history" => $history
    ])->setStatusCode(200);
  }



  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    Provider::where('id', $id)->update(['active' => 0]);
    // Provider::where('id', $id)->delete();
    // $tels =  Lid::select('tel')->where('provider_id', '=', $id);
    // Log::whereIn('tel', $tels)->delete();
    // Lid::where('provider_id', '=', $id)->delete();
    // Import::where('provider_id', '=', $id)->delete();
  }
}