<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateProperty;
use App\Http\Requests\ValidateUser;
use App\Http\Requests\ValidateVisa;
use App\Mail\FinishUserMail;
use App\Mail\InviteUserMail;
use App\Models\ResidencePrefernce;
use App\Models\User;
use App\Models\Visa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class VisaController extends Controller
{
    public function createBasicStep(Request $request)

    {   
        $user = $request->session()->get('user');
        return view('visas.create-basic-step',compact('user'));
    }
    public function postCreateBasicStep(ValidateUser $request)

    {
        if(empty($request->session()->get('user'))){
            $user = new User();
            $user->fill([
                'name' => $request->name,
                'email' => $request->email,
                'password'=>  Hash::make($request->password),
            ]);
            $request->session()->put('user', $user);
        }else{
            $user = $request->session()->get('user');
            $user->fill([
                'name' => $request->name,
                'email' => $request->email,
                'password'=>  Hash::make($request->password),
            ]);
            $request->session()->put('user', $user);
        }
        return redirect()->route('visa.create.step.one');

    }
    public function createStepOne(Request $request)
    {    
        $user = $request->session()->get('user');
        $visa = $request->session()->get('visa');
        return view('visas.create-step-one',compact('user','visa'));
    }
    public function postCreateStepOne(ValidateVisa $request)
    {
        $filename = $_FILES["personal_image"]["tmp_name"];
        $personal_destination = "upload/" . $_FILES["personal_image"]["name"]; 
        move_uploaded_file($filename, $personal_destination); 
        $filename  = $_FILES["passport_image"]["tmp_name"];
        $passport_destination = "upload/" . $_FILES["passport_image"]["name"]; 
        move_uploaded_file($filename, $passport_destination); 
        if(empty($request->session()->get('visa'))){
            $visa = new Visa();
            $visa->fill([
                'nickname' => $request->nickname,
                'fatherName' => $request->fatherName,
                'date_of_birth'=> $request->date_of_birth,
                'arrival_date'=> $request->arrival_date,
                'proffession'=> $request->proffession,
                'personal_image' =>$personal_destination,
                'passport_image' =>$passport_destination,
            ]);
            $request->session()->put('visa', $visa);
           
        }else{
            $visa = $request->session()->get('visa');
            $visa->fill([
                'nickname' => $request->nickname,
                'fatherName' => $request->fatherName,
                'date_of_birth'=> $request->date_of_birth,
                'arrival_date'=> $request->arrival_date,
                'proffession'=> $request->proffession,
                'personal_image' =>$personal_destination,
                'passport_image' =>$passport_destination,
            ]);
            $request->session()->put('visa', $visa);
        }
        return redirect()->route('visa.create.step.two');
    }


    public function createStepTwo(Request $request)

    {
        $user = $request->session()->get('user');
        $visa = $request->session()->get('visa');
        return view('visas.create-step-two',compact('user','visa'));

    }

   public function postCreateStepTwo(ValidateProperty $request)

    {
         if(empty($request->session()->get('residence'))){
            $residence= new ResidencePrefernce();
            $residence->fill($request->all());
            $request->session()->put('residence', $residence);
        }else{
            $residence = $request->session()->get('residence');
            $residence->fill($request->all());
            $request->session()->put('residence',$residence);
        }
        $user = $request->session()->get('user');
        $visa = $request->session()->get('visa');
        $request->session()->put('user', $user);
        $request->session()->put('visa', $visa);
        return redirect()->route('visa.create.step.three');

    }

    public function createStepThree(Request $request)

    {    
        $user = $request->session()->get('user');
        $visa = $request->session()->get('visa');
        $residence = $request->session()->get('residence');
        return view('visas.create-step-three',compact('user','visa','residence'));

    }
    public function postCreateStepThree(Request $request)

    {
        $user = $request->session()->get('user');
        $user->role_id = User::USER;
        $user->save();
        $visa = $request->session()->get('visa');
        $residence = $request->session()->get('residence');
        $visa->user_id = $user->id;
        $residence->user_id = $user->id;
        $request->session()->forget('user');
        $request->session()->forget('visa');
        $request->session()->forget('residence');
        $visa->save();
        $residence->save();
        Mail::to('eng.maya.esmaeel1@gmail.com')->send(new FinishUserMail());
        return view('visas.index');

    }
}