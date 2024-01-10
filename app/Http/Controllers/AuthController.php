<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use PDO;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            // return $request;
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'phone' => ['required'],
                'calle' => ['required', 'string'],
                'ciudad' => ['required'],
                'codigo_postal' => ['required'],
                'colonia' => ['required'],
                'estado' => ['required'],
                'num_ext' => ['required'],
                'organizacion' => ['required'],
                'pais' => ['required'],
                'rfc' => ['required', 'string'],
                'role' => ['required'],
            ]);

            $file = $request->file('logo');
            $originalname = $file->getClientOriginalName();
            $pathLogo = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urllogo = Storage::url($pathLogo);

            if ($request->role == 8) {
                $pay_days = $request->pay_days;
            } else {
                $pay_days = '';
            }


            $org = Organization::create([
                'name' => $request->organizacion,
                'rfc' => $request->rfc,
                'colony_id' => $request->colonia,
                'street' => $request->calle,
                'role_id' => $request->role,
                'external_number' => $request->num_ext,
                'internal_number' => $request->num_int,
                'logo' => $urllogo,
                'pay_days' => $pay_days,
            ]);


            if ($org) {

                if ($request->role == 7) {
                    $iva = $request->iva;
                    Organization::where('id', $org->id)
                        ->update(['url' => $request->url]);
                    // $orgUpdate = Organization::where('id', $org->id)
                    //     ->update(['url' => $request->url]);
                    // if ($orgUpdate) {
                    //     $arrayCategory = explode(',', $request->get('categoria'));
                    //     $category = Category::find($arrayCategory);
                    //     if ($category) {
                    //         $org->categories()->attach($category);
                    //     }
                    // }
                } else {
                    $iva = '';
                }

                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'role_id' => $request->role,
                    'organization_id' => $org->id,
                    'iva' => $iva,
                ])->sendEmailVerificationNotification();

                Notification::route('mail', $request->email)
                    ->notify(new GeneralNotification(3));

                $response['success'] = true;
                return $response;
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required'
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken($user->email . '-' . now());

                return response()->json([
                    'token' => $token->accessToken,
                    'user' => $user,
                    'success' => true,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña ingresada es incorrecta,favor de verificar.',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
    public function verify($user_id, Request $request)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->to('/verify-error');
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return redirect()->to('/success-verify');
        }
        // $response['message'] = 'Correo verificado correctamente';
        // $response['success'] = true;
        // return $response;
    }

    public function resend()
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $response['message'] = 'El correo ya ha sido verificado con anterioridad';
            $response['success'] = true;
            return $response;
        }

        $user->sendEmailVerificationNotification();

        $response['message'] = 'Se ha enviado el link de verificacion a tu correo';
        $response['success'] = true;
        return $response;
    }
    protected function sendResetLinkResponse(Request $request)
    {
        // dd($request);
        $input = $request->only('email');
        $validator = Validator::make($input, [
            'email' => "required|email"
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $response =  Password::sendResetLink($input);
        if ($response === Password::RESET_LINK_SENT) {
            $message = "El correo para restablecer su contraseña  ha sido enviado";
            $success = true;
        } else {
            $message = "Error al enviar el correo, es posible que el email no exista o que ya se haya enviado un email de recuperación.";
            $success = false;
        }
        //$message = $response == Password::RESET_LINK_SENT ? 'Mail send successfully' : GLOBAL_SOMETHING_WANTS_TO_WRONG;
        $response = ['data' => $input, 'message' => $message, 'success' => $success];
        return response($response, 200);
    }
    protected function sendResetResponse(Request $request)
    {
        $input = $request->only('email', 'token', 'password', 'password_confirmation');
        $validator = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $response = Password::reset($input, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
            //$user->setRememberToken(Str::random(60));
            event(new PasswordReset($user));
        });
        if ($response == Password::PASSWORD_RESET) {
            $message = "Tu contraseña ha sido modificada.";
            $success = true;
        } else {
            $message = "El correo ingresado no corresponde a la solicitud de recuperación o su token ya esta vencido.";
            $success = false;
        }
        $response = ['data' => $input['email'], 'message' => $message, 'success' => $success];
        return response()->json($response);
    }
    // protected function sendResetResponse(Request $request, $response)
    // {
    //     return response(['message' => trans($response)]);
    // }

    // protected function sendResetFailedResponse(Request $request, $response)
    // {
    //     return response(['error' => trans($response)], 422);
    // }

    // protected function sendResetLinkResponse(Request $request, $response)
    // {
    //     return response(['message' => $response]);
    // }


    // protected function sendResetLinkFailedResponse(Request $request, $response)
    // {
    //     return response(['error' => $response], 422);
    // }
}
