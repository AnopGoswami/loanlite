<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

class LoanController extends Controller
{

     /**
     * Add loan request applied by customer
     *
     * @param  object  $request
     * @return json response
     */
    public function apply(Request $request)
    {

        //Check if there is already pending loan application 
        $pendingLoan=Loan::where(['customer_id'=>$request->user()->id,'status'=>Loan::STATUS_PENDING])->first();
        if($pendingLoan)
        {
            return response()->json(['message' => "You already have pending loan application with ID:".$pendingLoan->id]);
        }

        //Define validation rules
        $messages = [
            'term.between' => 'The term must be between '.Loan::MIN_TERM.' to '.Loan::MAX_TERM.' weeks'
        ];
        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric|between:'.Loan::MIN_AMOUNT.','.Loan::MAX_AMOUNT,
            'term' => 'required|integer|between:'.Loan::MIN_TERM.','.Loan::MAX_TERM,
        ],$messages);

        
        //Validating request
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

      
        //Create loan entry
        $loan = new Loan();
        $loan->customer_id = $request->user()->id;
        $loan->amount = $request->amount;
        $loan->term = $request->term;
        $loan->status = Loan::STATUS_PENDING;


         //Save Loan and return response json
        if(!empty($loan->save())){

            return response()->json(['message' => "Congratulations your loan request successfully submitted. Loan ID:".$loan->id]);
        
        }else{

            return response()->json(['message' => "Error while loan submission"]);
        }
        
    }


    /**
     * View loan request applied by customer
     *
     * @param  object  $request
     * @param  integer $id
     * @return json response
     */
    public function view(Request $request,$id=null)
    {
        //Find loan entries
        $loans = (!empty($id))?Loan::where(['id'=>$id,'customer_id'=>$request->user()->id])->get():Loan::where(['customer_id'=>$request->user()->id])->get();


         //Return response json
        if(!empty($loans)){

            $result=[];
            foreach($loans as $loan)
            {
                $result[]= [
                    'id'=>$loan->id,
                    'amount'=>$loan->amount,
                    'dueamount'=>$loan->amount-$loan->payments->sum('amount'),
                    'term'=>$loan->term." weeks",
                    'status'=>Loan::STATUS[$loan->status],
                    'created_at'=>date("Y-m-d H:i:s",strtotime($loan->created_at)),
                    'installments'=>$loan->installments,
                    'payments'=>$loan->payments,
                ];
            }

            return response()->json(['loans' => $result]);
        
        }else{

            return response()->json(['message' => "No loan application found."]);
        }
        
    }

     /**
     * View pending loan list by admin
     *
     * @param  object  $request
     * @param  integer  $status
     * @return json response
     */
    public function list(Request $request,$status=null)
    {
        //Find loan entry
        $loans = ($status!=null)?Loan::where(['status'=>$status])->get():Loan::get();

         //Return response json
        if(count($loans)>0){

            return response()->json(['loans' => $loans]);
        
        }else{

            return response()->json(['message' => "No loan application"]);
        }
        
    }

     /**
     * Approve loan request applied by customer
     *
     * @param  object  $request
     * @return json response
     */
    public function approve($id)
    {
        //Find loan entry
        $loan = Loan::where(['id'=>$id,'status'=>Loan::STATUS_PENDING])->first();


         //Return response json
        if(!empty($loan)){

            //Aprove loan
            $loan->status=Loan::STATUS_APPROVED;
            $loan->approved_at=Carbon::now()->toDateTimeString();

            if($loan->save())
            {
                //Generate installments
                if($this->generateInstallments($loan))
                {
                    return response()->json(['message' => "Loan has been approved."]);

                }else{

                    return response()->json(['message' => "Error while generating installments."]);
                }

            }else{

                return response()->json(['message' => "Error while approving loan. ID:$id"]);

            }
        
        }else{

            return response()->json(['message' => "No pending loan application found with ID:$id"]);
        }
        
    }

    /**
     * Decline loan request applied by customer
     *
     * @param  object  $request
     * @return json response
     */
    public function decline($id)
    {
        //Find loan entry
        $loan = Loan::where(['id'=>$id,'status'=>Loan::STATUS_PENDING])->first();


         //Return response json
        if(!empty($loan)){

            //Aprove loan
            $loan->status=Loan::STATUS_DECLINED;

            if($loan->save())
            {
                return response()->json(['message' => "Loan has been declined."]);

            }else{

                return response()->json(['message' => "Error while declining loan. ID:$id"]);

            }
        
        }else{

            return response()->json(['message' => "No pending loan application found with ID:$id"]);
        }
        
    }


     /**
     * Generates installments for given loan objects
     *
     * @param  object  $loan
     * @return bool response
     */

    private function generateInstallments($loan)
    {
        //Generate installments
        $totalAmount=0;
        $installmentAmount=0;
        $amount=round(($loan->amount/$loan->term),2);
        $installments=[];

        for ($i=1;$i<=$loan->term;$i++){ 
            
            //Forst last installment use remaining amount else amount part
            $installmentAmount=($i==$loan->term)?$loan->amount-$totalAmount:$amount;

            $installments[]=[
                'loan_id'=>$loan->id,
                'amount'=>$installmentAmount,
                'status'=>Installment::STATUS_PENDING,
                'due_date'=>date("Y-m-d H:i:s",strtotime("+ ".(7*$i)." days")),
                'created_at'=>Carbon::now()->toDateTimeString(),
            ];

            $totalAmount+=$amount;
        }

        //Insert installments
        return Installment::insert($installments);
    }

     /**
     * Pay loan amount
     *
     * @param  object  $request
     * @return json response
     */
    public function pay(Request $request,$id)
    {
        //Check if loan status is approved
        $approvedLoan=Loan::where(['id'=>$id,'customer_id'=>$request->user()->id,'status'=>Loan::STATUS_APPROVED])->first();
        if(empty($approvedLoan))
        {
            return response()->json(['message' => "Loan is not payble / not found."]);
        }

        //Get paid and due amount
        $paidAmount=Payment::where('loan_id',$id)->sum('amount');
        $nextPendingIstallment=Installment::where(['loan_id'=>$id,'status'=>Installment::STATUS_PENDING])->first();
        $dueAmount=$approvedLoan->amount-$paidAmount;
        $minAmount=$nextPendingIstallment->amount;
        

        //Create validation rules 
        $rules=[];
        $messages=[];
        if($minAmount<$dueAmount)
        {
            //Minimum installment amount to be paid if due amount is greater than installment amount
            $rules['amount']="required|numeric|between:$minAmount,$dueAmount";
            $messages['between']="Amount should be between $minAmount and $dueAmount";

        }else{  

            //If due amount is less than installment amount
            $rules['amount']="required|numeric|between:$dueAmount,$dueAmount";
            $messages['between']="Amount value should be $dueAmount";
        }

       
        //Apply validation rules
        $validator = Validator::make($request->all(),$rules,$messages);

        
        //Validating request
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        //Create payment entry
        $payment = new Payment();
        $payment->loan_id = $id;
        $payment->amount = $request->amount;

        $totalPaidAmount=$paidAmount+$payment->amount;


         //Save Loan and return response json
        if(!empty($payment->save())){

            //Check and mark installments as paid if amount is appropriate
            //if all installments are paid then mark loan as paid
            if($this->payInstallments($approvedLoan,$totalPaidAmount)){

                return response()->json(['message' => "Congratulations you have successfully paid your loan."]);

            }else{

               return response()->json(['message' => "Amount has been paid sucessfully."]);

            }
        
        }else{

            return response()->json(['message' => "Error while adding payment."]);
        }
        
    }

    /**
     * Pay installments for loan and mark loan as paid if all installments are paid
     *
     * @param  object  $loan
     * @return bool response
     */
    private function payInstallments($loan,$totalPaidAmount)
    {

        //Get all installments
        $installments=Installment::where('loan_id',$loan->id)->get();
        $remainingAmount=$totalPaidAmount;
        $paidInstallments=0;

        //Check and mark as paid remaining installments
        foreach($installments as $installment)
        {
            if($remainingAmount>=$installment->amount && $installment->status==Installment::STATUS_PENDING){
                
                //Mark installment as paid
                $installment->status=Installment::STATUS_PAID;
                $installment->paid_at=Carbon::now()->toDateTimeString();
                $installment->save();
            }

            if($installment->status==Installment::STATUS_PAID)
            {
                $paidInstallments++;
            }

            $remainingAmount-=$installment->amount;
                
        }

        //If all installments are paid so mark loan as paid
        if($paidInstallments==$installments->count()){

            $loan->status=Loan::STATUS_PAID;
            $loan->paid_at=Carbon::now()->toDateTimeString();
            if($loan->save())
            {
                return true;
            }    

        }

        return false;
    }


}
