<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EpnoPart;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintClientToEpnoEvidence;
use App\Models\ComplaintEpnoToSupplierEvidence;
use App\Models\ComplaintLog;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Subservice;
use App\Models\SubserviceLog;
use App\MroPart;
use App\MroRequest;
use App\Models\User;
use App\Models\Valuestream;
use App\Models\VsUser;
use App\Models\PartCategory;
use App\Models\PartNo;
use App\Models\Role;
use App\Models\SupplierProposalLog;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\RequestFollowup;
use App\Models\SupplierProposal;
use App\Models\Notification;
use App\Models\OrganizationCategory;
use App\Models\ServiceLog;
use App\Models\SupplierProposalComplaint;
use App\Models\SupplierProposalComplaintLog;
use App\Notifications\ComplaintDetailsNotification;
use App\Notifications\GeneralNotification;
use App\Notifications\OrderDetailsNotification;
use App\OrderFiles;
use App\RequestFollowupLogs;
use Illuminate\Support\Facades\Notification as Notify;
// use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Arr;

// use Illuminate\Support\Facades\App;


class AgentController extends Controller
{

    public function GetEpnoParts()
    {
        $parts = EpnoPart::all()->where('status', 1);
        // dd($parts);
        return response()->json($parts);
    }
    public function GetPartnos()
    {
        $parts = PartNo::where('part_category_id', null)->where('epno_part_id', null)->get();
        // dd($parts);
        return response()->json($parts);
    }
    public function SendEpnoPart(Request $request)

    {
        $request->validate([
            'epno_id' => ['required'],
            'part_category_id' => ['required'],
            'partno_id' => ['required'],
        ]);

        $partno = PartNo::where('id', $request->partno_id)->update(['part_category_id' => $request->part_category_id, 'epno_part_id' => $request->epno_id]);
        if ($partno) {
            $response['message'] = "Actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetNewUsersRequest()
    {
        $newUsers = DB::table('users')
            ->join('organizations', 'users.organization_id', 'organizations.id')
            ->join('colonies', 'organizations.colony_id', 'colonies.id')
            ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
            ->join('cities', 'postal_codes.city_id', 'cities.id')
            ->join('states', 'cities.state_id', 'states.id')
            ->join('regions', 'states.region_id', 'regions.id')
            ->join('countries', 'regions.country_id', 'countries.id')
            ->select(
                'users.*',
                'users.id as user_id',
                'users.name as user',
                'organizations.*',
                'colonies.name as Colonia',
                'postal_codes.name as Codigo_postal',
                'cities.name as Ciudad',
                'states.name as Estado',
                'regions.name as Region',
                'countries.name as Pais',
            )
            ->where([['users.status', 2], ['organizations.status', 2]])
            ->get();
        return response()->json($newUsers);
    }
    // public function GetNewUsersRequestSupplier()
    // {
    //     $newUsersOrgClient = DB::table('users')
    //         ->join('organizations', 'users.organization_id', 'organizations.id')
    //         ->join('colonies', 'organizations.colony_id', 'colonies.id')
    //         ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
    //         ->join('cities', 'postal_codes.city_id', 'cities.id')
    //         ->join('states', 'cities.state_id', 'states.id')
    //         ->join('regions', 'states.region_id', 'regions.id')
    //         ->join('countries', 'regions.country_id', 'countries.id')
    //         ->join('locations', 'locations.organization_id', 'organizations.id')
    //         ->join('colonies as loc_colonies', 'locations.colony_id', 'loc_colonies.id')
    //         ->join('postal_codes as loc_postal_codes', 'loc_colonies.postal_code_id', 'loc_postal_codes.id')
    //         ->join('cities as loc_cities', 'loc_postal_codes.city_id', 'loc_cities.id')
    //         ->join('states as loc_states', 'loc_cities.state_id', 'loc_states.id')
    //         ->join('regions as loc_regions', 'loc_states.region_id', 'loc_regions.id')
    //         ->join('countries as loc_countries', 'loc_regions.country_id', 'loc_countries.id')
    //         ->select(
    //             'users.*',
    //             'users.id as user_id',
    //             'users.name as user',
    //             'organizations.*',
    //             'colonies.name as Colonia',
    //             'postal_codes.name as Codigo_postal',
    //             'cities.name as Ciudad',
    //             'states.name as Estado',
    //             'regions.name as Region',
    //             'countries.name as Pais',
    //             'locations.name as loc_name',
    //             'locations.default as loc_default',
    //             'locations.street as loc_street',
    //             'locations.external_number as loc_external_number',
    //             'locations.internal_number as loc_internal_number',
    //             'locations.type',
    //             'loc_colonies.name as loc_Colonia',
    //             'loc_postal_codes.name as loc_Codigo_postal',
    //             'loc_cities.name as loc_Ciudad',
    //             'loc_states.name as loc_Estado',
    //             'loc_regions.name as loc_Region',
    //             'loc_countries.name as loc_Pais'
    //         )
    //         ->where([['users.status', 2], ['locations.default', 1], ['users.role_id', 7]])
    //         ->get();
    //     // dd($newUsersOrg);
    //     return response()->json($newUsersOrgClient);
    // }

    public function ResponseNewUserRequest(Request $request)
    {
        return $request;
        if ($request->response == 1 && $request->role == 8) {
            VsUser::create(['user_id' => $request->user, 'vs_id' => $request->vs]);
        }

        if ($request->role == 7) {
            $resp = DB::table('users')
                ->join('organizations', 'users.organization_id', 'organizations.id')
                ->where('users.id', $request->user)
                ->update(['users.status' => $request->response, 'users.role_id' => 6, 'organizations.status' => $request->response]);

            foreach ($request->categoria as $cgy) {
                OrganizationCategory::create([
                    'organization_id' => $request->org,
                    'category_id' => $cgy,
                ]);
            }
        } else {
            $resp = DB::table('users')
                ->join('organizations', 'users.organization_id', 'organizations.id')
                ->where('users.id', $request->user)
                ->update(['users.status' => $request->response, 'users.role_id' => 4, 'organizations.status' => $request->response]);
        }



        if ($resp) {
            Notify::route('mail', $request->email)
                ->notify(new GeneralNotification($request->response));

            $response['message'] = "Actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetNewUserRequestNotification()
    {
        $notification = DB::table('users')
            ->join('organizations', 'users.organization_id', 'organizations.id')

            ->select(
                'users.name',
                'organizations.name as org'
            )
            ->where([['users.status', 2], ['organizations.status', 2]])
            ->get();
        $countNotif = $notification->count();

        return response()->json(['userNotification' => $notification, 'countNotification' => $countNotif]);
    }
    public function AddCategory(Request $request)
    {
        // return $request;
        $category = PartCategory::where('name', $request->name)->first();

        if ($category) {
            $response['existe_paquete'] = true;
            return $response;
        } else {
            $file = $request->file('image');
            $originalname = $file->getClientOriginalName();
            $pathLogo = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urllogo = Storage::url($pathLogo);

            $newcategory = new PartCategory();
            $newcategory->name = $request->input('name');
            $newcategory->image = $urllogo;

            if ($newcategory->save()) {
                $response['message'] = "Guardado correctamente";
                $response['success'] = true;
                return $response;
            } else {
                $response['message'] = "Error al guardar";
                $response['success'] = false;
                return $response;
            }
        }
    }
    public function AddUnit(Request $request)
    {
        $req = $request->all();
        $unit = Unit::where('name', $req['name'])->first();

        if ($unit) {
            // dd('entro en ya existia unidad');
            $response['existe_paquete'] = true;
            return $response;
        } else {
            // dd('entro en crear nuevo unidad');
            $newunit = new Unit();
            $newunit->name = $request->input('name');
            if ($newunit->save()) {
                $response['message'] = "Guardado correctamente";
                $response['success'] = true;
                return $response;
            } else {
                $response['message'] = "Error al guardar";
                $response['success'] = false;
                return $response;
            }
        }
    }
    public function GetCategories(Request $request)
    {
        $categories = PartCategory::all();
        return response()->json($categories);
    }
    public function GetUnits(Request $request)
    {
        $units = Unit::all();
        return response()->json($units);
    }
    public function AddPartnos(Request $request)
    {
        try {
            $partnum = EpnoPart::where('name', '=', $request->nombre)->first();
            // return $partnum;
            $request->validate([
                'nombre' => ['required', 'string', 'max:255'],
                'partno' => ['required', 'string',  'max:255'],
                'categoria' => ['required'],
                'unidad' => ['required'],
                'description' => ['required', 'string'],
                'precio' => ['required'],
            ]);
            if ($partnum != []) {
                $response['existe_partname'] = true;
                return $response;
            } else {

                $file = $request->file('myFile');
                $originalname = $file->getClientOriginalName();
                $pathLogo = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $urllogo = Storage::url($pathLogo);

                $part = new EpnoPart();
                $part->name = $request->input('nombre');
                $part->part_no = $request->input('partno');
                $part->unit_id = $request->input('unidad');;
                $part->description = $request->input('description');;
                $part->part_category_id = $request->input('categoria');
                $part->price = $request->input('precio');
                $part->image = $urllogo;

                if ($part->save()) {
                    $response['message'] = "Guardado correctamente";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "Error al guardar";
                    $response['success'] = false;
                    return $response;
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $response['existe_partnumber'] = true;
            return $response;
        }
    }

    public function ordenesTransitoTotal(Request $request)
    {
        $user = Auth::user();
        if ($user->role_id == 1 || $user->role_id == 2) {
            $request_follow_up = RequestFollowup::where('status', '1')->count();
        } else if ($user->role_id == 3 || $user->role_id == 5) {

            $request = DB::table('request_followups')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->select('request_types.name as name', 'request_types.id')
                ->where('request_followups.status', 1)
                ->groupBy('request_followups.request_type_id')
                ->get();

            $collection = collect();
            // $request_follow_up = array();
            foreach ($request as $req) {
                $table = $req->name . '_requests';

                $order = DB::table('request_followups')
                    ->join($table, 'request_followups.request_id', $table . '.id')
                    ->join('users', 'users.id', $table . '.user_id')
                    ->select(
                        'request_followups.id'
                        //   DB::raw('count(request_followups.id) as count')
                    )
                    ->where([
                        ['request_followups.status', '=', '1'],
                        [$table . '.status', '=', '1'],
                        ['users.organization_id', '=', $user->organization_id],

                    ])
                    ->orderBy('request_followups.created_at', 'desc')
                    ->get();

                if (!$order->isEmpty()) {
                    $collection->push($order);
                    // array_push($request_follow_up, $order);
                }
            }
            $newArr = $collection->collapse();
            $request_follow_up =   $newArr->count();
        }
        return response()->json($request_follow_up);
    }

    public function totalUsuariosAgent()
    {
        $usuarios = DB::table('users')
            ->where('status', 1)
            ->count()
            //->get()
        ;

        //dd($usuarios);
        return response()->json($usuarios);
    }
    public function productosServiciosAgent()
    {

        $service = DB::table('request_followups')
            ->join('service_requests', 'request_followups.request_id', 'service_requests.id')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select(
                'request_followups.id',
                'request_followups.request_id',
                'request_followups.request_type_id',
                'service_requests.user_id as user',
                'service_requests.title as titulo',
                'request_types.name as tipo',
                DB::raw('COUNT(service_requests.title) as count'),
                DB::raw('TRUNCATE(SUM(IFNULL(service_requests.final_cost,0)),2) as gasto')
            )
            ->where('request_followups.status', 1)
            ->groupBy("service_requests.title")
            // ->get()
        ;

        $mro = DB::table('request_followups')
            ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
            ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
            ->select(
                'request_followups.id',
                'request_followups.request_id',
                'request_followups.request_type_id',
                'mro_requests.user_id as user',
                'epno_parts.name as titulo',
                'request_types.name as tipo',
                DB::raw('COUNT(mro_requests.id) as count'),
                DB::raw('TRUNCATE(SUM(IFNULL(mro_requests.final_cost,0)),2) as gasto')
            )
            ->where('request_followups.status', 1)
            ->where('request_followups.request_type_id', 1)
            ->groupBy('epno_parts.name')
            // ->get();
            ->union($service);

        $productos = DB::query()->fromSub($mro, 'sr_mr')
            ->select(
                'id',
                'request_id',
                'user',
                'titulo as name',
                'tipo',
                'count as ordenes',
                'gasto as suma',
            )
            ->orderBy('count', 'DESC')
            ->take(5)
            ->get();

        return response()->json($productos);
    }
    public function usuariosActivosAgent()
    {
        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select(
                'service_requests.id as id',
                'users.role_id',
                'users.id as id_user',
                'users.name as name',
                'service_requests.final_cost'
            )
            ->where('service_requests.status', 1);

        $ordenSerMro = DB::table('mro_requests')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->select(
                'mro_requests.id as id',
                'users.role_id',
                'users.id as id_user',
                'users.name as name',
                'mro_requests.final_cost'
            )
            ->where('mro_requests.status', 1)
            ->union($ordenService)
            //->get()
        ;

        $usuActivos = DB::query()->fromSub($ordenSerMro, 'sr_mr')
            ->select(
                'id',
                'name',
                'id_user',
                'role_id',
                DB::raw('COUNT(id) as ordenes'),
                DB::raw('TRUNCATE(SUM(IFNULL(final_cost,0)),2) as suma')
            )
            ->groupBy('name')
            ->orderBy('ordenes', 'DESC')
            ->take(5)
            ->get();

        //dd($usuActivos);
        return response()->json($usuActivos);
    }
    public function profileLocationsAgent($id)
    {
        // $org = DB::table('users')->select('organization_id')->where('id',$id)->get();
        $org = User::where('id', $id)->first();

        $locations = DB::table('locations')
            ->join('colonies', 'locations.colony_id', 'colonies.id')
            ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
            ->join('cities', 'postal_codes.city_id', 'cities.id')
            ->join('states', 'cities.state_id', 'states.id')
            ->join('regions', 'states.region_id', 'regions.id')
            ->join('countries', 'regions.country_id', 'countries.id')
            ->select(
                'locations.id',
                'locations.name',
                'colonies.name as colonie',
                'postal_codes.name as CP',
                'cities.name as city',
                'states.name as state',
                'countries.name as country'
            )
            ->where([['locations.status', 1], ['locations.organization_id', $org->organization_id]])
            ->get();

        // dd($locations);
        return response()->json($locations);
    }
    public function reviewsAgent()
    {
        $id = Auth::user()->id;

        $rating = DB::table('ratings')
            ->join('request_followups', 'ratings.request_followup_id', 'request_followups.id')
            ->select(
                'ratings.id',
                'ratings.request_followup_id',
                'ratings.client_to_agent_comment',
                'request_followups.purchase_order'
            )
            ->where('request_followups.status', 1)
            ->get();

        //dd($rating);
        return response()->json($rating);
    }

    public function ordenesPerfil(Request $request)
    {
        $orga = Auth::user()->organization_id;
        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select('users.id', 'service_requests.final_cost', 'users.name', 'users.role_id')
            ->where([
                ['users.status', 1],
                ['service_requests.status', 1],
                ['users.organization_id', $orga]
            ]);

        $ordenSerMro = DB::table('mro_requests')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->select('users.id', 'mro_requests.final_cost', 'users.name', 'users.role_id')
            ->where([
                ['users.status', 1],
                ['mro_requests.status', 1],
                ['users.organization_id', $orga]
            ])
            ->union($ordenService);

        $costo = DB::query()->fromSub($ordenSerMro, 'sr_mr')
            ->select('id as id_user', 'role_id', 'name', DB::raw('SUM(IFNULL(final_cost,0)) as suma'), DB::raw('COUNT(id) as ordenes'))
            ->groupBy('id')
            ->get();

        //dd($costo);
        return response()->json($costo);
    }


    public function gastosPerfil()
    {
        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select('users.id', 'service_requests.final_cost', 'users.name', 'service_requests.created_at');

        $ordenSerMro = DB::table('mro_requests')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->select('users.id', 'mro_requests.final_cost', 'users.name', 'mro_requests.created_at')
            ->union($ordenService);

        $gastos = DB::query()->fromSub($ordenSerMro, 'sr_mr')
            ->select(
                'id',
                DB::raw('COUNT(id) as total_reg'),
                DB::raw('TRUNCATE(SUM(final_cost), 2) as costo'),
                DB::raw('TRUNCATE(AVG(final_cost), 2) as promedio'),
                DB::raw('TRUNCATE(SUM(final_cost) - AVG(final_cost), 2) as dif'),
                DB::raw("DATE_FORMAT(created_at,'%M') as month"),
                DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                'created_at'
            )
            ->groupBy('month', 'year')
            ->get();

        // dd($gastos);
        return response()->json($gastos);
    }
    public function consumoClientesAgent()
    {
        $role = Auth::user()->role_id;
        $org = Auth::user()->organization_id;

        if ($role == 1) {

            $ordenService = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('users.id', 'service_requests.final_cost', 'users.name', 'service_requests.created_at')
                ->where([['service_requests.status', 1], ['users.role_id', 3]])
                ->orWhere('users.role_id', 4);

            $ordenSerMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('users.id', 'mro_requests.final_cost', 'users.name', 'mro_requests.created_at')
                ->where([['mro_requests.status', 1], ['users.role_id', 3]])
                ->orWhere('users.role_id', 4)
                ->union($ordenService);

            $gastos1 = DB::query()->fromSub($ordenSerMro, 'sr_mr')
                ->select(
                    'id',
                    DB::raw('COUNT(id) as total_reg'),
                    DB::raw('SUM(final_cost) as suma'),
                    DB::raw('AVG(final_cost) as promedio'),
                    DB::raw('SUM(final_cost) - AVG(final_cost) as dif'),
                    DB::raw("DATE_FORMAT(created_at,'%M') as months"),
                    DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                    'created_at'
                )
                ->groupBy('months', 'year')
                ->get();

            // dd($gastos);
            return response()->json($gastos1);
        } else {

            $ordenService = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('users.id', 'service_requests.final_cost', 'users.name', 'service_requests.created_at')
                ->where('users.organization_id', $org)
                ->where('users.role_id', 3)
                ->orWhere('users.role_id', 4);

            $ordenSerMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('users.id', 'mro_requests.final_cost', 'users.name', 'mro_requests.created_at')
                ->where('users.organization_id', $org)
                ->where('users.role_id', 3)
                ->orWhere('users.role_id', 4)
                ->union($ordenService)
                //->get()
            ;

            $gastos2 = DB::query()->fromSub($ordenSerMro, 'sr_mr')
                ->select(
                    'id',
                    DB::raw('COUNT(id) as total_reg'),
                    DB::raw('SUM(final_cost) as suma'),
                    DB::raw('AVG(final_cost) as promedio'),
                    DB::raw('SUM(final_cost) - AVG(final_cost) as dif'),
                    DB::raw("DATE_FORMAT(created_at,'%M') as months"),
                    DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                    'created_at'
                )
                ->groupBy('months', 'year')
                ->get();

            return response()->json($gastos2);
        }
    }
    public function consumoSupplierAgent()
    {
        $role = Auth::user()->role_id;
        $org = Auth::user()->organization_id;


        if ($role == 1) {
            $ordenService = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('users.id', 'service_requests.final_cost', 'users.name', 'service_requests.created_at')
                ->where([['service_requests.status', 1], ['users.role_id', 6]])
                ->orWhere('users.role_id', 7);

            $ordenSerMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('users.id', 'mro_requests.final_cost', 'users.name', 'mro_requests.created_at')
                ->where([['mro_requests.status', 1], ['users.role_id', 6]])
                ->orWhere('users.role_id', 7)
                ->union($ordenService);

            $gastos = DB::query()->fromSub($ordenSerMro, 'sr_mr')
                ->select(
                    'id',
                    DB::raw('COUNT(id) as total_reg'),
                    DB::raw('SUM(final_cost) as suma'),
                    DB::raw('AVG(final_cost) as promedio'),
                    DB::raw('SUM(final_cost) - AVG(final_cost) as dif'),
                    DB::raw("DATE_FORMAT(created_at,'%M') as months"),
                    DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                    'created_at'
                )
                ->groupBy('months', 'year')
                ->get();

            // dd($gastos);
            return response()->json($gastos);
        } else {
            $ordenService = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('users.id', 'service_requests.final_cost', 'users.name', 'service_requests.created_at')
                ->where('users.organization_id', $org)
                ->where('users.role_id', 6)
                ->orWhere('users.role_id', 7);

            $ordenSerMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('users.id', 'mro_requests.final_cost', 'users.name', 'mro_requests.created_at')
                ->where('users.organization_id', $org)
                ->where('users.role_id', 6)
                ->orWhere('users.role_id', 7)
                ->union($ordenService);

            $gastos2 = DB::query()->fromSub($ordenSerMro, 'sr_mr')
                ->select(
                    'id',
                    DB::raw('COUNT(id) as total_reg'),
                    DB::raw('SUM(final_cost) as suma'),
                    DB::raw('AVG(final_cost) as promedio'),
                    DB::raw('SUM(final_cost) - AVG(final_cost) as dif'),
                    DB::raw("DATE_FORMAT(created_at,'%M') as months"),
                    DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                    'created_at'
                )
                ->groupBy('months', 'year')
                ->get();

            //dd($gastos2);
            return response()->json($gastos2);
        }
    }

    // public function GetOpenOrders($type)
    // {
    //     // productosServiciosAgent
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->groupBy('request_followups.request_type_id')
    //         ->get();
    //     $arrayOrder = array();
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         // dd($table);
    //         if ($type == 1) {
    //             $order = DB::select('SELECT
    //             a.*,
    //             a.id,
    //             a.purchase_order as po,
    //             a.created_at as inicio,
    //             g.name as estatus,
    //             b.user_id as usuario,
    //             b.title as titulo,
    //             b.total_days as dias,
    //             b.description as descripcion,
    //             f.name as tipo,
    //             e.name as categoria,
    //             d.name as org,
    //             if((select true from request_followup_logs where status = true and request_followup_id = a.id and step_id = 3 limit 1), (select created_at from request_followup_logs where step_id = 3 and status = true and request_followup_id = a.id limit 1), null) as change_step
    //         FROM
    //             request_followups AS a INNER JOIN
    //             ' . $table . ' AS b ON a.request_id = b.id INNER JOIN
    //             users AS c ON b.user_id = c.id INNER JOIN
    //             organizations AS d ON c.organization_id = d.id INNER JOIN
    //             service_categories AS e ON b.service_category_id = e.id INNER JOIN
    //             request_types AS f ON f.id = a.request_type_id INNER JOIN
    //             steps AS g ON g.id = a.step_id
    //         WHERE
    //             a.status = true AND
    //             b.status = true AND
    //             f.status = true AND
    //             g.status = true AND
    //             a.request_type_id = ' . $req->id . ' AND
    //             a.step_id BETWEEN 1 AND 5 OR
    //             a.step_id=9

    //         ORDER BY
    //             a.created_at DESC');
    //             // dd($order);
    //             // $order = DB::table('request_followups')
    //             //     ->join($table, 'request_followups.request_id', $table . '.id')
    //             //     ->join('users', 'users.id', $table . '.user_id')
    //             //     ->join('organizations', 'users.organization_id', 'organizations.id')
    //             //     // ->join('request_followup_logs', 'request_followup_logs.request_followup_id', 'request_followups.id')
    //             //     ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //             //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             //     // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             //     ->join('steps', 'request_followups.step_id', 'steps.id')
    //             //     ->select(
    //             //         'request_followups.*',
    //             //         $table . '.user_id as user',
    //             //         $table . '.title as titulo',
    //             //         $table . '.total_days as dias',
    //             //         $table . '.description as descripcion',
    //             //         'request_types.name as tipo',
    //             //         'service_categories.name as categoria',
    //             //         'organizations.name as org',

    //             //     )
    //             //     ->where([
    //             //         ['request_followups.status', '=', '1'],
    //             //         // ['request_followup_logs.step_id', '=', '3'],
    //             //         [$table . '.status', '=', '1'],
    //             //         // [$table . '.user_id', '=', $user->id],
    //             //         ['request_types.status', '=', '1'],
    //             //         // ['supplier_proposals.status', '=', '1'],
    //             //         ['steps.status', '=', '1'],
    //             //         ['request_followups.request_type_id', $req->id],
    //             //     ])
    //             //     ->whereBetween('request_followups.step_id', [1, 5])
    //             //     ->orWhere('request_followups.step_id', 9)
    //             //     ->orderBy('request_followups.created_at', 'desc')
    //             //     // ->groupBy('request_followups.id')
    //             //     ->get();
    //         } elseif ($type == 0) {
    //             $order = DB::select('SELECT
    //             a.*,
    //             a.id,
    //             a.purchase_order as po,
    //             a.created_at as inicio,
    //             g.name as estatus,
    //             b.user_id as usuario,
    //             b.title as titulo,
    //             b.total_days as dias,
    //             b.description as descripcion,
    //             f.name as tipo,
    //             e.name as categoria,
    //             d.name as org,
    //             if((select true from request_followup_logs where status = true and request_followup_id = a.id and step_id = 3 limit 1), (select created_at from request_followup_logs where step_id = 3 and status = true and request_followup_id = a.id limit 1), null) as change_step
    //         FROM
    //             request_followups AS a INNER JOIN
    //             ' . $table . ' AS b ON a.request_id = b.id INNER JOIN
    //             users AS c ON b.user_id = c.id INNER JOIN
    //             organizations AS d ON c.organization_id = d.id INNER JOIN
    //             service_categories AS e ON b.service_category_id = e.id INNER JOIN
    //             request_types AS f ON f.id = a.request_type_id INNER JOIN
    //             steps AS g ON g.id = a.step_id
    //         WHERE
    //             a.status = true AND
    //             b.status = true AND
    //             f.status = true AND
    //             g.status = true AND
    //             a.request_type_id = ' . $req->id . ' AND
    //             a.step_id BETWEEN 6 AND 8          
    //         ORDER BY
    //             a.created_at DESC');
    //             // $order = DB::table('request_followups')
    //             //     ->join($table, 'request_followups.request_id', $table . '.id')
    //             //     ->join('users', 'users.id', $table . '.user_id')
    //             //     ->join('organizations', 'users.organization_id', 'organizations.id')
    //             //     ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //             //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             //     // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             //     ->join('steps', 'request_followups.step_id', 'steps.id')
    //             //     ->select(
    //             //         'request_followups.*',
    //             //         $table . '.user_id as user',
    //             //         $table . '.title as titulo',
    //             //         $table . '.total_days as dias',
    //             //         $table . '.description as descripcion',
    //             //         'request_types.name as tipo',
    //             //         'service_categories.name as categoria',
    //             //         'organizations.name as org'

    //             //     )
    //             //     ->where([
    //             //         ['request_followups.status', '=', '1'],
    //             //         [$table . '.status', '=', '1'],
    //             //         // [$table . '.user_id', '=', $user->id],
    //             //         ['request_types.status', '=', '1'],
    //             //         // ['supplier_proposals.status', '=', '1'],
    //             //         ['steps.status', '=', '1'],
    //             //         ['request_followups.request_type_id', $req->id],
    //             //     ])
    //             //     ->whereBetween('request_followups.step_id', [6, 8])
    //             //     ->orderBy('request_followups.created_at', 'desc')
    //             //     ->get();
    //         } elseif ($type == 2) {
    //             $order = DB::select('SELECT
    //             a.*,
    //             a.id,
    //             a.purchase_order as po,
    //             a.created_at as inicio,
    //             g.name as estatus,
    //             b.user_id as usuario,
    //             b.title as titulo,
    //             b.total_days as dias,
    //             b.description as descripcion,
    //             f.name as tipo,
    //             e.name as categoria,
    //             d.name as org,
    //             if((select true from request_followup_logs where status = true and request_followup_id = a.id and step_id = 3 limit 1), (select created_at from request_followup_logs where step_id = 3 and status = true and request_followup_id = a.id limit 1), null) as change_step
    //         FROM
    //             request_followups AS a INNER JOIN
    //             ' . $table . ' AS b ON a.request_id = b.id INNER JOIN
    //             users AS c ON b.user_id = c.id INNER JOIN
    //             organizations AS d ON c.organization_id = d.id INNER JOIN
    //             service_categories AS e ON b.service_category_id = e.id INNER JOIN
    //             request_types AS f ON f.id = a.request_type_id INNER JOIN
    //             steps AS g ON g.id = a.step_id
    //         WHERE
    //             a.status = true AND
    //             b.status = true AND
    //             f.status = true AND
    //             g.status = true AND
    //             a.request_type_id = ' . $req->id . ' ORDER BY a.created_at DESC');
    //             // $order = DB::table('request_followups')
    //             //     ->join($table, 'request_followups.request_id', $table . '.id')
    //             //     ->join('users', 'users.id', $table . '.user_id')
    //             //     ->join('organizations', 'users.organization_id', 'organizations.id')
    //             //     ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //             //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             //     // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             //     ->join('steps', 'request_followups.step_id', 'steps.id')
    //             //     ->select(
    //             //         'request_followups.*',
    //             //         $table . '.user_id as user',
    //             //         $table . '.title as titulo',
    //             //         $table . '.total_days as dias',
    //             //         $table . '.description as descripcion',
    //             //         'request_types.name as tipo',
    //             //         'service_categories.name as categoria',
    //             //         'organizations.name as org'

    //             //     )
    //             //     ->where([
    //             //         ['request_followups.status', '=', '1'],
    //             //         [$table . '.status', '=', '1'],
    //             //         // [$table . '.user_id', '=', $user->id],
    //             //         ['request_types.status', '=', '1'],
    //             //         // ['supplier_proposals.status', '=', '1'],
    //             //         ['steps.status', '=', '1'],
    //             //         ['request_followups.request_type_id', $req->id],
    //             //     ])
    //             //     ->orderBy('request_followups.created_at', 'desc')
    //             //     ->get();
    //         }

    //         // if (!$order->isEmpty()) {
    //         array_push($arrayOrder, $order);
    //         // }
    //     }

    //     if ($arrayOrder !== null && $arrayOrder !== []) {

    //         return response()->json($arrayOrder[0]);
    //     } else {
    //         return response()->json($arrayOrder);
    //     }
    // }
    // public function GetOpenOrdersMro($type)
    // {


    //     if ($type == 1) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_followup_logs', 'request_followup_logs.request_followup_id', 'request_followups.id')
    //             ->join('users', 'users.id', 'mro_requests.user_id')
    //             ->join('organizations', 'users.organization_id', 'organizations.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'supplier_proposals.total_days as dias',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo',
    //                 'organizations.name as org',
    //                 'request_followup_logs.created_at as change_step'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 // [$table . '.user_id', '=', $user->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //                 ['request_followups.request_type_id', 1],
    //             ])
    //             ->whereBetween('request_followups.step_id', [1, 5])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 0) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_followup_logs', 'request_followup_logs.request_followup_id', 'request_followups.id')
    //             ->join('users', 'users.id', 'mro_requests.user_id')
    //             ->join('organizations', 'users.organization_id', 'organizations.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'supplier_proposals.total_days as dias',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo',
    //                 'organizations.name as org',
    //                 'request_followup_logs.created_at as change_step'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 // [$table . '.user_id', '=', $user->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //                 ['request_followups.request_type_id', 1],
    //             ])
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 2) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_followup_logs', 'request_followup_logs.request_followup_id', 'request_followups.id')
    //             ->join('users', 'users.id', 'mro_requests.user_id')
    //             ->join('organizations', 'users.organization_id', 'organizations.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'supplier_proposals.total_days as dias',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo',
    //                 'organizations.name as org',
    //                 'request_followup_logs.created_at as change_step'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 // [$table . '.user_id', '=', $user->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //                 ['request_followups.request_type_id', 1],
    //             ])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     }

    //     return response()->json($order);
    // }

    // public function GetCloseOrders(Request $request)
    // {
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_followups.request_id as request_id', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->get();

    //     $arrayOrder = array();
    //     $title = 'request_types.name as titulo';
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         $req_id = $req->request_id;

    //         $order = DB::table('request_followups')
    //             ->join($table, 'request_followups.request_id', $table . '.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 $table . '.user_id as user',
    //                 $table . '.title as titulo',
    //                 $table . '.total_days as dias',
    //                 $table . '.description as descripcion',
    //                 'request_types.name as tipo'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 [$table . '.status', '=', '1'],
    //                 // [$table . '.user_id', '=', $user->id],
    //                 [$table . '.id', '=', $req_id],
    //                 ['request_followups.request_type_id', $req->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             // ->whereBetween('request_followups.step_id', [6, 8])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order);
    //         }
    //     }

    //     return response()->json($arrayOrder);
    // }
    // public function GetCloseOrdersMro(Request $request)
    // {
    //     $order = DB::table('request_followups')
    //         ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select('request_followups.*', 'mro_requests.user_id as user', 'request_types.name as titulo', 'request_types.name as tipo')
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             // ['request_followups.step_id', '>=', '1','<=','5'],
    //             ['mro_requests.status', '=', '1'],
    //             // [$table . '.user_id', '=', $user->id],
    //             ['request_types.status', '=', '1'],
    //             // ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],
    //             ['request_followups.request_type_id', 1],
    //         ])
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->groupBy('request_followups.request_id')
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();

    //     return response()->json($order);
    // }
    public function AddCotizacionMro(Request $request)
    {
        $user = Auth::user();
        $product_ar = array();
        if ($user) {
            // $cot_price = $request->cost_win * ((100 + $request->ganancia) / 100);
            // $neto = $request->qty * $cot_price;
            // $total = $neto + $precio_iva;
            $mro = MroRequest::where('id', $request->request_id)->first();
            $precio_iva = $mro->subtotal * ($mro->iva / 100);
            $products = MroPart::join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
                ->join('units', 'epno_parts.unit_id', 'units.id')
                ->where('mro_parts.mro_request_id', $request->request_id)
                ->where('mro_parts.status', 0)->select('mro_parts.*', 'epno_parts.name', 'units.name as um')->get();


            foreach ($products as $p) {
                $product_array = [
                    'precio_unitario' => $p->part_cost,
                    // 'codigo' => $request->codigo,
                    'descripcion' => $p->name,
                    'qty' => $p->qty,
                    'um' => $p->um,
                ];
                array_push($product_ar, $product_array);
            }
            // return $product_ar;

            $data = [
                'org' => $request->org,
                'client_name' => $request->user_name,
                'iva' => $mro->iva,
                'precio_iva' => $precio_iva,
                'user_name' => $user->name,
                'fecha_entrega' => Carbon::now()->add($request->time, 'day')->format('Y-m-d'),
                'dias_validos' => $request->condiciones_pago,
                'vigencia' => $request->vigencia,
                'total' => $mro->final_cost,
                'currency' => $request->currency,
                'purchase' => $request->purchase,
                'tipo_cambio' => $request->tipo_cambio,
                'date' => Carbon::now()->format('Y-m-d'),
                'final_cost' => $mro->subtotal,
                'products' => $product_ar,
            ];
            $fila_name = 'COT-' . $request->request_followup_id . '-' . $request->org . '.pdf';
            PDF::loadView('cotizacionEN', $data)
                ->save(storage_path('app/public/uploads/') . $fila_name);
            $url = '/storage/uploads//' . $fila_name;


            $cotizacion = RequestFollowup::where('id', $request->request_followup_id)->update(['epno_cot_file' => $url]);


            if ($cotizacion) {
                $response['success'] = true;
                $response['url'] = $url;
                return $response;
            } else {
                $response['success'] = false;
                return $response;
            }
        }
    }
    public function AddClientCot(Request $request)
    {
        // return $request;
        try {
            //code...

            $user = Auth::user();
            if ($user->role_id == 3 || $user->role_id == 5) {
                $product_array = array();
                // $neto = $request->service_info['client_subtotal'];
                // $precio_iva = $neto * ($request->iva / 100);
                // $total = $neto + $precio_iva;
                $neto = 0;
                $precio_iva = 0;
                $total = 0;

                foreach ($request->subservices as $sub) {
                    if ($sub['step_id'] !== 8) {

                        foreach ($sub['proposals'] as  $sp) {
                            $products = [
                                'precio_unitario' => $sp['epno_cost'],
                                'descripcion' => $sp['desc'],
                                'qty' => $sp['qty'],
                                'um' => $sub['unit_name'],
                            ];
                            if ($sp['epno_cost'] !== null && $sp['desc'] !== "" &&  $sp['deadline'] !== null) {

                                $neto = $neto + $sp['epno_cost'] * $sp['qty'];
                                $precio_iva = $neto * ($request->iva / 100);
                                $total = $neto + $precio_iva;
                                array_push($product_array, $products);
                            }
                        }
                    }
                }

                if ($product_array  !== []) {


                    $data = [
                        'org' => $request->client_info['org_name'],
                        'client_name' =>  $request->client_info['contact_name'],
                        'iva' => $request->iva,
                        'precio_iva' => $precio_iva,
                        'user_name' => $user->name,
                        'fecha_entrega' => $request->service_info['deadline'],
                        'dias_validos' => $request->condiciones_pago,
                        'vigencia' => $request->vigencia,
                        'total' => $total,
                        'purchase' => $request->service_info['order_num'],
                        'tipo_cambio' => $request->tipo_cambio,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'subtotal' => $neto,
                        'currency' => $request->currency,
                        'products' => $product_array,

                    ];
                    $fila_name =  $request->service_info['order_num'] . '-' . $request->client_info['org_name'] . '.pdf';
                    PDF::loadView('cotizacionEN', $data)
                        ->save(storage_path('app/public/uploads/') . $fila_name);
                    $url = '/storage/uploads//' . $fila_name;

                    $cotizacion = Order::where('id', $request->service_info['order_id'])
                        ->update([
                            'cot_date' => Carbon::now()->format('Y-m-d'),
                        ]);

                    if ($cotizacion) {
                        $response['success'] = true;
                        $response['message'] = "Archivo generado correctamente.";
                        $response['url'] = $url;
                        return $response;
                    } else {
                        $response['message'] = "Error al actualizar la fecha de cotización";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "No se pudo generar el pdf ya que no hay ninguna cotizacion aceptada o proveedores agregados.";
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['message'] = "No tienes los permisos para realizar esta acción";
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {
            //throw $th;
            $response['message'] = $th->getMessage();
            $response['success'] = false;
            return $response;
        }
    }
    public function GetCotizationFile($id)
    {
        $costoBajo = SupplierProposal::where('request_followup_id', $id)->min('cost');
        // dd($costoBajo);
        $cotFile = DB::table('request_followups')
            ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
            ->select('supplier_proposals.cotization_file as cotFile')
            ->where('supplier_proposals.cost', $costoBajo)
            ->where('supplier_proposals.request_followup_id', $id)
            ->get();

        if (!$cotFile->isEmpty()) {
            return response()->json($cotFile[0]);
        } else {
            $response['message'] = "No hay archivo de cotización";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetCotizationFiles($service, $id)
    {

        try {
            // if ($service == 'mro') {
            //     $cotFiles = RequestFollowup::where('id', $id)->first();
            //     if ($cotFiles->client_po_file !== null && $cotFiles->supplier_po_file !== null) {
            //         return response()->json([$cotFiles->client_po_file, $cotFiles->supplier_po_file]);
            //     } elseif ($cotFiles->client_po_file !== null) {
            //         return response()->json([$cotFiles->client_po_file]);
            //     } elseif ($cotFiles->supplier_po_file !== null) {
            //         return response()->json([$cotFiles->supplier_po_file]);
            //     } else {
            //         $response['success'] = false;
            //         return $response;
            //     }
            //     return $cotFiles;
            // } else {

            // $cotFileRequest = DB::table('request_followups')
            //     ->join($service . '_requests', 'request_followups.request_id', $service . '_requests.id')
            //     ->select($service . '_requests.specifications_file as cotFile')
            //     ->where('request_followups.id', $id)
            //     ->get();
            $cotFileRequest = OrderFiles::where('request_followup_id', $id)->get('file as cotFile');

            // dd($cotFileRequest);
            if (!$cotFileRequest->isEmpty()) {

                $costoBajo = SupplierProposal::where('request_followup_id', $id)->min('cost');
                if ($costoBajo !== null) {

                    $cotFileSupplier = DB::table('request_followups')
                        ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                        ->select('supplier_proposals.cotization_file as cotFile')
                        ->where('supplier_proposals.cost', $costoBajo)
                        ->where('supplier_proposals.request_followup_id', $id)
                        ->get();

                    // return response()->json($cotFileSupplier[0]->cotFile);

                    if (!$cotFileSupplier->isEmpty()) {
                        if ($cotFileSupplier[0]->cotFile !== null) {

                            $clientPOFile = RequestFollowup::where('id', $id)->get('client_po_file as cotFile');
                            if (!$clientPOFile->isEmpty()) {
                                if ($clientPOFile[0]->cotFile !== null) {
                                    $cotFileAgent = RequestFollowup::where('id', $id)->get('epno_cot_file as cotFile');
                                    if (!$cotFileAgent->isEmpty()) {
                                        if ($cotFileAgent[0]->cotFile !== null) {
                                            $poFileAgent = RequestFollowup::where('id', $id)->get('supplier_po_file as cotFile');
                                            if (!$poFileAgent->isEmpty()) {
                                                if ($poFileAgent[0]->cotFile !== null) {
                                                    return response()->json([$clientPOFile, $cotFileSupplier, $cotFileRequest, $cotFileAgent, $poFileAgent]);
                                                } else {
                                                    return response()->json([$clientPOFile, $cotFileSupplier, $cotFileRequest, $cotFileAgent]);
                                                }
                                            } else {
                                                return response()->json([$clientPOFile, $cotFileSupplier, $cotFileRequest, $cotFileAgent]);
                                            }
                                        } else {
                                            return response()->json([$clientPOFile, $cotFileSupplier, $cotFileRequest]);
                                        }
                                        // dd($cotFileSupplier[0], $cotFileRequest[0], $cotFileAgent[0]);
                                    } else {
                                        return response()->json([$clientPOFile, $cotFileSupplier, $cotFileRequest]);
                                    }
                                } else {
                                    return response()->json([$cotFileSupplier, $cotFileRequest]);
                                }
                            } else {
                                return response()->json([$cotFileSupplier, $cotFileRequest]);
                            }
                        } else {
                            return response()->json([$cotFileRequest]);
                        }
                    } else {
                        return response()->json([$cotFileRequest]);
                    }
                } else {
                    // $response['message'] = "No hay ningun proveedor asignado aun";
                    // $response['success'] = false;
                    // return $response;
                    return response()->json([$cotFileRequest]);
                }

                // dd($cotFileSupplier);

            } else {
                $response['message'] = "No hay archivo de especificaciones";
                $response['success'] = false;
                return $response;
            }
            // }
        } catch (\Illuminate\Database\QueryException $e) {
            $response['error'] = true;
            return $response;
        }
    }

    public function POEpnoCotFile($id)
    {
        $cotizacion = RequestFollowup::where('id', $id)->first('epno_cot_file');
        // dd($cotizacion[0]->epno_cot_file);
        if ($cotizacion->epno_cot_file !== null && $cotizacion->epno_cot_file !== '') {
            $response['existe_epno_cotF'] = true;
            return $response;
        } else {
            $response['existe_epno_cotF'] = false;
            return $response;
        }
    }

    public function SupplierPoFile($id)
    {
        $poFile = RequestFollowup::where('id', $id)->first('supplier_po_file');
        // dd($poFile ->supplier_po_file);
        if ($poFile->supplier_po_file !== null) {
            $response['poFile'] = $poFile->supplier_po_file;
            $response['existe_supPo_file'] = true;
            return $response;
        } else {
            $response['existe_supPo_file'] = false;
            return $response;
        }
    }

    public function SendPoToSupplier(Request $request)
    {
        try {

            $buyer = Auth::user();
            $products = array();
            if ($buyer->role_id == 3 || $buyer->role_id == 5) {
                if ($request->option == 1) {

                    $servicios = Service::join('subservices', 'services.id', 'subservices.service_id')
                        ->join('supplier_proposals', 'subservices.id', 'supplier_proposals.subservice_id')
                        ->join('users', 'users.id', 'supplier_proposals.user_id')
                        ->join('organizations', 'organizations.id', 'users.organization_id')
                        ->join('categories', 'subservices.category_id', 'categories.id')
                        ->join('units', 'subservices.unit_id', 'units.id')
                        ->select(
                            'services.description as service_desc',
                            'services.order_num',
                            'services.step_id as service_step',
                            'services.user_id as service_client',
                            'services.quote_file as epno_cot',
                            'subservices.*',
                            'subservices.qty as subservice_qty',
                            'supplier_proposals.*',
                            'supplier_proposals.id as supp_id',
                            'categories.name as category_name',
                            'units.name as unit_name',
                            'users.name as supplier_name',
                            'users.email as supplier_mail',
                            'organizations.name as org_name',
                        )
                        ->where([
                            ['services.id', $request->service['id']],
                            ['supplier_proposals.user_id', $request->supplier['user_id']],
                        ])
                        ->get();
                    if ($servicios) {
                        $response['success'] = true;
                        $response['message'] = "Servicios retornados correctamente.";
                        $response['services'] = $servicios;
                        return $response;
                    } else {
                        $response['success'] = false;
                        $response['message'] = "No se puedo realizar la acción.";
                        return $response;
                    }
                } else {
                    $finalcost = 0;

                    // return $request;
                    foreach ($request->subservices as $sub) {
                        $neto = $sub['qty'] * $sub['unitary_subtotal_cost'];
                        $finalcost = $finalcost + $neto;
                        $product_array = [

                            'net_value' => $neto,
                            'um' => $sub['unit_name'],
                            'precio_unitario' => $sub['unitary_subtotal_cost'],
                            'descripcion' => $sub['name'],
                            'qty' => $sub['qty'],

                        ];
                        array_push($products, $product_array);
                    }
                    $data = [
                        'seller' => $request->subservices[0]['org_name'],
                        'user_name' => $buyer->name,
                        'user_phone' => $buyer->phone,
                        'user_mail' => $buyer->email,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'ordering_address' => $request->supplier['ordering_address'],
                        'billing_address' => $request->supplier['billing_address'],
                        'dias_validos' => $request->supplier['condiciones_pago'],
                        'currency' => $request->supplier['currency'],
                        'delivery_address' => $request->supplier['delivery_address'],
                        'delivery_terms' => $request->supplier['delivery_terms'],
                        'shipping_inst' => $request->supplier['shipping_inst'],
                        'special_inst' => $request->supplier['special_inst'],
                        'purchase' => $request->service['order_num'],
                        'tipo_cambio' => $request->tipo_cambio,
                        'final_cost' => $neto,
                        'products' => $products,


                    ];
                    $fila_name = $request->service['order_num'] . '-' . $request->subservices[0]['org_name'] . '.pdf';
                    $pdf = PDF::loadView('poPdf', $data)
                        ->save(storage_path('app/public/uploads/') . $fila_name);
                    $url = '/storage/uploads//' . $fila_name;
                    if ($pdf) {
                        $response['success'] = true;
                        $response['url'] = $url;
                        $response['message'] = "La po ha sido generada correctamente.";
                        return $response;
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Hubo un error al intentar generar el archivo.";
                        return $response;
                    }
                }
            } else {
                $response['success'] = false;
                $response['message'] = "No cuentas con los permisos para realizar esta acción.";
                return $response;
            }
        } catch (\Throwable $th) {
            $response['success'] = false;
            $response['message'] = "Hubo un error al realizar la accion.";
            return $th;
        }
    }

    public function SubirPOGenerada(Request $request)
    {

        $user = Auth::user();

        if ($user->role_id == 3 || $user->role_id == 5) {
            $org = Organization::where('id', $user->organization_id)->first('name');
            $service = json_decode($request->service);
            $subservices = json_decode($request->subservices);
            // return $subservices;

            $file = $request->file('po');
            $originalname = $file->getClientOriginalName();
            $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlFile = Storage::url($pathFile);

            foreach ($subservices as $sub) {
                $upSupp = SupplierProposal::where('id', $sub->supp_id)
                    ->update(['epno_po_file' => $urlFile]);

                if ($upSupp) {

                    $notificationSupplier = new Notification();
                    $notificationSupplier->user_id = $sub->user_id;
                    $notificationSupplier->notification_type_id = 22;
                    $notificationSupplier->table_name = "services";
                    $notificationSupplier->table_id = $service->order_id;

                    if ($notificationSupplier->save()) {
                        DB::select('call limitNotificationCount (?)', array($sub->user_id));

                        Notify::route('mail', $sub->supplier_mail)
                            ->notify(new OrderDetailsNotification(
                                22,
                                6,
                                $service->order_id,
                                $service->order_num,
                                $sub->name,
                                $user->name,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = "archivo subido satisfactoriamente";
            return $response;
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function SendInvoiceFile(Request $request)
    {
        $user = Auth::user();
        if ($user->role_id == 3 || $user->role_id == 5) {

            $file = $request->file('invoice');
            $originalname = $file->getClientOriginalName();
            $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlFile = Storage::url($pathFile);

            $upOrder = Order::where('id', $request->order_id)
                ->update(
                    [
                        'invoice_file' => $urlFile,
                        'invoice_date' => Carbon::now()->format('Y-m-d'),
                        'expiration_date' => Carbon::now()->add($request->pay_days, 'day')->format('Y-m-d'),
                        'expiration_date_supplier' => Carbon::now()->add(45, 'day')->format('Y-m-d'),
                    ]
                );

            if ($upOrder) {
                $response['success'] = true;
                $response['message'] = "archivo subido satisfactoriamente";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "no se pudo actualizar la orden";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }



    public function Getallusers($role)
    {
        $userAuth = Auth::user();
        if ($role == 1) {
            $users = DB::table('users')
                ->join('roles', 'users.role_id', 'roles.id')
                ->join('organizations', 'users.organization_id', 'organizations.id')
                ->join('colonies', 'organizations.colony_id', 'colonies.id')
                ->join('vs_users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.phone',
                    'users.email',
                    'vs_users.vs_id',
                    'roles.name as tipo',
                    'users.status',
                    'organizations.name as org',
                    'organizations.rfc',
                    'organizations.street',
                    'organizations.logo',
                    'organizations.external_number',
                    'organizations.internal_number',
                    'colonies.name as colonia'
                )
                ->where([['organizations.status', 1]])
                ->where('users.id', '!=', $userAuth->id)
                ->orderBy('users.id', 'ASC')
                ->get();
        } else if ($role == 3) {
            $vs = VsUser::where('user_id', $userAuth->id)->first('vs_id');

            $users = DB::table('users')
                ->join('roles', 'users.role_id', 'roles.id')
                ->join('organizations', 'users.organization_id', 'organizations.id')
                ->join('colonies', 'organizations.colony_id', 'colonies.id')
                ->join('vs_users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.phone',
                    'users.email',
                    'vs_users.vs_id',
                    'roles.name as tipo',
                    'users.status',
                    'organizations.name as org',
                    'organizations.rfc',
                    'organizations.street',
                    'organizations.logo',
                    'organizations.external_number',
                    'organizations.internal_number',
                    'colonies.name as colonia'
                )
                ->where('organizations.status', 1)
                ->where('vs_users.vs_id', '=', $vs->vs_id)
                ->where('users.id', '!=', $userAuth->id)
                ->orderBy('users.id', 'ASC')
                ->get();
        }


        return response()->json($users);
    }

    public function UpdownUser(Request $request)
    {

        if ($request->checked == true) {
            $userStatus = 1;
            $nft = 18;
        } else {
            $userStatus = 0;
            $nft = 14;
        }
        $updateUser = User::where('id', $request->id)->update(['status' => $userStatus]);
        $mail = User::where('id', $request->id)->first('email');

        if ($updateUser) {
            Notify::route('mail', $mail->email)
                ->notify(new GeneralNotification($nft));

            $response['message'] = "User actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar user";
            $response['success'] = false;
            return $response;
        }
    }

    public function ChangeVS(Request $request)
    {
        $updateVs = VsUser::where('user_id', $request->id)->update(['vs_id' => $request->value]);

        if ($updateVs) {

            $response['message'] = "User actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar user";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetAllCatalogos(Request $request)
    {
        $roles = Role::all();
        $units = Unit::all();
        $categorias = Category::all();
        $vs = Valuestream::all();

        return response()->json([
            'roles' => $roles,
            'units' => $units,
            'categorias' => $categorias,
            'vs' => $vs,
        ]);
    }

    public function NewCatalogo(Request $request)
    {

        if ($request->option == 1) {
            $catalogo = Role::updateOrCreate(
                ['name' => $request->data],
                ['status' => 1]
            );
        } else if ($request->option == 2) {
            $catalogo = Unit::updateOrCreate(
                ['name' => $request->data],
                ['status' => 1]
            );
        } else if ($request->option == 3) {
            $catalogo = Category::updateOrCreate(
                ['name' => $request->data],
                ['status' => 1]
            );
        } else if ($request->option == 4) {
            $catalogo = Valuestream::updateOrCreate(
                ['name' => $request->data],
                ['status' => 1]
            );
        }

        if ($catalogo) {
            $response['message'] = "Catalogo guardado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al guardad catalogo";
            $response['success'] = false;
            return $response;
        }
    }
    // Borrar api 
    // public function GetCloseOrdersReviews(Request $request)
    // {
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_followups.request_id as request_id', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();

    //     $arrayOrder = array();
    //     $title = 'request_types.name as titulo';
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         $req_id = $req->request_id;

    //         $order = DB::table('request_followups')
    //             ->join($table, 'request_followups.request_id', $table . '.id')
    //             ->join('ratings', 'ratings.request_followup_id', 'request_followups.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 $table . '.user_id as user',
    //                 $table . '.title as titulo',
    //                 $table . '.description as descripcion',
    //                 'request_types.name as tipo',
    //                 'ratings.client_to_agent_rating as rate',
    //                 'ratings.client_to_agent_comment as comment'
    //             )
    //             ->where([
    //                 ['ratings.client_to_agent_rating', '!=', null],
    //                 ['ratings.client_to_agent_comment', '!=', null],
    //                 ['request_followups.status', '=', '1'],
    //                 [$table . '.status', '=', '1'],
    //                 [$table . '.id', '=', $req_id],
    //                 ['request_followups.request_type_id', $req->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             // ->whereBetween('request_followups.step_id', [6, 8])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order[0]);
    //         }
    //     }
    //     return response()->json($arrayOrder);
    // }

    //Borrar api
    // public function GetCloseOrdersMroReviews(Request $request)
    // {
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_followups.request_id as request_id', 'request_types.id')
    //         ->where('request_followups.request_type_id', '=', 1)
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->get();

    //     $arrayOrder = array();
    //     $title = 'request_types.name as titulo';
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         $req_id = $req->request_id;

    //         $order = DB::table('request_followups')
    //             ->join($table, 'request_followups.request_id', $table . '.id')
    //             ->join('ratings', 'ratings.request_followup_id', 'request_followups.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 $table . '.user_id as user',
    //                 $title,
    //                 'request_types.name as tipo',
    //                 'ratings.client_to_agent_rating as rate',
    //                 'ratings.client_to_agent_comment as comment'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['ratings.client_to_agent_rating', '!=', null],
    //                 ['ratings.client_to_agent_comment', '!=', null],
    //                 [$table . '.status', '=', '1'],
    //                 // [$table . '.user_id', '=', $user->id],
    //                 [$table . '.id', '=', $req_id],
    //                 ['request_followups.request_type_id', $req->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             // ->whereBetween('request_followups.step_id', [6, 8])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order[0]);
    //         }
    //     }

    //     return response()->json($arrayOrder);
    // }

    public function stepsAgent()
    {
        $role = Auth::user()->role_id;
        $org = Auth::user()->organization_id;

        $foll_logs = DB::table('request_followup_logs')
            ->join('users', 'request_followup_logs.user_id', 'users.id')
            ->select(
                'request_followup_logs.id',
                'request_followup_logs.request_followup_id',
                'request_followup_logs.step_id',
                'request_followup_logs.user_id',
                'request_followup_logs.created_at'
            )
            ->where('request_followup_logs.status', 1)
            ->where(function ($query) {
                $query->where('request_type_id', 1)
                    ->orWhere('request_type_id', 2);
            })
            //->groupBy('request_followup_logs.request_followup_id')
            //->where([['users.organization_id',$org],['request_followup_logs.status',1]])
            ->get();

        dd($foll_logs);
        //return response()->json($arrayOrder);

    }
    public function GetSupplierProposalsOrderById($id)
    {
        $orderSuppliers = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
            ->join('users', 'supplier_proposals.user_id', 'users.id')
            ->join('organizations', 'users.organization_id', 'organizations.id')
            ->select(
                'organizations.name as org',
                'organizations.logo',
                'users.email',
                'users.phone',
                'users.name',
                'supplier_proposals.cost',
                'supplier_proposals.total_days',
                'supplier_proposals.cotization_file',
                DB::raw('(CASE WHEN request_followups.supplier_proposal_id=supplier_proposals.id THEN "true" ELSE "false" END) as ganador')
            )
            ->where('request_followups.id', $id)
            ->get();

        return response()->json($orderSuppliers);
    }

    public function isMroReqReady($id)
    {
        $isReady = collect(DB::select('SELECT isMroReqReady(?) AS isReady', [$id]))->first()->isReady;
        if ($isReady == 1) {
            $response['isReady'] = true;
            return $response;
        } else {

            $response['isReady'] = false;
            return $response;
        }
    }

    public function ChangeMroStepToFinal(Request $request)
    {
        $user = Auth::user()->id;
        $changeStep = collect(DB::select('SELECT onChangeReqFolls_MoveMROReqs (?,?) AS changeStep', [$request->id, $user]))->first()->changeStep;
        //   dd($changeStep);

        if ($changeStep == 1) {
            $response['isReady'] = true;
            return $response;
        } else {

            $response['isReady'] = false;
            return $response;
        }
    }

    public function ConsumoClientes(Request $request)
    {

        // productosServiciosAgent

        $current_year = date('Y');

        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name', 'request_types.id')
            ->where('request_followups.status', '=', '1')
            ->where('request_followups.step_id', 6)
            ->groupBy('request_followups.request_type_id')
            ->get();
        $collection = collect();
        foreach ($request_follow_up as $req) {
            $table = $req->name . '_requests';

            $order = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.step_id',
                    'request_types.name as service',
                    'request_followups.purchase_order',
                    'request_followups.created_at as fecha',
                    $table . '.final_cost as final_cost',
                    DB::raw(
                        '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)                      
                        ELSE ' . $table . '.final_cost
                        END) AS costo_servicio',
                    ),
                )
                ->selectRaw(
                    '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)  - ' . $table . '.final_cost                      
                        ELSE "0" 
                        END) AS ahorro, MONTHNAME(request_followups.created_at) as month'
                )
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                ->groupBy('request_followups.request_id')
                ->orderBy('request_followups.created_at', 'asc')
                ->get();
            $collection->push($order);
        }
        $newArr = $collection->collapse();
        $grouped = $newArr->groupBy('month');

        $groupwithcount = $grouped->map(function ($group, $month) {
            return [
                'orders' => $group->count('purchase_order'),
                'ahorro_ordenes' => round($group->sum('ahorro'), 2),
                'costo' => round($group->sum('final_cost'), 2),
                'promedio' => round($group->avg('final_cost'), 2),
                'desviacion' => round(($group->sum('final_cost') - $group->avg('final_cost')), 2),
                'month' => $month,
                'order' => $group,
            ];
        });

        return response()->json($groupwithcount);
    }

    public function VentasSupplier(Request $request)
    {
        $current_year = date('Y');

        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name', 'request_types.id')
            // ->where('request_followups.request_type_id','!=',1)
            ->groupBy('request_followups.request_type_id')
            ->get();
        $collection = collect();
        foreach ($request_follow_up as $req) {
            $table = $req->name . '_requests';
            //ANTES
            // $order = DB::table('request_followups')
            //     ->join($table, 'request_followups.request_id', $table . '.id')
            //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            //     ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
            //     ->join('steps', 'request_followups.step_id', 'steps.id')
            //     ->select(
            //         'request_followups.id',
            //         'request_followups.purchase_order',
            //         'request_types.name as service',
            //         'request_followups.created_at as fecha',
            //         'supplier_proposals.cost as costo_servicio',
            //     )
            //     ->selectRaw('MONTHNAME(request_followups.created_at) as month')

            //     ->where([
            //         ['request_followups.status', '=', '1'],
            //         [$table . '.status', '=', '1'],
            //         ['supplier_proposals.status', 1],
            //         ['request_types.status', '=', '1'],
            //         ['steps.status', '=', '1'],
            //         ['request_followups.request_type_id', $req->id],
            //     ])
            //     ->where('request_followups.step_id', 6)
            //     ->whereYear('request_followups.created_at', $current_year)
            //     ->orderBy('request_followups.created_at', 'asc')
            //     ->get();
            $order = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.request_type_id',
                    'request_followups.purchase_order',
                    $table . '.final_cost as costo_servicio',
                    'request_types.name as service',
                    'request_followups.created_at as fecha',
                    'supplier_proposals.cost as costo_proveedor',
                    DB::raw($table . '.final_cost -supplier_proposals.cost as ganancia'),
                    DB::raw('ROUND((' . $table . '.final_cost -supplier_proposals.cost)*100/' . $table . '.final_cost,2) as ganancia_porcent')
                )
                ->selectRaw('MONTHNAME(request_followups.created_at) as month')

                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['supplier_proposals.status', 1],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                ->orderBy('request_followups.created_at', 'asc')
                ->get();
            $collection->push($order);
        }
        $newArr = $collection->collapse();
        $grouped = $newArr->groupBy('month');


        $groupwithcount = $grouped->map(function ($group, $month) {
            //Antes
            // return [
            //     'ahorro_ordenes' => $group->count('purchase_order'),
            //     'costo' => round($group->sum('costo_servicio'), 2),
            //     'promedio' => round($group->avg('costo_servicio'), 2),
            //     'desviacion' => round(($group->sum('costo_servicio') - $group->avg('costo_servicio')), 2),
            //     'month' => $month,
            //     'order' => $group,
            // ];
            return [
                // 'ahorro_ordenes' => $group->count('purchase_order'),
                'costo' => round($group->sum('costo_servicio'), 2),
                'cost_promedio' => round($group->avg('costo_servicio'), 2),
                'ganancia_promedio' => round(($group->avg('ganancia')), 2),
                'month' => $month,
                'order' => $group,
            ];
        });



        return response()->json($groupwithcount);
    }

    public function UploadPoAutorization($id)
    {
        $suppliersCount = SupplierProposal::where([['request_followup_id', $id], ['cotization_file', '!=', null]])
            ->count();
        return response()->json($suppliersCount);
    }
    // Borrar public function NewService(Request $request)
    // {
    //     $service = Category::updateOrCreate(
    //         ['name' => $request->name],
    //         ['status' => 1],

    //     );

    //     if ($service) {
    //         $response['message'] = "Se agrego el servicio con éxito.";
    //         $response['success'] = true;
    //         return $response;
    //     } else {
    //         $response['message'] = "Hubo un problema al añadir el servicio.";
    //         $response['success'] = false;
    //         return $response;
    //     }
    // }

    public function EditEpnoPart(Request $request)
    {
        $upPart = EpnoPart::where('id', $request->id)
            ->update(['name' => $request->nombre, 'part_no' => $request->partno, 'description' => $request->desc]);
        if ($upPart) {
            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function ManualProcessCot(Request $request)
    {
        try {
            // return $request;
            $agente = Auth::user()->name;
            $user = Auth::user()->id;
            $neto = $request->qty * $request->cost;
            $precio_iva = $neto * ($request->iva / 100);
            $total = $neto + $precio_iva;
            $data = [
                'org' => $request->org,
                'client_name' => $request->user_name,
                // 'precio_unitario' => $request->cost,
                'iva' => $request->iva,
                // 'codigo' => $request->codigo,
                // 'descripcion' => $request->descripcion,
                'precio_iva' => $precio_iva,
                // 'qty' => $request->qty,
                'user_name' => $agente,
                'final_cost' => $neto,
                'fecha_entrega' => Carbon::now()->add($request->time, 'day')->format('Y-m-d'),
                'dias_validos' => $request->condiciones_pago,
                'vigencia' => $request->vigencia,
                'total' => $total,
                'purchase' => $request->purchase,
                'date' => Carbon::now()->format('Y-m-d'),
                'tipo_cambio' => $request->tipo_cambio,
                'currency' => $request->currency,
                'products' => [
                    [
                        'precio_unitario' => $request->cost,
                        // 'codigo' => $request->codigo,
                        'descripcion' => $request->descripcion,
                        'qty' => $request->qty,
                        'um' => $request->unidad,
                    ]
                ]
            ];
            $fila_name = 'COT-' . $request->request_followup_id . '-' . $request->org . '.pdf';
            PDF::loadView('cotizacionEN', $data)
                ->save(storage_path('app/public/uploads/') . $fila_name);
            $url = '/storage/uploads//' . $fila_name;
            // return $url;


            // $file = $request->file('myFile');
            // $originalname = $file->getClientOriginalName();
            // $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            // $urlFile = Storage::url($pathFile);

            $supp = SupplierProposal::create([
                'request_followup_id' => $request->request_followup_id,
                'cost' => $request->cost,
                'total_days' => $request->time,
                'user_id' => 139,
                // produccion 'user_id' => 136,
                // 'cotization_file' => $url
            ]);

            if ($supp) {
                $upRequestFollow = RequestFollowup::where('id', $request->request_followup_id)
                    ->update([
                        // 'supplier_proposal_id' => $supp->id,
                        'cot_price' => $request->cost,
                        // 'epno_cot_file' => $url,
                        // 'step_id' => 2
                    ]);

                if ($upRequestFollow) {
                    $req_log = RequestFollowupLogs::create([
                        'request_followup_id' => $request->request_followup_id,
                        'step_id' => 1,
                        'user_id' => $user,
                    ]);

                    if ($req_log) {
                        // $notificationUser = new Notification();
                        // $notificationUser->user_id = $request->user_id;
                        // $notificationUser->type_notification_id = 5;
                        // $notificationUser->table_name = "request_followups";
                        // $notificationUser->table_name_id = $request->request_followup_id;

                        // if ($notificationUser->save()) {
                        //     Notify::route('mail', $request->user_email)
                        //         ->notify(new OrderDetailsNotification(5, $request->user_role, $request->request_followup_id, $request->request_type_id, $request->purchase_order));

                        $response['success'] = true;
                        $response['url'] = $url;
                        return $response;
                        // } else {
                        //     $response['success'] = false;
                        //     return $response;
                        // }
                    } else {
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    return $response;
                }
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function SubirClientCotGenerada(Request $request)
    {
        try {
      
        $user = Auth::user();

        if ($user->role_id == 3 || $user->role_id == 5) {
            $org = Organization::where('id', $user->organization_id)->first('name');

            $file = $request->file('file');
            $originalname = $file->getClientOriginalName();
            $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlFile = Storage::url($pathFile);


            $upService = Service::where('id', $request->service_id)
                ->update([
                    'quote_file' => $urlFile,
                ]);

            if ($upService) {

                $changeStep = DB::select('CALL processOrder(?,?,?)', array($request->order_id, $user->id, null));
                $getSuppliers = json_decode($changeStep[0]->response);

                foreach ($getSuppliers->suppliers as $id) {
                    $notification = new Notification();
                    $notification->user_id = $id->user_id;
                    $notification->notification_type_id = 20;
                    $notification->table_name = "services";
                    $notification->table_id = $request->order_id;
                    if ($notification->save()) {
                        DB::select('call limitNotificationCount (?)', array($id->user_id));
                    }

                    Notify::route('mail', $id->user_email)
                        ->notify(new OrderDetailsNotification(
                            20,
                            6,
                            $request->order_id,
                            $request->purchase_order,
                            $request->title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));
                }

                $notificationUser = new Notification();
                $notificationUser->user_id = $request->user_id;
                $notificationUser->notification_type_id = 5;
                $notificationUser->table_name = "services";
                $notificationUser->table_id = $request->order_id;

                if ($notificationUser->save()) {
                    DB::select('call limitNotificationCount (?)', array($request->user_id));

                    Notify::route('mail', $request->user_email)
                        ->notify(new OrderDetailsNotification(
                            5,
                            $request->user_role,
                            $request->order_id,
                            $request->purchase_order,
                            $request->title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));

                    $response['success'] = true;
                    $response['message'] = "archivo guardado correctamente.";
                    return $response;
                } else {
                    $response['success'] = false;
                    $response['message'] = "No se pudo guardar la notificacion via app.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "No se pudo guardar el archivo en el servicio.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No tienes los permisos para realizar esta acción.";
            return $response;
        }
    } catch (\Throwable $th) {
        $response['success'] = false;
        $response['message'] = $th->getMessage();
        return $response;
    }
    }

    public function ShowSupplierProposals(Request $request)
    {
        $userAuth = Auth::user();
        $supplier_proposals  = DB::select('CALL subServiceSupplierSelection(?)', array($request->subservice));
        $getSuppliers = json_decode($supplier_proposals[0]->response);
        $ids_array = array($userAuth->id);

        foreach ($getSuppliers as $sp) {

            array_push($ids_array, $sp->id);
        }

        $more_supp = DB::table('users')
            ->join('roles', 'users.role_id', 'roles.id')
            ->join('organizations', 'users.organization_id', 'organizations.id')
            ->join('organization_categories', 'organizations.id', 'organization_categories.organization_id')
            ->select(
                'users.id',
                'users.name',
                'users.phone',
                'users.email',
                'users.role_id',
                'roles.name as tipo',
                'users.status',
                'organizations.name as org',
                'organizations.id as org_id',
                'organizations.rfc',
                'organizations.street',
                'organizations.logo',
                'organizations.external_number',
                'organizations.internal_number',
                'organization_categories.category_id'
            )
            ->where([
                ['organizations.status', 1],
                ['organization_categories.category_id', $request->categoria],
                ['users.role_id', 6], ['users.status', 1]
            ])
            ->whereNotIn('users.id', $ids_array)
            ->orderBy('users.id', 'ASC')
            ->get();

        return response()->json([
            "suppliers" => $getSuppliers,
            "more_supp" => $more_supp,

        ]);
    }

    // Borrar ests apis comentadas
    // public function CotAutomaticProcess(Request $request)
    // {
    //     $user = Auth::user()->id;
    //     $AgentId = User::where('role_id', 1)->get('id');
    //      $supplier_proposals  = collect(DB::select('SELECT onCreateRequestFollowUp_SelectSuppliers (?) AS suppliers', [$request->request_follow_up]))->first()->suppliers;
    //     $step = RequestFollowup::where('id', $request->request_follow_up)->update(['step_id' => 1]);
    //     $getSuppliers = json_decode($supplier_proposals, true);

    //     if ($step) {
    //         $foll_logs = RequestFollowupLogs::create([
    //             'request_followup_id' => $request->request_follow_up,
    //             'step_id' => 1,
    //             'user_id' => $user,
    //         ]);
    //         if ($foll_logs) {

    //             Notify::route('mail', $request->user_email)
    //                 ->notify(new OrderDetailsNotification(
    //                     3,
    //                     $request->user_role,
    //                     $request->request_follow_up,
    //                     $request->request_type_id,
    //                     $request->purchase_order
    //                 ));

    //             $notificationUser = new Notification();
    //             $notificationUser->user_id = $request->user_id;
    //             $notificationUser->type_notification_id = 3;
    //             $notificationUser->table_name = "request_followups";
    //             $notificationUser->table_name_id = $request->request_follow_up;
    //             if ($notificationUser->save()) {
    //                 DB::select('call limitNotificationCount (?)', array($request->user_id));
    //             }


    //             foreach ($getSuppliers as $sp) {

    //                 Notify::route('mail', $sp['email'])
    //                     ->notify(new OrderDetailsNotification(
    //                         20,
    //                         $sp['role'],
    //                         $request->request_follow_up,
    //                         $request->request_type_id,
    //                         $request->purchase_order
    //                     ));

    //                 $notification = new Notification();
    //                 $notification->user_id = $sp['userId'];
    //                 $notification->type_notification_id = 20;
    //                 $notification->table_name = "request_followups";
    //                 $notification->table_name_id = $request->request_follow_up;
    //                 if ($notification->save()) {
    //                     DB::select('call limitNotificationCount (?)', array($sp['userId']));
    //                 }
    //             }

    //             foreach ($AgentId as $Agid) {
    //                 $user_info = User::select('email', 'role_id')->where('id', $Agid->id)->first();

    //                 $notificationAgent = new Notification();
    //                 $notificationAgent->user_id = $Agid->id;
    //                 $notificationAgent->type_notification_id = 3;
    //                 $notificationAgent->table_name = "request_followups";
    //                 $notificationAgent->table_name_id = $request->request_follow_up;
    //                 if ($notificationAgent->save()) {
    //                     DB::select('call limitNotificationCount (?)', array($Agid->id));
    //                 }

    //                 Notify::route('mail', $user_info->email)
    //                     ->notify(new OrderDetailsNotification(
    //                         3,
    //                         $user_info->role_id,
    //                         $request->request_follow_up,
    //                         $request->type,
    //                         $request->purchase
    //                     ));
    //             }
    //         }
    //     }

    //     return response()->json($getSuppliers);
    // }

    // public function CotAddMoreSuppliers(Request $request)
    // {

    // foreach ($request->suppliers as $id) {
    //     $newSp = SupplierProposal::create([
    //         'user_id' => $id,
    //         'request_followup_id' => $request->request_follow_up,
    //     ]);
    //     if ($newSp) {
    //         $user_info = User::select('email', 'role_id')->where('id', $id)->first();

    //         Notify::route('mail', $user_info->email)
    //             ->notify(new OrderDetailsNotification(
    //                 20,
    //                 $user_info->role_id,
    //                 $request->request_follow_up,
    //                 $request->request_type_id,
    //                 $request->purchase_order
    //             ));

    //         $notification = new Notification();
    //         $notification->user_id = $id;
    //         $notification->type_notification_id = 20;
    //         $notification->table_name = "request_followups";
    //         $notification->table_name_id = $request->request_follow_up;
    //         if($notification->save()){
    //             DB::select('call limitNotificationCount (?)', array($id)); 
    //         }
    //     }
    // }
    // }

    public function AddSubserviceSuppliers(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->role_id == 3 || $user->role_id == 5) {
                if (
                    isset($request->values['suppliers']) &&
                    isset($request->values['more_supp']) &&
                    $request->values['more_supp'] !== [] &&
                    $request->values['suppliers'] !== []
                ) {
                    $all_sp = array_merge($request->values['more_supp'][0], $request->values['suppliers']);
                    $suppliers = array_unique($all_sp);
                } else if (isset($request->values['suppliers']) &&  $request->values['suppliers'] !== []) {
                    $suppliers = $request->values['suppliers'];
                } else {
                    $suppliers = $request->values['more_supp'][0];
                }
                foreach ($suppliers as $sp) {
                    $supp_code = collect(DB::select('SELECT getSupplierCode(?) AS code', [$sp]))->first()->code;

                    SupplierProposal::create([
                        'service_id' => $request->service,
                        'subservice_id' => $request->subservice,
                        'user_id' => $sp,
                        'supplier_code' => $supp_code,
                    ]);
                }

                $response['success'] = true;
                return $response;
            } else {
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function AddNewSubservice(Request $request)
    {
        // return $request;
        $user = Auth::user();
        if ($user->role_id == 3 || $user->role_id == 5) {

            if ($request->option == 1) {
                foreach ($request->subservices as $sub) {
                    $subservice = Subservice::create([
                        'service_id' => $request->service['id'],
                        'name' => $sub['name'],
                        'step_id' => 1,
                        'qty' => $sub['qty'],
                        'category_id' => $sub['category'],
                        'unit_id' => $sub['unit'],
                        'specs_file' => $sub['file'],
                    ]);

                    if ($subservice) {
                        SubserviceLog::create([
                            'subservice_id' => $subservice->id,
                            'step_id' => $subservice->step_id,
                            'user_id' =>  $user->id,
                        ]);
                    }
                }

                $response['success'] = true;
                $response['message'] = "Subservicios creados correctamente.";
                return $response;
            } else {
                $service = Service::where('id', $request->service['id'])
                    ->update(['step_id' => 2]);

                if ($service) {
                    ServiceLog::create([
                        'service_id' => $request->service['id'],
                        'step_id' => 2,
                        'user_id' => $user->id,
                    ]);

                    $subservice = Subservice::create([
                        'service_id' => $request->service['id'],
                        'name' => $request->service['desc'],
                        'step_id' => 2,
                        'qty' => $request->subservices['qty'],
                        'category_id' => $request->subservices['category'],
                        'unit_id' => $request->subservices['unit'],
                    ]);

                    if ($subservice) {
                        $subLog = SubserviceLog::create([
                            'subservice_id' => $subservice->id,
                            'step_id' => $subservice->step_id,
                            'user_id' => $user->id,
                        ]);

                        if ($subLog) {
                            $response['success'] = true;
                            $response['message'] = "Servicio actualizado de manera correcta.";
                            return $response;
                        } else {
                            $response['success'] = false;
                            $response['message'] = "No se pudo guardar registro del servicio.";
                            return $response;
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = "No se pudo actualizar la informacion del servicio.";
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "No se pudo actualizar la informacion del servicio.";
                    return $response;
                }
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con permisos para realizar esta acción.";
            return $response;
        }
    }

    public function ChangeServiceInfo(Request $request)
    {
        // return $request;
        if ($request->type == 1) {
            $type = Service::where('id', $request->service)
                ->update([
                    'type' => $request->value
                ]);
        } elseif ($request->type == 2) {
            $type = Service::where('id', $request->service)
                ->update([
                    'description' => $request->value
                ]);
        } else {
            $type = Service::where('id', $request->service)
                ->update([
                    'prioridad' => $request->value
                ]);
        }

        if ($type) {
            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function GetVS()
    {
        $vs = Valuestream::all();
        return response()->json($vs);
    }

    public function EpnoSelectSuppliers(Request $request)
    {
        try {
            //code...
       
        $user = Auth::user();
        // return $request;

        if ($user->role_id == 3 || $user->role_id == 5) {

            $upSupp = SupplierProposal::where([
                ['id', $request->supplier],
                ['service_id', $request->service],
                ['subservice_id', $request->subservice],
            ])->update([
                'epno_cost' => $request->cost,
                'epno_deadline' => $request->entrega,
                'description' => $request->descripcion,               
            ]);

            if ($upSupp) {
                $response['success'] = true;
                $response['message'] = "Proveedor actualizado correctamente";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "Error al actualizar proveedor";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    } catch (\Throwable $th) {
        $response['success'] = false;
        $response['message'] = $th->getMessage();
        return $response;
    }
    }

    public function SuppCotAgain(Request $request)
    {
        $user = Auth::user();

        // return $request;

        if ($user->role_id == 3 || $user->role_id == 5) {
            $up = SupplierProposal::join('subservices', 'supplier_proposals.subservice_id', 'subservices.id')
                ->where('supplier_proposals.id', $request->supplier['id'])
                ->where('supplier_proposals.subservice_id', $request->subservice)
                ->update([
                    'subservices.step_id' => 2,
                    'supplier_proposals.unitary_subtotal_cost' => null,
                    'supplier_proposals.supplier_deadline' => null,
                    'supplier_proposals.quote_file' => null,
                    'supplier_proposals.total_cost' => null,
                    'supplier_proposals.qty' => 0.00,
                    'supplier_proposals.iva' => 0,
                ]);

            if ($up) {

                $notificationSupp = new Notification();
                $notificationSupp->user_id = $request->supplier['user_id'];
                $notificationSupp->notification_type_id = 1;
                $notificationSupp->table_name = "services";
                $notificationSupp->table_id = $request->service['order_id'];

                if ($notificationSupp->save()) {
                    DB::select('call limitNotificationCount (?)', array($request->supplier['user_id']));

                    Notify::route('mail', $request->supplier['user_email'])
                        ->notify(new OrderDetailsNotification(
                            1,
                            6,
                            $request->service['order_id'],
                            $request->service['order_num'],
                            $request->service['title'],
                            $user->name,
                            $user->phone,
                            $user->email,
                            "EP&O Electronic Purchase and Order",
                        ));
                }

                $response['success'] = true;
                $response['message'] = "El proveedor podra cotizar nuevamente.";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "No se pudo dar el permiso de cotizar nuevamente al proveedor.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function ShowSubserviceComplaintSupplier(Request $request)
    {
        // return $request;
        $user = Auth::user();

        // if ($user->role_id == 3 || $user->role_id == 5) {
        if ($user->role_id == 10) {
            $suppliers = SupplierProposal::with('User.organization')->where('service_id', $request->service)
                ->where('subservice_id', $request->subservice)->get();

            return response()->json($suppliers);
        }
    }

    public function AddSupplierComplaint(Request $request)
    {
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');
        // if ($user->role_id == 3 || $user->role_id == 5) {
        if ($user->role_id == 10) {

            foreach ($request->suppliers as $sp) {
                $user_sup = SupplierProposal::with('User')->where('id', $sp)->first();

                $supplier = SupplierProposalComplaint::create([
                    'subservice_complaint_id' => $request->subservice_complaint_id,
                    'supplier_proposal_id' => $sp,
                    'user_id' => $user_sup->user->id,
                    'step_id' => $request->complaint_step,
                ]);

                SupplierProposalComplaintLog::create([
                    'supplier_proposal_complaint_id' => $supplier->id,
                    'user_id' => $user->id,
                    'step_id' => $request->complaint_step,
                    'description' => "En " + $request->step_name,
                ]);


                if (isset($request->client_evidencias)) {

                    foreach ($request->client_evidencias as $ce) {
                        $evidencia = ComplaintClientToEpnoEvidence::where('id', $ce)->first();
                        ComplaintEpnoToSupplierEvidence::create([
                            'complaint_id' => $request->complaint_id,
                            'user_id' => $user_sup->user->id,
                            'epno_description' => $evidencia->client_description,
                            'epno_file' => $evidencia->client_file,
                            'epno_file_name' => $evidencia->client_file_name,
                        ]);
                    }
                }

                if (isset($request->epno_evidencias) && isset($request->epno_descs)) {

                    $descs = collect($request->epno_descs);
                    $evidencias = collect($request->epno_evidencias);
                    $epno_evidencias = $descs->zip($evidencias);

                    foreach ($epno_evidencias as $e) {
                        $fileE = $e[1];
                        $originalnameE = $fileE->getClientOriginalName();
                        $pathE = Storage::putFileAs('/public/uploads/', $fileE,  $originalnameE);
                        $urlE = Storage::url($pathE);


                        ComplaintEpnoToSupplierEvidence::create([
                            'complaint_id' => $request->complaint_id,
                            'user_id' => $user_sup->user->id,
                            'epno_description' => $e[0],
                            'epno_file' => $urlE,
                            'epno_file_name' => $originalnameE,
                        ]);
                    }
                }

                $notificationSupp = new Notification();
                $notificationSupp->user_id = $user_sup->user->id;
                $notificationSupp->notification_type_id = 25;
                $notificationSupp->table_name = "complaints";
                $notificationSupp->table_id = $request->complaint_id;
                if ($notificationSupp->save()) {
                    DB::select('call limitNotificationCount (?)', array($user_sup->user->id));
                }

                Notify::route('mail', $user_sup->user->email)
                    ->notify(new ComplaintDetailsNotification(
                        25,
                        6,
                        $request->complaint_id,
                        $request->complaint_num,
                        $request->service_title,
                        $user->name,
                        $user->phone,
                        $user->email,
                        $org->name,
                    ));
            }

            $response['success'] = true;
            $response['message'] = "Proceso terminado correctamente.";
            return $response;
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function ProccessInternalComplaint(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id == 10) {
            // if ($user->role_id == 3 || $user->role_id == 5) {
            $supp = SupplierProposalComplaint::create([
                'subservice_complaint_id' => $request->subservice,
                'user_id' => $user->id
            ]);

            if ($supp) {
                $response['success'] = true;
                $response['message'] = "Proceso terminado correctamente.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function ChangeComplaintType(Request $request)
    {
        // return $request;
        $user = Auth::user();
        if ($user->role_id == 10) {

            $upComplaint = Complaint::where('id', $request->complaint)->update(['type' => $request->value]);

            if ($upComplaint) {
                $response['success'] = true;
                $response['message'] = "Queja actualizada correctamente.";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "Hubo un problema al actualizar la queja.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function CancelarRechazarQueja(Request $request)
    {
        // return $request;
        $user = Auth::user();

        if ($user->role_id == 10) {
            $upQueja = Complaint::where('id', $request->complaint_id)->update([
                'step_id' => $request->opcion,
                'close_date' => Carbon::now()->format('Y-m-d'),
            ]);

            if ($upQueja) {

                $log = ComplaintLog::create([
                    'complaint_id' => $request->complaint_id,
                    'user_id' => $user->id,
                    'step_id' => $request->opcion,
                    'description' => $request->descripcion,
                ]);

                if ($log) {

                    $notificationClient = new Notification();
                    $notificationClient->user_id = $request->user_id;
                    $notificationClient->notification_type_id = 27;
                    $notificationClient->table_name = "complaints";
                    $notificationClient->table_id = $request->complaint_id;
                    if ($notificationClient->save()) {
                        DB::select('call limitNotificationCount (?)', array($request->user_id));
                    }

                    Notify::route('mail', $request->user_email)
                        ->notify(new ComplaintDetailsNotification(
                            27,
                            4,
                            $request->complaint_id,
                            $request->complaint_num,
                            $request->service_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $request->descripcion,
                        ));

                    $response['success'] = true;
                    $response['message'] = "Queja actualizada correctamente.";
                    return $response;
                } else {
                    $response['success'] = false;
                    $response['message'] = "Hubo un error al actualizar la queja.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Hubo un error al actualizar la queja.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function SendPoSupplier(Request $request)
    {
        // return $request;
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');

        if ($user->role_id == 10) {

            $file = $request->file('file');
            $originalname = $file->getClientOriginalName();
            $pathPO = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlPO = Storage::url($pathPO);
            $upSupplier = SupplierProposalComplaint::where('id', $request->supplier_id)->update(['po_file' => $urlPO]);

            if ($upSupplier) {

                $notificationClient = new Notification();
                $notificationClient->user_id = $request->supplier_user;
                $notificationClient->notification_type_id = 22;
                $notificationClient->table_name = "complaints";
                $notificationClient->table_id = $request->complaint_id;
                if ($notificationClient->save()) {
                    DB::select('call limitNotificationCount (?)', array($request->supplier_user));
                }

                Notify::route('mail', $request->supplier_mail)
                    ->notify(new ComplaintDetailsNotification(
                        22,
                        6,
                        $request->complaint_id,
                        $request->complaint_num,
                        $request->service_title,
                        $user->name,
                        $user->phone,
                        $user->email,
                        $org->name,
                    ));

                $response['success'] = true;
                $response['message'] = "archivo subido correctamente";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "no se pudo asignar al proveedor";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "no cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }

    public function CloseComplaint(Request $request)
    {
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');
        $user_epno = User::where('role_id', 10)->first();
        if ($user->role_id == 10) {

            $fileRC = $request->file('root_cause');
            $originalnameRC = $fileRC->getClientOriginalName();
            $pathRC = Storage::putFileAs('/public/uploads/', $fileRC,  $originalnameRC);
            $urlRC = Storage::url($pathRC);

            $fileLL = $request->file('leccion_aprendida');
            $originalnameLL = $fileLL->getClientOriginalName();
            $pathLL = Storage::putFileAs('/public/uploads/', $fileLL,  $originalnameLL);
            $urlLL = Storage::url($pathLL);

            $data = [
                'user_name' => $request->user_name,
                'user_mail' => $request->user_mail,
                'user_phone' => $request->user_phone,
                'complaint_num' => $request->complaint_num,
                'primer_d' => $request->primer_d,
                'segunda_d' => $request->segunda_d,
                'tercer_d' => $request->tercer_d,
                'cuarta_d' => $request->cuarta_d,
                'quinta_d' => $request->quinta_d,
                'sexta_d' => $request->sexta_d,
                'septima_d' => $request->septima_d,
                'octava_d' => $request->octava_d,
                'date' => Carbon::now()->format('Y-m-d'),

            ];

            $fila_name = '8D-' . $request->service_title . '-' . $request->user_name . '.pdf';
            PDF::loadView('8ds', $data)
                ->save(storage_path('app/public/uploads/') . $fila_name);
            $url = '/storage/uploads//' . $fila_name;

            $complaint = Complaint::where('id', $request->complaint_id)->update([
                'step_id' => 14,
                'root_cause' => $urlRC,
                'lesson_learned' => $urlLL,
                'ocho_ds' => $url,
                'close_date' => Carbon::now()->format('Y-m-d'),
            ]);

            if ($complaint) {

                $log = ComplaintLog::create([
                    'complaint_id' => $request->complaint_id,
                    'user_id' => $user->id,
                    'step_id' => 14,
                    'description' => 'Queja cerrada correctamente.',
                ]);

                if ($log) {


                    $notificationClient = new Notification();
                    $notificationClient->user_id = $request->user_id;
                    $notificationClient->notification_type_id = 29;
                    $notificationClient->table_name = "complaints";
                    $notificationClient->table_id = $request->complaint_id;
                    if ($notificationClient->save()) {
                        DB::select('call limitNotificationCount (?)', array($request->user_id));
                    }

                    Notify::route('mail', $request->user_email)
                        ->notify(new ComplaintDetailsNotification(
                            29,
                            4,
                            $request->complaint_id,
                            $request->complaint_num,
                            $request->service_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));

                    $notificationEpno = new Notification();
                    $notificationEpno->user_id = $user_epno->id;
                    $notificationEpno->notification_type_id = 29;
                    $notificationEpno->table_name = "complaints";
                    $notificationEpno->table_id = $request->complaint_id;
                    if ($notificationEpno->save()) {
                        DB::select('call limitNotificationCount (?)', array($user_epno->id));
                    }

                    Notify::route('mail', $user_epno->email)
                        ->notify(new ComplaintDetailsNotification(
                            29,
                            10,
                            $request->complaint_id,
                            $request->complaint_num,
                            $request->service_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));

                    foreach ($request->suppliers as $supp) {
                        $sp_info = json_decode($supp);

                        $notificationSupplier = new Notification();
                        $notificationSupplier->user_id = $sp_info->user->id;
                        $notificationSupplier->notification_type_id = 29;
                        $notificationSupplier->table_name = "complaints";
                        $notificationSupplier->table_id = $request->complaint_id;
                        if ($notificationSupplier->save()) {
                            DB::select('call limitNotificationCount (?)', array($sp_info->user->id));
                        }

                        Notify::route('mail', $sp_info->user->email)
                            ->notify(new ComplaintDetailsNotification(
                                29,
                                6,
                                $request->complaint_id,
                                $request->complaint_num,
                                $request->service_title,
                                $user->name,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));
                    }

                    $response['success'] = true;
                    $response['message'] = "Queja actualizada correctamente.";
                    return $response;
                } else {
                    $response['success'] = true;
                    $response['message'] = "No se logro insertar el log de cambio, aun que si se actualizo la información.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "No se puso actualizar la queja.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "no cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }
}
