<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\User;
use DataTables;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
 
class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            if(!Auth::check())
                return redirect("login")->withSuccess('Opps! You do not have access');
    
            if ($request->ajax()) {
                $users = User::select('id','name','email', 'photo')->get();
                return Datatables::of($users)->addIndexColumn()
                    ->addColumn('photo', function ($users) {
                        $url= url('/images/'.$users->photo);
                        return '<img src='.$url.' border="0" style="width: 200px;height: 200px;"  class="img-rounded" align="center" />';
                    })
                    ->addColumn('action', function($users){
                        $button = '<button type="button" name="edit" id="'.$users->id.'" class="edit btn btn-primary btn-sm"> <i class="bi bi-pencil-square"></i>Edit</button>';
                        $button .= '   <button type="button" name="edit" id="'.$users->id.'" class="delete btn btn-danger btn-sm"> <i class="bi bi-backspace-reverse-fill"></i> Delete</button>';
                        return $button;
                    })
                    ->rawColumns(['photo', 'action'])
                    ->make(true);
            }
    
            return view('user.index');
        } catch(Exception $e) {
            throw $e->getMessage();
        }
    }
 
    public function store(Request $request)
    {
        try {
            $rules = array(
                'name'      =>  'required',
                'email'     =>  'required',
                'password'  =>  'required',
                'photo'     => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            );
    
            $error = Validator::make($request->all(), $rules);
    
            if($error->fails())
            {
                return response()->json(['errors' => $error->errors()->all()]);
            }
    
            $image = $request->file('photo');
            $photo = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $photo);
            $pass = $request->password;
            $postpass = Hash::make($pass);

            $requestBody = array(
                'name'        =>  $request->name,
                'email'       =>  $request->email,
                'photo'       =>  $photo,
                'password'    =>  $postpass
            );
    
            User::create($requestBody);
    
            return response()->json(['success' => 'Data Added successfully.']);
        } catch(Exception $e) {
            throw $e->getMessage();
        }
    }
 
    public function edit($id)
    {
        try {
            if(request()->ajax())
            {
                $data = User::findOrFail($id);
                return response()->json(['result' => $data]);
            }
        } catch(Exception $e) {
            throw $e->getMessage();
        }
    }
 
    public function update(Request $request)
    {
        try {
            $rules = array(
                'name'        =>  'required',
                'email'         =>  'required'
            );
    
            $error = Validator::make($request->all(), $rules);
    
            if($error->fails())
            {
                return response()->json(['errors' => $error->errors()->all()]);
            }
    
            $requestBody = array(
                'name'    =>  $request->name,
                'email'     =>  $request->email
            );
            if (!empty($request->file('photo'))) {
                $image = $request->file('photo');
                $photo = rand() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $photo);
                $requestBody['photo'] = $photo;
            }
    
            User::whereId($request->hidden_id)->update($requestBody);
    
            return response()->json(['success' => 'Data is successfully updated']);
        } catch(Exception $e) {
            throw $e->getMessage();
        }
    }
 
    public function destroy($id)
    {
        try {
            $data = User::findOrFail($id);
            $data->delete();
        } catch(Exception $e) {
            throw $e->getMessage();
        }
    }
}