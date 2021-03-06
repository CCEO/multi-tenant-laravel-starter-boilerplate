<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;

use App\Role;
use App\Permission;
use \Illuminate\Support\Str;

//Enables us to output flash messaging
use Session;

class UserController extends Controller {

    public function __construct() {
        $this->middleware(['auth', 'isAdmin']); //isAdmin middleware lets only users with a specific permission permission to access these resources
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index() {
        $users = User::all(); 
        return view('tenant.users.index')->with('users', $users);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {
        $roles = Role::all();
        return view('tenant.users.create', ['roles'=>$roles]);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {
        
        //Validate name, email and password fields
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:tenant.users',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create(['name'=> $request->name, 'email' => $request->email, 'password' => bcrypt($request->password)]);

        $roles = $request['roles']; //Retrieving the roles field
        
        //Checking if a role was selected
        if (isset($roles)) {

            foreach ($roles as $role) {
            $role_r = Role::where('id', '=', $role)->firstOrFail();            
            $user->assignRole($role_r); //Assigning role to user
            }
        }        

        session()->flash('alert', ['type' => 'success', 'message' => 'User added']);        

        return redirect()->route('tenant.users.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('users'); 
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $user = User::findOrFail($id); //Get user with specified id
        $roles = Role::get(); //Get all roles

        return view('tenant.users.edit', compact('user', 'roles')); //pass user and roles data to view

    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $user = User::findOrFail($id); //Get role specified by id

        //Validate name, email and password fields    
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:tenant.users,email,'.$id,
            'password'=>'required|min:6|confirmed'
        ]);
        $input = $request->only(['name', 'email', 'password']); //Retreive the name, email and password fields
        $roles = $request['roles']; //Retreive all roles
        $user->fill($input)->save();

        if (isset($roles)) {        
            $user->roles()->sync($roles);  //If one or more role is selected associate user to roles          
        }        
        else {
            $user->roles()->detach(); //If no role is selected remove exisiting role associated to a user
        }

        session()->flash('alert', ['type' => 'success', 'message' => 'User updated']);        
        return redirect()->router('tenant.users.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
       //Find a user with a given id and delete
        $user = User::findOrFail($id); 
  
        // do not delete super-admin user or is an admin
        if (($user->email == env('SUPER_ADMIN_EMAIL')) || $user->hasRole('admin')) {
            session()->flash('alert', ['type' => 'danger', 'message' => 'Not allowed to delete this user']);        
            return redirect()->route('tenant.users.index');
        } else {
            $user->delete();

            session()->flash('alert', ['type' => 'success', 'message' => 'User deleted']);        
            return redirect()->route('tenant.users.index');
        }
    }
}