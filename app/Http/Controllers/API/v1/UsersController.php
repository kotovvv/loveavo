<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Lid;
use App\Models\Log;
use App\Models\Import;
use App\Models\Balans;
use App\Models\Depozit;
use DB;
use Debugbar;
use Hash;
use Storage;
use File;
use Session;

class UsersController extends Controller
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
      return User::select(['users.*'])
        ->when($office_id > 0, function ($query) use ($office_id) {
          return $query->where('office_id', $office_id);
        })
        ->orderBy('office_id')
        ->orderBy('role_id')
        // ->orderBy('group_id')
        ->orderBy('order')
        ->get();
    }
  }

  public function getServers($id)
  {
    $servers = [];
    if (session()->has('office_id')) {
      $servers = User::where('id', $id)->value('servers');
      if (strlen($servers) > 20) {
        $servers = preg_split("/\r\n|\n|\r/", $servers);
        foreach ($servers as $key => $server) {
          $a_server = explode(';', $server);
          $servers[$key] = ['name' => $a_server[0], 'server' => $a_server[1], 'login' => $a_server[2], 'password' => $a_server[3], 'prefix' => $a_server[4]];
        }
      }
    }
    return $servers;
  }

  public function getOffices()
  {
    if (session()->has('office_id')) {
      $office_id = session()->get('office_id');
      $where = $office_id > 0 ? " where `id` = " . $office_id : "";
      $sql = 'SELECT * FROM `offices` ' . $where . ' ORDER BY NAME';
      return DB::select(DB::raw($sql));
    }
  }

  public function updateOffice(Request $request)
  {
    $data = $request->all();
    if (!isset($data['id']) || $data['id'] == 0) {
      $sql = "INSERT into `offices` (`name`,`created_at`,`updated_at`) values ('$data[name]','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "')";
      DB::insert(DB::raw($sql));
    } elseif ($data['id'] > 0) {
      $sql = "UPDATE `offices` set `name` = '$data[name]', `updated_at` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = " . (int) $data['id'];
      DB::update(DB::raw($sql));
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
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
  public function update(Request $request)
  {
    $data = $request->all();

    if (isset($data['password'])) $password = $data['password'] ? Hash::make($data['password']) : Auth::user()->password;
    // $file_name = $data['pic'] == 'null' ? NULL : $data['pic'];
    // if ($data['pic'] != 'null' && is_file($data['pic'])) {
    //   $file_name = pathinfo($data['pic']->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $data['pic']->getClientOriginalExtension();
    //   Storage::disk('public')->put($file_name, File::get($data['pic']));
    // }
    if (isset($data['id']) && $data['id'] > 0) {
      if (isset($data['balans']) && $data['balans'] > 0) {
        $arr = [];
        $arr['user_id'] = $data['id'];
        $arr['balans'] = $data['balans'];
        $arr['date'] = Date('Y-m-d');
        $arr['time'] = Date('h:i:s');
        Balans::insert($arr);
      }

      $arr = [];
      $arr['name'] = $data['name'];
      $arr['active'] = $data['active'] == 'true' || $data['active'] == 1 ? 1 : 0;
      $arr['role_id'] = $data['role_id'];
      $arr['fio'] = $data['fio'];
      // $arr['pic'] = $file_name;
      $arr['group_id'] = $data['group_id'] > 0 ? $data['group_id'] : 0;
      $arr['office_id'] = isset($data['office_id']) ? $data['office_id'] : 0;
      $arr['order'] = $data['order'];
      $arr['sip'] = $data['sip'] == 'true' ? 1 : 0;
      // $arr['sip_server'] = $data['sip_server'] ? $data['sip_server'] : '';
      // $arr['sip_login'] = $data['sip_login'] ? $data['sip_login'] : '';
      // $arr['sip_password'] = $data['sip_password'] ? $data['sip_password'] : '';
      // $arr['sip_prefix'] = $data['sip_prefix'] ? $data['sip_prefix'] : '';
      $arr['servers'] = $data['servers'] ? $data['servers'] : '';
      if (isset($data['user_serv'])) {
        $arr['user_serv'] = $data['user_serv'] ? $data['user_serv'] : '';
      }
      if (isset($data['serv'])) {
        $arr['serv'] = $data['serv'] ? $data['serv'] : '';
      }

      if (User::where('id', $data['id'])->value('office_id') != $data['office_id']) {
        Lid::where('user_id', $data['id'])->update(['office_id' => $data['office_id']]);
      }
      if (User::where('id', $data['id'])->update($arr)) {
        if (isset($data['password'])) {
          $user = User::find($data['id']);
          $user->password = $password;
          $user->save();
        }

        return response('User updated', 200);
      } else return response('User updated error', 301);
    } else {
      $user = new User();
      $user->name = $data["name"];
      $user->fio = $data["fio"];
      $user->role_id = $data["role_id"];
      $user->group_id = $data['group_id'];
      // $user->pic = $file_name;
      // $user->sip_server = $data['sip_server'] ? $data['sip_server'] : '';
      // $user->sip_login = $data['sip_login'] ? $data['sip_login'] : '';
      // $user->sip_password = $data['sip_password'] ? $data['sip_password'] : '';
      // $user->sip_prefix = $data['sip_prefix'] ? $data['sip_prefix'] : '';
      $user->sip = $data['sip'] == 'true' ? 1 : 0;
      $user->servers = $data['servers'] ? $data['servers'] : '';
      $user->office_id = isset($data['office_id']) ? $data['office_id'] : 0;
      $user->password = $password;
      $user->order = $data['order'];
      $user->save();
      return response('User added', 200);
    }
  }

  public function status_users(Request $request)
  {
    $repoprt = [];
    $req = $request->All();
    // Debugbar::info($req);
    $a_users = $req['users'];
    $datefrom = $req['datefrom'];
    $dateto = $req['dateto'];


    foreach ($a_users as $user_id) {
      $sql = "SELECT fio FROM `users` WHERE id = " . $user_id;
    }
    $fio = DB::select(DB::raw($sql));

    foreach ($a_users as $user_id) {
      $sql = "SELECT COUNT(*) n FROM `lids` WHERE `user_id` =  " . $user_id;
    }
    $all = DB::select(DB::raw($sql));

    $current_date = $datefrom;
    // write dates

    $repoprt[] =  ['color' => '', 'col' => ['User', $fio[0]->fio]];
    $repoprt[] = ['color' => '', 'col' => ['Total leads', $all[0]->n]];
    $row_dates = ['color' => '', 'col' => ['Dates']];
    $row_added = ['color' => '', 'col' => ['Added']];
    $row_status = [];
    $count_status = 0;
    while (strtotime($current_date) <= strtotime($dateto)) {
      $row_dates['col'][] = $current_date;
      $sql = "SELECT COUNT(*) n FROM `lids` WHERE `user_id` = " . $a_users[0] . " AND CAST(created_at AS DATE) = '" . $current_date . "'";
      $new = DB::select(DB::raw($sql));
      $row_added['col'][] = $new[0]->n;

      //
      $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
    }

    $repoprt[] = $row_dates;
    $repoprt[] = $row_added;
    // Debugbar::info($new);
    $statuses = DB::select(DB::raw("SELECT id, `name`, color FROM `statuses` WHERE `active` = 1 ORDER BY `order` ASC"));

    foreach ($statuses as $status) {
      $current_date = $datefrom;
      $col = [$status->name];
      $count_status = 0;
      while (strtotime($current_date) <= strtotime($dateto)) {
        $sql = "SELECT COUNT(*) n FROM `logs` WHERE `user_id` = " . $a_users[0] . " AND CAST(created_at AS DATE) = '" . $current_date . "'  AND `status_id` = " . $status->id;
        $sts = DB::select(DB::raw($sql));
        $col[] =  $sts[0]->n;
        $count_status += $sts[0]->n;
        //  Debugbar::info($sql);
        $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
      }
      $col[] = $count_status;
      $repoprt[] = ['color' => $status->color, 'col' => $col];
    }
    return $repoprt;
  }


  public function getusers()
  {
    $office_id = session()->get('office_id');
    $user = User::where('id', (int) session()->get('user_id'))->first();

    // $role_id = $user->role_id;
    $group_id = $user['group_id'];
    return User::select([
      'users.*',
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id) as hmlids '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 8) as statnew '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 9) as cb '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 33) as inp '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 12) as notint '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 7) as noans '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 24) as lang '),
      DB::raw('(SELECT COUNT(*) FROM lids l WHERE l.user_id = users.id AND `status_id` = 11) as trash ')

    ])
      //->where('users.role_id', '>', 1)
      ->where('users.active', 1)
      ->when($office_id > 0, function ($query) use ($office_id) {
        return $query->where('office_id', $office_id);
      })
      ->when($group_id > 0 && $user['role_id'] != 1, function ($query) use ($group_id) {
        return $query->where('group_id', $group_id);
      })
      ->orderBy('users.order', 'asc')
      ->get();
  }

  public function getrelatedusers(Request $request)
  {
    $related_users = $request->All();
    // if (count($related_users) == 0) return false;

    return User::select(['users.*', DB::raw('(SELECT COUNT(user_id) FROM lids WHERE lids.user_id = users.id) as hmlids ')])
      ->where('users.role_id', '>', 1)
      ->where('users.active', 1)
      // ->whereIn('id', $related_users)
      ->orderBy('users.role_id', 'asc')
      ->get();
  }

  public function getroles()
  {
    return Role::All();
  }

  public function deleteuser($id)
  {
    Lid::where('user_id', '=', $id)->delete();
    $group_id = User::where('id', $id)->value('group_id');
    if ($group_id) {
      Log::where('user_id', '=', $id)->update(['user_id' => $group_id]);
    }
    Import::where('user_id', '=', $id)->delete();
    $user = User::find($id);
    if ($user) {
      $user->delete();
    }
  }

  public function delDataUser($id)
  {
    Balans::where('user_id', $id)->delete();
    DB::table('calls')->where('user_id', $id)->delete();
  }

  public function setBalans(Request $request)
  {
    $data = $request->All();
    if (isset($data['id']) && isset($data['balans']) && $data['balans'] > 0) {
      $arr = [];
      $arr['user_id'] = $data['id'];
      $arr['balans'] = $data['balans'];
      $arr['date'] = Date('Y-m-d');
      $arr['time'] = Date('h:i:s');
      Balans::insert($arr);
    }
  }


  public function lastBalans($id)
  {
    // $lastBalans = Balans::select('balans', 'date')->where('user_id', '=', $id)->orderBy('date', 'DESC')->first();
    // return $lastBalans['balans'] . ' (' . $lastBalans['date'] . ')';
  }


  public function getDataDay($user_id)
  {
    //get office
    // SELECT officep_id`,fio FROM `users` WHERE `role_id` = 2 AND `group_id` > 0 AND `id` = `group_id`
    $date = date('Y-m-d');
    $datefrom = date('Y-m-d', strtotime("-30 days"));
    if ($user_id) {
      $getStatuses = Log::select('logs.status_id', 'statuses.name', 'statuses.color')->leftJoin('statuses', 'statuses.id', '=', 'logs.status_id')->where('logs.user_id', $user_id)->where('logs.status_id', '>', 0)->whereDate('logs.created_at', '>=', $date)->orderBy('statuses.order', 'ASC')->get();
      return response($getStatuses);
    }
    $getDataDay =  DB::select(DB::Raw("SELECT
    `id`,
    `fio`,
    (SELECT COUNT(*) FROM `lids`  WHERE `user_id` = u.id) hmlids,
    (SELECT SUM(`count`) FROM `calls` WHERE u.`id` = `user_id` AND CAST(timecall AS DATE) = CURDATE()) hmcalls,
    (SELECT SUM(`duration`) FROM `calls` WHERE u.`id` = `user_id` AND CAST(timecall AS DATE) = CURDATE()) alltime,
    (SELECT SUM(`balans`) FROM `balans` WHERE u.`id` = `user_id` AND `date` = CURDATE()) balans,
    (SELECT SUM(`balans`) FROM `balans` WHERE u.`id` = `user_id` AND `date` >= '" . $datefrom . "' AND `date` <= CURDATE()) mbalans,
    `group_id`,
    `role_id`
    FROM `users` u
    WHERE `active` = 1 AND `role_id` > 1 AND `group_id` > 0 ORDER BY `group_id`,`role_id`"));
    return response($getDataDay);
  }

  public function getBalansMonth($id)
  {
    $dateto = date('Y-m-d');
    $datefrom = date('Y-m-d', strtotime("-30 days"));
    $getBalans = Balans::select('balans', 'date')->where('user_id', '=', $id)->whereDate('date', '>=', $datefrom)->whereDate('date', '<=', $dateto)->orderBy('date', 'ASC')->get();
    return $getBalans;
  }

  public function getStatusesMonth($id)
  {
    $dateto = date('Y-m-d h:i:s');
    $datefrom = date('Y-m-d h:i:s', strtotime("-30 days"));
    $getStatuses = Log::select('logs.status_id', 'statuses.name', 'statuses.color', DB::Raw('CAST(logs.created_at as date) as date'))->leftJoin('statuses', 'statuses.id', '=', 'logs.status_id')->where('logs.user_id', $id)->where('logs.status_id', '>', 0)->whereDate('logs.created_at', '>=', $datefrom)->whereDate('logs.created_at', '<=', $dateto)->orderBy('statuses.order', 'ASC')->get();
    return $getStatuses;
  }

  public function getDepozitsMonth($id)
  {
    $dateto = date('Y-m-d');
    $datefrom = date('Y-m-d h:i:s', strtotime("-30 days"));
    $getDeposits = Depozit::where('user_id', $id)->whereDate('created_at', '>=', $datefrom)->whereDate('created_at', '<=', $dateto)->get();
    return $getDeposits;
  }
  public function getCallsMonth($id)
  {
    $respons = [];
    $dateto = date('Y-m-d h:i:s');
    $datefrom = date('Y-m-d h:i:s', strtotime("-30 days"));
    $respons['callmonth'] = DB::select(DB::Raw("SELECT SUM(COUNT) as count,SUM(duration) as duration FROM `calls` WHERE user_id = $id AND timecall >= '$datefrom' AND timecall <= '$dateto'"));
    $respons['callday'] = DB::select(DB::Raw("SELECT SUM(COUNT)as count ,SUM(duration) as duration FROM `calls` WHERE user_id = $id AND CAST(timecall as date) = CURDATE()"));
    return $respons;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
