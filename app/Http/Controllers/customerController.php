<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class customerController extends Controller
{


	public function checkEmail(Request $request){
		if($request->has('email')){

			if(\App\User::where('email','=',$request->get('email'))->count()==0){
				return 'ok';
			}else{
				abort(499);
			}
		}else{
			abort(420); 
		}
	}


    public function signup(Request $request){
    	$this->validate(request(),[
    		'dogName'		=>	'required',
    		'dogSize'		=>	'required',
    		'firstName'		=>	'required',
    		'lastName'		=>	'required',
    		'email'			=>	'required|email',    		
    		'password'		=>	'required',
    		'lineOne'		=>	'required',    		
    		'postcode'		=>	'required',
    		'stripeData'	=>	'required',
    		'planSelected'	=>	'required',
    	]);
		
		\Stripe\Stripe::setApiKey(env('STRIPE_PRIVATE'));

    	$stripeData = $request->get('stripeData');

    	$customer = \Stripe\Customer::create(array(
			"email" => $request->get('email'),
			"source" => $stripeData['id'],
		));


    	$user = new \App\User;
    	$user->name 		= $request->get('firstName').' '.$request->get('lastName');
    	$user->firstName 	= $request->get('firstName');
    	$user->lastName 	= $request->get('lastName');
    	$user->email 		= $request->get('email');
    	$user->password 	= \Hash::make($request->get('password'));
    	$user->dogName	 	= $request->get('dogName');
    	$user->dogSize 		= $request->get('dogSize');
    	$user->lineOne 		= $request->get('lineOne');
    	$user->lineTwo 		= $request->get('lineTwo');
    	$user->lineThree 	= $request->get('lineThree');
    	$user->city 		= $request->get('city');
    	$user->county 		= $request->get('county');
    	$user->postcode 	= $request->get('postcode');
    	$user->stripe_id 	= $customer->id;
    	$user->plan_id  	= $request->get('planSelected');
    	$user->save();

    	$plan = \App\stripePlan::find($user->plan_id);

    	$stripeRequest = new \App\stripeRequest;

    	$status = $stripeRequest->createNewSubscription($user,$plan);

    	return $status;
    }

}
