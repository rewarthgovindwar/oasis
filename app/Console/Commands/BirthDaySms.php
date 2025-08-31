<?php

namespace App\Console\Commands;

use App\SmStaff;
use App\SmStudent;
use Illuminate\Console\Command;

class BirthDaySms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:birthdaysms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle(): bool
    {
        date_default_timezone_set(timeZone());
        $currentDate = date('-m-d');

        $allStudents = SmStudent::where('date_of_birth', 'like', '%'.$currentDate)->get();
        foreach ($allStudents as $allStudent) {
            $compact['user_email'] = $allStudent->email;
            @send_sms($allStudent->mobile, 'student_birthday', $compact);
        }

        $allStaffs = SmStaff::where('date_of_birth', 'like', '%'.$currentDate)->get();
        foreach ($allStaffs as $allStaff) {
            $compact['user_email'] = $allStaff->email;
            @send_sms($allStaff->mobile, 'staff_birthday', $compact);
        }

        return true;
    }
}
