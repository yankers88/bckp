<?php

class AccountController extends BaseController{
    
    
    
    /*
    |   Social Authentication -> Google
    */
    public function loginWithGoogle(){
        $code = Input::get( 'code' );
        $googleService = OAuth::consumer( 'Google', 'https://localhost/gp' );
        if ( !empty( $code ) ) {
            $token = $googleService->requestAccessToken( $code );
            $result = json_decode($googleService->request( 'https://www.googleapis.com/oauth2/v1/userinfo' ), true );
            if(isset($result['email']) && !empty($result['email'])){
            if(AccountController::userExist($result['email'])){
            $authenticate = User::where('email', '=', $result['email']);
                if($authenticate->count()){
                    $authenticate = $authenticate->first();
                    $authenticate->active = 1;
                    $authenticate->activate_code = '';
                    $authenticate->save();
                    Session::put('auth', $authenticate);
                    return Redirect::secure('/');
                }
            }else{
                 Session::put('temp',$result);
                return Redirect::secure('/update/social');
            }
            
            }else{
                 $result['email']='';  
                 Session::put('temp',$result);
                return Redirect::secure('/update/social');
            }
            
        }
        else {
            $url = $googleService->getAuthorizationUri();
            return Redirect::secure( (string)$url );
        }
    }
    
    
    /*
    |   Social Authentication -> Facebook
    */
   public function loginWithFacebook() {
        $code = Input::get( 'code' );
        $fb = OAuth::consumer( 'Facebook', 'https://localhost/fb' );
        if ( !empty( $code ) ) {
            $token = $fb->requestAccessToken( $code );
            $result = json_decode( $fb->request( '/me?fields=email,name,gender' ), true );
            $result['picture']  = 'https://graph.facebook.com/'.$result['id'].'/picture?type=large';
           if(isset($result['email']) && !empty($result['email'])){
           if(AccountController::userExist($result['email'])){
                $authenticate = User::where('email', '=', $result['email']);
                if($authenticate->count()){
                    $authenticate = $authenticate->first();
                    $authenticate->active = 1;
                    $authenticate->activate_code = '';
                    $authenticate->save();
                    Session::put('auth', $authenticate);
                    return Redirect::secure('/');
                }
            }
            else{
                Session::put('temp',$result);
                return Redirect::secure('/update/social');
            }
            }else{
                $result['email']='';
                Session::put('temp',$result);
                return Redirect::secure('/update/social');
            }
        }
        else {
            $url = $fb->getAuthorizationUri();
            return Redirect::secure( (string)$url );
        }
    } 
    public function loginWithTwitter() {
    $token = Input::get( 'oauth_token' );
    $verify = Input::get( 'oauth_verifier' );
    $tw = OAuth::consumer( 'Twitter', 'https://127.0.0.1/tp');
             if ( !empty( $token ) && !empty( $verify ) ) {
             $token = $tw->requestAccessToken( $token, $verify );
             $result = json_decode( $tw->request( 'account/verify_credentials.json' ), true );
             $result['username']=$result['screen_name'];
             if(isset($result['email']) && !empty($result['email'])){
               if(AccountController::userExist($result['email'])){
               $authenticate = User::where('email', '=', $result['email']);
                if($authenticate->count()){
                    $authenticate = $authenticate->first();
                    $authenticate->active = 1;
                    $authenticate->activate_code = '';
                    $authenticate->save();
                    Session::put('auth', $authenticate);
                    return Redirect::secure('/');
                }}else{
                Session::put('temp',$result);
                return Redirect::secure('/update/social');
            }
            }else{
                  $result['email']='';
                Session::put('temp',$result);
                return Redirect::secure('/update/social');
            } }else {
        // get request token
        $reqToken = $tw->requestRequestToken();
         // get Authorization Uri sending the request token
       //  $twitterService->requestRequestToken();
        $url = $tw->getAuthorizationUri(array('oauth_token' => $reqToken->getRequestToken()));
        // return to twitter login url
        return Redirect::to( (string)$url );
    }
}
    /*
    |   Register Social User -> Facebook / Email
    */
    public function updateSocialPost(){
       $validator = Validator::make(Input::all(), array(
        'email'     => 'email|required',
        'username'  => 'required|min:3|max:30|unique:users',
        'language'  => 'required'
        ));
        
        if($validator->fails()){
            return Redirect::secure('/update/social')->withErrors($validator)->withInput();
        }else{
            $temp = Session::get('temp');
            $temp['picture']= URL::secure('/assets/uploads').'/no_image.jpeg';
            $temp['gender']='';
            $user_check= AccountController::checkAvailability(Input::get('username'));
            if($user_check=='not_taken'){
            $new = User::create(array(
                'name'      => $temp['name'],
                'email'     => Input::get('email'),
                'username'  => Input::get('username'),
                'gender'    => $temp['gender'],
                'language'  => Input::get('language'),
                'active'    => 1,
                'ban'       => 0,
                'dp_uri'    => $temp['picture'],
                'cover_uri' => URL::secure('/assets/uploads').'/cover_image.jpeg'
            ));
            }
            else{
            echo 'Username is already taken.Try another one.';
            }
            if($new){
                $user = User::where('email', '=', $temp['email']);
                if($user->count()){
                    $user = $user->first();
                   Session::put('auth',$user);
                   Session::forget('temp');
                   return Redirect::secure('/');
                }
            }
        }}
    
    
    /*
    |   Check if User Exists by Email
    */
    public function userExist($email){
        $getuser = User::where('email','=',$email);
        
        if($getuser->count()){
            return true;
        }else{
            return false;
        }
    }
     /*
    |   Check if UserName Exists by username
    */
    public function checkAvailability($name){
        $getuser = User::where('username','=',$name);
        
        if($getuser->count()){
            return 'taken';
        }else{
            return 'not_taken';
        }
    }
    /*
    |   Authenticate User -> Email / Google / Facebook 
    */
    public function login(){
        $validator = Validator::make(Input::all(),array(
            'email'     => 'required|email',
            'password'  => 'required'
        ));
        
        if($validator->fails()){
            return Redirect::secure('/login')->withErrors($validator)->withInput();
        }else{
            $user = Auth::attempt(array(
                'email'     => Input::get('email'),
                'password'  => Input::get('password')
            ) , true);
            if($user){
                $auth = User::where('email', '=', Input::get('email'));
                if($auth){
                    $auth = $auth->first();
                    Session::put('auth', $auth);
                    return Redirect::secure('/');
                }
            }else{
                return Redirect::secure('/login')->with('failure','Incorrect Email/Password combination!');
            }
        }
    }
    
    /*
    |   Register user with Email/Password
    */
    
    public function registerUser(){

        $validator = Validator::make(Input::all(), array(
            'name'          => 'required',
            'username'      => 'required|min:3|max:30|unique:users',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:6',
            //'language'      => 'required',
        ));
        
        if($validator->fails()){

            return Redirect::secure('/register')->withErrors($validator)->withInput();
        }else{
            
            $code       = str_random(60);
            $email      = Input::get('email');
            $username   = Input::get('username');
            $user_check= AccountController::checkAvailability(Input::get('username'));
            if($user_check=='not_taken'){
            $new = User::create(array(
            'name'              => Input::get('name'),
            'username'          => $username,
            'password'          => Hash::make(Input::get('password')),
            'email'             => $email,
            'language'          => Input::get('language'),
            'active'            => 0,
            'ban'               => 0,
            'dp_uri'            => URL::secure('/assets/uploads').'/no_image.jpeg',
            'cover_uri'         => URL::secure('/assets/uploads').'/cover_image.jpeg',
            'activate_code'     => $code
            ));
            }else{
            
                echo 'Username is already taken.Try another one.';
            }
            if($new){
   //       echo 'hi';exit;
                 Mail::send('emails.auth.activate', array(
					'link' => URL::route('account-activate', $code) ,
					'username' => $username
				),
                           
				function ($message){
					$message->to(Input::get('email'))->subject('Activate your Account');
				}
				);
				
                  
                return Redirect::secure('/login')->with('global','Please verify account, check your email for link!');
            }else{
                return Redirect::secure('/login')->with('global','Unable to create your account, Try again!!');
            }
        }
    }
    
    /*
    |   Email Verification
    */
    
    public function getActivate($code){
        $user = User::where('activate_code', '=', $code)->where('active', '=', 0);
		if ($user->count()){
			$user = $user->first();

			// Update User To Active State

			$user->active = 1;
			$user->activate_code = '';
			
			if ($user->save()){
			    if(Session::has('auth')){
			        Session::forget('auth');
			        Session::put('auth',$user);
			        return Redirect::secure('/')->with('global', 'Congrats! We have activated your account');
			    }else{
			        return Redirect::secure('/login')->with('global', 'Congrats! We have activated your account');
			    }
            }
        }
        
        return Redirect::route('get-login')
                ->with('global', 'we could not activate your account, 
                 try again late!');
        
        die();
        
    }
    
    /*
    |   Resend Email Verification Link
    */
    
    public function sendVerification(){
        if(Request::ajax()){
            if(Session::has('auth')){
                $username   = Session::get('auth')['username'];
                $email      = Session::get('auth')['email'];
                
                $user = User::where('email','=',$email)->first();
                if($user->active == 0 && strlen($user->activate_code) > 0){
                    
                    Mail::send('emails.auth.activate', array(
    					'link' => URL::route('account-activate', $user->activate_code) ,
    					'username' => $username
    				),
                               
    				function ($message) use($username,$email){
    					$message->to($email, 
                        $username)->subject('Activate your Account');
    				});
    				
    				return Response::json(true);
                }else{
                    $code = str_random(60);
                    $user->activate_code = $code;
                    $user->active = 0;
                    $user->save();
                    
                    Mail::send('emails.auth.activate', array(
    					'link' => URL::route('account-activate', $code) ,
    					'username' => $username
    				),
                               
    				function ($message) use($username,$email){
    					$message->to($email, 
                        $username)->subject('Activate your Account');
    				});
    				
    				return Response::json(true);
                }
            }else{
                return Reponse::json(false);
            }
        }
    }
    
    /*
    |   Register Account Route View Generator
    */
    public function registerUserGet(){
        return View::make('accounts.register-get');
    }
    
    /*
    |   Logout Authenticated User
    */
    public function logout(){
        Auth::logout();
        Session::forget('auth');
        return Redirect::secure('/');
    }
    
    
    /*
    |   Only Allow Access to /update/social route for users with Sessions::get('temp');
    */
    public function updateSocial(){
        if(Session::has('temp')){
            return View::make('accounts.updatesocial');
        }else{
            return Redirect::secure('/');
        }
    }
    
    /*
    |   Show User Login Form
    */
    public function loginUserGet(){
        return View::make('accounts.login-get');
    }
    
}
