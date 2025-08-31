<?php
namespace App\PaymentGateway;

use App\User;
use Exception;
use App\SmAddIncome;
use App\SmFeesAssign;
use App\SmFeesPayment;
use App\SmPaymentMethhod;
use App\SmPaymentGatewaySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\Lms\Entities\CoursePurchaseLog;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\Fees\Http\Controllers\FeesExtendedController;

class SslCommerz {
    
    public function config()
    {
       $ssl =  SmPaymentGatewaySetting::where('gateway_name', '=', 'SslCommerz')
                                ->where('school_id',auth()->user()->school_id)
                                ->select('ssl_store_name', 'ssl_store_id','ssl_store_password','ssl_environment')
                                ->first();
        return [
            'store_name' => !empty($ssl) ? $ssl->ssl_store_name:'',
            'store_id' => !empty($ssl) ? $ssl->ssl_store_id:'',
            'store_password' => !empty($ssl) ? $ssl->ssl_store_password:'',
            'environment' => !empty($ssl) ? $ssl->ssl_environment:'',
        ];
    }

    public function handle($data)
    {
       try{
            $config  = $this->config();
            $url = $this->paymentUrl($config['environment']);

            if(isset($data['payment_type']) && $data['payment_type'] == 'Lms')
            {
                $payment = $this->lmsPaymentData($config, $data);
                $response = $this->process($url, $payment);
                return $response['url'];
            }else{

                if($data['type'] == 'old_fees'){
                    $payment = $this->studentOldFeesPaymentData($config, $data);
                    $response = $this->process($url, $payment);
                    return $response;
                }else{
                    $payment = $this->getPaymentData($config, $data);
                    $response = $this->process($url, $payment);
                    return $response;
                }                
            }            
       }catch(Exception $e){          
          return false;
       }
    }

    private function process($url, $data)
    {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url );
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1 );
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC


        $content = curl_exec($handle );

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if($code == 200 && !( curl_errno($handle))) {
            curl_close( $handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close( $handle);
            echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
            exit;
        }

        # PARSE THE JSON RESPONSE
        $sslcz = json_decode($sslcommerzResponse, true );
        
        if(isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL']!="" ) {
            return [
                "success" => true,
                "url" => $sslcz['GatewayPageURL'],
            ];
        } else {
           return false;
        }
    }


    private function studentOldFeesPaymentData($config, $data)
    {
        $userInfo = $this->userInfo($data['user_id']);   
        if($userInfo->role_id == 3)
        {
            $cus_name = $userInfo->parent->guardians_name;
            $cus_phone = !empty($userInfo->parent->guardians_mobile) ? $userInfo->parent->guardians_mobile:$userInfo->parent?->fathers_mobile;
            $cus_email = $userInfo->parent->guardians_email;
            $cus_address = !empty($userInfo->parent->guardians_address) ? $userInfo->parent->guardians_address:"Dhaka";
        }else{
            $cus_name = $userInfo->student->full_name;
            $cus_phone = $userInfo->student->mobile;
            $cus_email = $userInfo->student->email;
            $cus_address = $userInfo->student->current_address;
        }
        $post_data = array();
        $txd = isset($data['payment_id']) ? $data['payment_id']:$data['payment_id'];
        $post_data['store_id'] = $config['store_id'];
        $post_data['store_passwd'] = $config['store_password'];
        $post_data['total_amount'] = $data['amount'];
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = $data['type']."_".$txd.'_'.uniqid();
        $post_data['success_url'] = route('ssl.success');
        $post_data['fail_url'] = route('ssl.failed');
        $post_data['cancel_url'] = route('ssl.failed');
        
        # EMI INFO
        $post_data['emi_option'] = "0";
        $post_data['emi_max_inst_option'] = "0";
        $post_data['emi_selected_inst'] = "0";

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $cus_name;
        $post_data['cus_email'] = $cus_email;
        $post_data['cus_add1'] = $cus_address;
        $post_data['cus_city'] = $cus_address;
        $post_data['cus_state'] = $cus_address;
        $post_data['cus_phone'] = $cus_phone;
        $post_data['cus_country'] = "Bangladesh";

        $post_data['shipping_method'] = 'NO';
        

        # OPTIONAL PARAMETERS
        $post_data['value_a']  =  $data['payment_id'];
        $post_data['value_b']  =  $data['assign_id'];
        $post_data['value_c']  =  $data['user_id'];
        $post_data['value_d']  =  $data['type'];

        # CART PARAMETERS
        $post_data['cart'] = json_encode(array(
            array("product"=> $data['type'].' Course Purchase',"amount"=> $data['amount'])
        ));
        $post_data['product_name'] = "Course ".$data['type'];
        $post_data['product_category'] = "top up";
        $post_data['product_profile'] = 'non-physical-goods';
        $post_data['product_amount'] = $data['amount'];
        $post_data['vat'] = "0";
        $post_data['discount_amount'] = "0";
        $post_data['convenience_fee'] = "0";
        return $post_data;
    }

    private function lmsPaymentData($config, $data)
    {
        $userInfo = $this->userInfo($data['user_id']);   
        if($userInfo->role_id == 3)
        {
            $cus_name = $userInfo->parent->guardians_name;
            $cus_phone = !empty($userInfo->parent->guardians_mobile) ? $userInfo->parent->guardians_mobile:$userInfo->parent?->fathers_mobile;
            $cus_email = $userInfo->parent->guardians_email;
            $cus_address = !empty($userInfo->parent->guardians_address) ? $userInfo->parent->guardians_address:"Dhaka";
        }else{
            $cus_name = $userInfo->student->full_name;
            $cus_phone = $userInfo->student->mobile;
            $cus_email = $userInfo->student->email;
            $cus_address = $userInfo->student->current_address;
        }
        $post_data = array();
        $txd = isset($data['purchase_log_id']) ? $data['purchase_log_id']:$data['purchase_log_id'];
        $post_data['store_id'] = $config['store_id'];
        $post_data['store_passwd'] = $config['store_password'];
        $post_data['total_amount'] = $data['amount'];
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = $data['type']."_".$txd.'_'.uniqid();
        $post_data['success_url'] = route('ssl.success');
        $post_data['fail_url'] = route('ssl.failed');
        $post_data['cancel_url'] = route('ssl.failed');
        
        # EMI INFO
        $post_data['emi_option'] = "0";
        $post_data['emi_max_inst_option'] = "0";
        $post_data['emi_selected_inst'] = "0";

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $cus_name;
        $post_data['cus_email'] = $cus_email;
        $post_data['cus_add1'] = $cus_address;
        $post_data['cus_city'] = $cus_address;
        $post_data['cus_state'] = $cus_address;
        $post_data['cus_phone'] = $cus_phone;
        $post_data['cus_country'] = "Bangladesh";

        $post_data['shipping_method'] = 'NO';
        

        # OPTIONAL PARAMETERS
        $post_data['value_a']  =  $data['purchase_log_id'];
        $post_data['value_b']  =  $data['student_id'];
        $post_data['value_c']  =  $data['user_id'];
        $post_data['value_d']  =  $data['type'];

        # CART PARAMETERS
        $post_data['cart'] = json_encode(array(
            array("product"=> $data['type'].' Course Purchase',"amount"=> $data['amount'])
        ));
        $post_data['product_name'] = "Course ".$data['type'];
        $post_data['product_category'] = "top up";
        $post_data['product_profile'] = 'non-physical-goods';
        $post_data['product_amount'] = $data['amount'];
        $post_data['vat'] = "0";
        $post_data['discount_amount'] = "0";
        $post_data['convenience_fee'] = "0";
        return $post_data;
    }


    
    public function getPaymentData($config, $data)
    {
        $userInfo = $this->userInfo($data['user_id']);   
        if($userInfo->role_id == 3)
        {
            $cus_name = $userInfo->parent->guardians_name;
            $cus_phone = !empty($userInfo->parent->guardians_mobile) ? $userInfo->parent->guardians_mobile:$userInfo->parent?->fathers_mobile;
            $cus_email = $userInfo->parent->guardians_email;
            $cus_address = !empty($userInfo->parent->guardians_address) ? $userInfo->parent->guardians_address:"Dhaka";
        }else{
            $cus_name = $userInfo->student->full_name;
            $cus_phone = $userInfo->student->mobile;
            $cus_email = $userInfo->student->email;
            $cus_address = $userInfo->student->current_address;
        }
        $post_data = array();
        $txd = isset($data['transcationId']) ? $data['transcationId']:$data['invoice_id'];
        $post_data['store_id'] = $config['store_id'];
        $post_data['store_passwd'] = $config['store_password'];
        $post_data['total_amount'] = $data['amount'];
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = $data['type']."_".$txd.'_'.uniqid();
        $post_data['success_url'] = route('ssl.success');
        $post_data['fail_url'] = route('ssl.failed');
        $post_data['cancel_url'] = route('ssl.failed');
        
        # EMI INFO
        $post_data['emi_option'] = "0";
        $post_data['emi_max_inst_option'] = "0";
        $post_data['emi_selected_inst'] = "0";

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $cus_name;
        $post_data['cus_email'] = $cus_email;
        $post_data['cus_add1'] = $cus_address;
        $post_data['cus_city'] = $cus_address;
        $post_data['cus_state'] = $cus_address;
        $post_data['cus_phone'] = $cus_phone;
        $post_data['cus_country'] = "Bangladesh";

        $post_data['shipping_method'] = 'NO';
        

        # OPTIONAL PARAMETERS
        $post_data['value_a']  =  isset($data['transcationId']) ? $data['transcationId']:$data['invoice_id'];
        $post_data['value_b']  =  $data['invoice_id'];
        $post_data['value_c']  =  $data['user_id'];
        $post_data['value_d']  =  $data['type'];

        # CART PARAMETERS
        $post_data['cart'] = json_encode(array(
            array("product"=> $data['type'].' Payment',"amount"=> $data['amount'])
        ));
        $post_data['product_name'] = "Student ".$data['type'];
        $post_data['product_category'] = "top up";
        $post_data['product_profile'] = 'non-physical-goods';
        $post_data['product_amount'] = $data['amount'];
        $post_data['vat'] = "0";
        $post_data['discount_amount'] = "0";
        $post_data['convenience_fee'] = "0";
        return $post_data;
    }

    private function userInfo($user_id)
    {
        $user =  User::with(['student'])->where('id',$user_id)->first();   
        return $user;
    }

    
    public function successCallBack($data)
    {
        
        $config = $this->config();
        $url = $this->validateUrl($config['environment']);
        $response = $this->validationProcess($url,$data,$config);
        if($response->status == 'VALID'){
            if($response->value_d == 'Fees') {
                $payment_id =  $response->value_a;
                if($payment_id ){
                    $extendedController = new FeesExtendedController();
                    $extendedController->addFeesAmount($payment_id, null);
                }                 
                return [
                    "success" => true,
                    "url" => route('fees.fees-invoice-view',[$response->value_b,'view'])
                ];           

            }elseif($response->value_d == 'diposit'){
                $user = User::find($response->value_c);
                $currentBalance = $user->wallet_balance;
                $user->wallet_balance = $currentBalance + $response->amount;
                $user->update();

                $addPayment = WalletTransaction::find($response->value_b);
                $addPayment->status = 'approve';
                $addPayment->update();

                $gs = generalSetting();
                $compact['full_name'] =  $user->full_name;
                $compact['method'] =  'SslCommerz';
                $compact['create_date'] =  date('Y-m-d');
                $compact['school_name'] =  $gs->school_name;
                $compact['current_balance'] =  $user->wallet_balance;
                $compact['add_balance'] =  $response->amount;
                @send_mail($user->email, $user->full_name, "wallet_approve", $compact);           
                return [
                    "success" => true,
                    "url" => route('wallet.my-wallet')
                ];
            }elseif($response->value_d == 'Lms'){
                $coursePurchase = CoursePurchaseLog::find($response->value_a);
                $coursePurchase->active_status= 'approve';
                $coursePurchase->save();
                @lmsProfit($coursePurchase->instructor_id, $coursePurchase->amount);
                @addIncome("SslCommerz", 'Lms Fees Collect', $response->amount, $response->value_a, Auth()->user()->id);
                return [
                    "success" => true,
                    "url" => url('lms/student/purchase-log',$coursePurchase->student_id)
                ];
            }elseif($response->value_d == 'old_fees'){
                
                $payment_id =  $data['value_a'];
                $assign_id=  $data['value_b'];
                $user_id=  $data['value_c'];
                $user = $this->userInfo($user_id);
                
                $fees_assign = SmFeesAssign::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($assign_id);
                $payment_method = SmPaymentMethhod::withoutGlobalScope(ActiveStatusSchoolScope::class)->where('method',"SslCommerz")
                                                                                                      ->where('school_id', $fees_assign->school_id)
                                                                                                      ->first();
                $fees_payment = SmFeesPayment::find($payment_id);
                    $fees_payment->active_status = 1;
                    $fees_payment->save();
                    $income_head = generalSetting();
               
                    $add_income = new SmAddIncome();
                    $add_income->name = 'Fees Collect';
                    $add_income->date = date('Y-m-d');
                    $add_income->amount = $fees_payment->amount;
                    $add_income->fees_collection_id = $fees_payment->id;
                    $add_income->active_status = 1;
                    $add_income->income_head_id = $income_head->income_head_id;
                    $add_income->payment_method_id = @$payment_method->id;
                    $add_income->created_by = $fees_payment->created_by;
                    $add_income->school_id = $fees_payment->school_id;
                    $add_income->academic_id = $fees_payment->academic_id;
                    $add_income->save();
                    Cache::forget('have_due_fees_'.@$fees_payment->studentInfo->user_id); 
                    DB::commit();
                    if($user->role_id == 2){
                        return redirect()->to(url('student-fees'))->send();
                    }elseif($user->role_id == 3){
                        return redirect()->to(url('parent-fees'.'/'.$fees_payment->student_id))->send();
                    }
                
            }else{
                return [
                    "success" => false,
                    "url" => url('/home')
                ];
            }
        }
        return [
            "success" => false,
            "url" => url('/home')
        ];
    }

    private function validationProcess($url,$data,$config)
    {
        $url = $url."?val_id={$data['val_id']}&store_id={$config['store_id']}&store_passwd={$config['store_password']}&v=1&format=json";
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

        $result = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE); 
        if($code == 200 && !( curl_errno($handle)))
        {           
            return json_decode($result);
        } else {
            return false;
        }
    }

    public function cancelCallback($data){
        if($data['value_d'] == 'diposit')
        {
            $deposit = WalletTransaction::find($data['value_b']);
            if($deposit){
                $deposit->delete();
            }            
            return route('wallet.my-wallet');
        }elseif($data['value_d'] == 'Lms'){
            $coursePurchase = CoursePurchaseLog::find($data['value_a']);
            return url('lms/student/purchase-log',$coursePurchase->student_id);
        }else{
            return route('fees.fees-invoice-view',[$data['value_b'],'view']);
        }
    }


    private function paymentUrl($environment){
        return $environment == 'live' ? 'https://securepay.sslcommerz.com/gwprocess/v4/api.php':'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
    }

    private function validateUrl($environment)
    {
        return $environment == 'live' ? 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php':'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php';
    }

}