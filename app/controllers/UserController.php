<?php

class UserController extends BaseController {

  public function __construct() {
    $this->beforeFilter('guest',
      array('only' => array('getSignup', 'getLogin')));
    $this->beforeFilter('csrf',
      array('only' => array('postSignup', 'postLogin')));
    $this->beforeFilter('auth',
      array('only' => array('anyLogout')));
  }


  # GET: http://localhost/users
  public function getIndex() {
    return Redirect::to('/pastes');
  }


  # GET: http://localhost/users/signup
  public function getSignup() {
    return View::make('signup');
  }


  # POST: http://localhost/users/signup
  public function postSignup() {
    $rules = array(
      'email' => 'email|unique:users,email',
      'password' => 'min:3'   
    );          

    $validator = Validator::make(Input::all(), $rules);

    if($validator->fails()) {
      return Redirect::to('/users/signup')
        ->with('flash_message', 'Sign up failed; please fix the errors listed below.')
        ->withInput()
        ->withErrors($validator);
    }

    $user = new User;
    $user->email = Input::get('email');
    $user->password = Hash::make(Input::get('password'));
    $user->remember_token = True;

    # Try to add the user
    try {
      $user->save();
    }
    # Fail
    catch (Exception $e) {
      return Redirect::to('/users/signup')
        ->withErrors(['message' => 'Sign up failed; please try again.'])
        ->withInput();
    }

    # Log the user in
    Auth::login($user);

    return Redirect::to('/pastes')->with('flash_message', 'Welcome to Binpaste!');
  }


  # GET: http://localhost/users/login
  public function getLogin() {
    return View::make('login');

  }


  # POST: http://localhost/users/login
  public function postLogin() {
    # explicitly skip validation, try not to leak information about accounts

    $credentials = Input::only('email', 'password');

    if (Auth::attempt($credentials, $remember=True)) {
      return Redirect::intended('/pastes')->with('flash_message', 'Welcome Back!');
    }
    else {
      return Redirect::to('/users/login')
        ->withErrors(['message' => 'Log in failed; please try again.'])
        ->withInput();
    }

    return Redirect::to('/users/login');
  }


  # ANY: http://localhost/users/logout
  public function anyLogout() {
    Auth::logout();

    return Redirect::to('/');
  }
}
