<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lid;
use App\Models\User;
use Storage;
use DB;


//$ php artisan loadcalls:two
//$ php artisan schedule:list
//* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

class Load_calls extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'loadcalls:two';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Parse files with calls and write in table calls on phone namber';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $directory = 'copy';
    $files = Storage::disk('public')->allFiles($directory);
    // $files = Storage::disk('public')->files($directory);
    $curdate = date('Y-m-d');
    foreach ($files as  $file) {
      if (!file_exists(Storage::disk('public')->get($file))) continue;
      $user_serv = explode('/', $file)[2];
      $serv = explode('/', $file)[1];
      $data = [];
      $a_row = $this->parseIni($file);
      foreach ($a_row as $row) {
        // $row[0] - tel
        // $row[3] - date
        // $row[4] - sec
        // $row[5] - status
        if (!is_array($row)) continue;
        if (!preg_match('/^[0-9]+$/', $row[0])) continue;
        if ($curdate != date('Y-m-d', $row[3])) continue; //only today
        // $a_lid =  $this->getLeadOnTel($row[0], $row[3]);
        $user = User::where(['serv' => $serv, 'user_serv' => $user_serv, 'active' => 1])->first();
        if (!$user) {
          continue;
        }

        $data['user_id'] = $user['id'];
        $data['office_id'] = $user['office_id'];

        $data['tel'] = $row[0];
        $data['timecall'] = date('Y-m-d H:i:s', $row[3]);
        $data['duration'] = $row[4];
        $data['status'] = $row[5];
        try {
          DB::table('calls')->updateOrInsert($data);
        } catch (\Throwable $th) {
          return 0;
        }
      }
      //return $a_row;
      Storage::disk('public')->delete($file);
    }
    return 1;
  }

  protected function parseIni($filename)
  {
    // $file = file_get_contents($filename);
    $file = Storage::disk('public')->get($filename);
    // $file = Storage::get($filename);

    $file = mb_convert_encoding($file, "UTF-8", "UTF-16LE");
    $lines = explode("\n", $file);
    $rows = [];
    $read = false;
    if (!empty($lines)) {
      // If lines exists
      foreach ($lines as $line) {
        // Skipping the empty line and Comment line
        if ((empty(trim($line))) || (preg_match('/^#/', $line) > 0))
          continue;

        // Output Line Content
        $line = trim($line);
        if (strpos($line, "Calls") === 1) {
          $read = true;
          continue;
        }
        if (strpos($line, "[") === 0) {
          $read = false;
          continue;
        }
        if ($read === true) {
          $a1 = explode('=', $line);
          $a2 = explode(';', $a1[1]);

          // print_r($a2);
          try {
            $a2[3];
          } catch (\Throwable $th) {
            continue;
          }
          // $da = date('Y-m-d H:i:s', $a2[3]);
          // echo "Тел:{$a2[0]} time:{$da} sek:{$a2[4]} status:{$a2[5]}<br>\n";
          $rows[] = $a2;
        }
      }
    }
    return $rows;
  }
  protected function getLeadOnTel($tel, $datecall)
  {

    $lid = Lid::select('id', 'tel', 'user_id', 'updated_at', 'office_id')->where('tel', $tel)->where('created_at', '<', date('Y-m-d H:i:s', $datecall))->orderBy('created_at', 'DESC')->get()->toArray();
    // if ($lid) {
    //   print ($lid[0]['tel'] . ' ' . date('Y-m-d H:i:s', strtotime($lid[0]['updated_at'])) . ' ' . $lid[0]['user_id']) . '<br>';
    // }
    return $lid;
  }
}
