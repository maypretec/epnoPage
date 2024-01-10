<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Location;
use App\Models\VsUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function Create(Request $request)

    {
            $userAuth = Auth::user();

            if ($userAuth->role_id == 1) {
                $vs_id = $request->vs;
            } else {
                $vs_auth = VsUser::where('user_id', $userAuth->id)->first('vs_id');

                $vs_id = $vs_auth->vs_id;
            }

            if ($userAuth) {

                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'phone' => ['required', 'string', 'max:255'],
                    'password' => ['required', 'string', 'min:8'],
                ]);

                $user = User::create([
                    'organization_id' => $userAuth->organization_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'role_id' => $request->role_id,
                    'status' => 1,
                ]);

                if ($user) {
                    $user->sendEmailVerificationNotification();

                    $vs = VsUser::create(['user_id' => $user->id, 'vs_id' => $vs_id]);
                    if ($vs) {
                        $response['message'] = "Usuario creado correctamente.";
                        $response['success'] = true;
                        return $response;
                    } else {
                        $response['message'] = "Hubo un error al guardar el usuario nuevo.";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "Hubo un error al guardar el usuario.";
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['message'] = "Usuario no encontrado, favor de loggearse.";
                $response['success'] = false;
                return $response;
            }
    }
    public function UserComplete(Request $request)
    {
        $user = Auth::user();
        if ($user->organization_id == null && $user->status == 2) {
            $response['complete'] = false;
            $response['role'] = $user->role_id;
            $response['id'] = $user->id;
            return $response;
        } else {

            $response['role'] = $user->role_id;
            $response['id'] = $user->id;
            return $response;
        }
    }

    public function UserRole(Request $request)
    {
        $user = Auth::user();

        if ($user) {

            $response['role'] = $user->role_id;
            $response['id'] = $user->id;
            return $response;
        } else {

            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    public function profileInfo($id)
    {
        // $id = Auth::user()->id;
        // $arrayOrder = array();

        $info = DB::table('users')
            ->join('organizations', 'users.organization_id', 'organizations.id')
            ->join('colonies', 'organizations.colony_id', 'colonies.id')
            ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
            ->join('cities', 'postal_codes.city_id', 'cities.id')
            ->join('states', 'cities.state_id', 'states.id')
            ->join('regions', 'states.region_id', 'regions.id')
            ->join('countries', 'regions.country_id', 'countries.id')
            ->select(
                'users.id',
                'users.name',
                'users.role_id',
                'users.organization_id',
                'users.email',
                'users.phone',
                'organizations.logo',
                'organizations.name as org',
                'organizations.rfc',
                'colonies.name as colonie',
                'postal_codes.name as CP',
                'cities.name as city',
                'states.name as state',
                'countries.name as country'
            )
            ->where('users.id', $id)
            ->first();


        if ($info->role_id == 4 || $info->role_id == 9) {

            $org_rate = null;

            $user_rate = null;
        } elseif ($info->role_id == 1 || $info->role_id== 2 || $info->role_id == 3 || $info->role_id == 5) {
            //POR EL MOMENTO USER Y ORG RATE SON IGUALES, YA QUE SOLO HAY COMENTARIOS HACIA LA APP.
            $org_epno = DB::table('services')
                ->join('agent_ratings', 'services.id', 'agent_ratings.service_id')
                ->select(
                    DB::raw('AVG(rating) as rate')
                )
                ->where([
                    ['services.status', '=', '1'],
                    ['services.step_id', '=', 7],

                ])
                ->first();

            $user_epno = $org_rate = DB::table('services')
                ->join('agent_ratings', 'services.id', 'agent_ratings.service_id')
                ->select(
                    DB::raw('AVG(rating) as rate')
                )
                ->where([
                    ['services.status', '=', '1'],
                    ['services.step_id', '=', 7],

                ])
                ->first();

            $org_rate = round($org_epno->rate, 2);
            $user_rate = round($user_epno->rate, 2);
        } elseif ($info->role_id == 6) {
            //POR EL MOMENTO USER Y ORG RATE SON IGUALES, YA QUE AUN NO HAY VARIOS proveedores en la plataforma de una misma org.

            $org_supp = DB::table('services')
                ->join('supplier_proposals', 'supplier_proposals.service_id', 'services.id')
                ->join('supplier_ratings', 'services.id', 'supplier_ratings.service_id')
                ->select(

                    DB::raw('AVG(supplier_ratings.rating) as rate')
                )
                ->where([
                    ['services.status', '=', '1'],
                    ['services.step_id', '=', 7],
                    ['supplier_proposals.user_id', '=', $id],
                    ['supplier_proposals.is_winner', '=', '1'],

                ])
                ->first();

            $user_supp = DB::table('services')
                ->join('supplier_proposals', 'supplier_proposals.service_id', 'services.id')
                ->join('supplier_ratings', 'services.id', 'supplier_ratings.service_id')
                ->select(

                    DB::raw('AVG(supplier_ratings.rating) as rate')
                )
                ->where([
                    ['services.status', '=', '1'],
                    ['services.step_id', '=', 7],
                    ['supplier_proposals.user_id', '=', $id],
                    ['supplier_proposals.is_winner', '=', '1'],

                ])
                ->first();

            $org_rate = round($org_supp->rate, 2);
            $user_rate = round($user_supp->rate, 2);
        }


        return response()->json([
            'user_id' => $info->id,
            'name' => $info->name,
            'role_id' => $info->role_id,
            'organization_id' => $info->organization_id,
            'email' => $info->email,
            'phone' => $info->phone,
            'logo' => $info->logo,
            'org' => $info->org,
            'rfc' => $info->rfc,
            'colonie' => $info->colonie,
            'CP' => $info->CP,
            'city' => $info->city,
            'state' => $info->state,
            'country' => $info->country,
            'org_rate' => $org_rate,
            'user_rate' => $user_rate
        ]);
    }

    public function createLocation(Request $request)
    {
        $user = Auth::user();
        $role = Auth::user()->role_id;

        $location = Location::create([
            'organization_id' => $user->organization_id,
            'name' => $request->nombre,
            'colony_id' => $request->colonia,
            'street' => $request->calle,
            'internal_number' => $request->numero_interior,
            'external_number' => $request->numero_exterior,
            'type' => 1,
            'status' => 1,
        ]);
        return response()->json($location);
    }
}
