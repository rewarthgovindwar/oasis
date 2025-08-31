<?php

namespace App\Console\Commands;

use App\SmSchool;
use Illuminate\Console\Command;

class PaymentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'payment:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
    public function handle(): ?bool
    {
        $schools = SmSchool::all();
        if (moduleStatusCheck('University')) {
            foreach ($schools as $school) {
                paymentRemainder($school->id);
            }
        } elseif (directFees()) {
            foreach ($schools as $school) {
                smPaymentRemainder($school->id);
            }
        } else {
            return null;
        }

        return true;
    }
}
