<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use App\Models\Organization;
use App\Location;
use App\Notifications\RegisterRequestNotification;
use App\OrderFiles;
use App\Models\PartNo;
use App\RequestFollowup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\SupplierProposal;
use App\ServiceCategory;
use Illuminate\Support\Facades\Notification;
use App\Models\Notification as Notify;
use App\Notifications\OrderDetailsNotification;
use App\Notifications\ProductNotification;
use App\Models\ProductComment;
use App\Models\SupplierProposalLog;
use App\Models\VsUser;
use DateTime;

class SupplierController extends Controller

{
    public function perfilSupplier(Request $request)
    {

        return $request;
        $userAuth = Auth::user();
        // dd($request);
        if ($userAuth) {
            if ($request->get('categoria') !== null || $request->get('categoria') !== "") {
                $arrayCategory = explode(',', $request->get('categoria'));

                $request->validate([
                    'terminos' => ['accepted'],
                ]);

                // $pathLogo = Storage::putFile('/public/uploads/', $request->file('myFile'));

                // $urllogo = Storage::url($pathLogo);

                $file = $request->file('myFile');
                $originalname = $file->getClientOriginalName();
                $pathLogo = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $urllogo = Storage::url($pathLogo);


                $organization = new Organization();
                $organization->name = $request->input('organizacion');
                $organization->rfc = $request->input('rfc');
                $organization->colony_id = $request->input('colonia');;
                $organization->street = $request->input('calle');
                $organization->external_number = $request->input('numero_exterior');
                $organization->internal_number = $request->input('numero_interior');
                $organization->url = $request->input('url');
                $organization->logo = $urllogo;


                if ($organization->save()) {
                    $location = new Location();
                    $location->organization_id =  $organization->id;
                    $location->name = $request->input('nombre_planta');
                    $location->colony_id = $request->input('coloniaP');;
                    $location->street = $request->input('calleP');
                    $location->internal_number = $request->input('numero_interior_p');
                    $location->external_number = $request->input('numero_exterior_p');
                    $location->type = $request->input('tipo');
                    $location->default = 1;

                    $role = User::where('id', $userAuth->id)->update(['organization_id' => $organization->id]);
                    if ($location->save() &&  $role) {
                        $category = ServiceCategory::find($arrayCategory);
                        if ($category) {
                            $organization->Categories()->attach($category);

                            Notification::route('mail', 'contacto@epno.com.mx')
                                ->notify(new RegisterRequestNotification(
                                    $request->input('organizacion'),
                                    $request->input('rfc'),
                                    $request->input('nombre_planta'),
                                    $request->input('calle')
                                ));

                            $response['message'] = "Guardado correctamente";
                            $response['success'] = true;
                            $response['org_id'] = $organization->id;

                            return $response;
                        } else {
                            $response['message'] = "No se encontro la  categoria";
                            $response['success'] = false;
                            return $response;
                        }
                    } else {
                        $response['message'] = "Error al guardar";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "Error al guardar";
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['message'] = "Todos los campos deben estar llenos";
                $response['campos_vacios'] = true;
                return $response;
            }
        } else {
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    public function addPart(Request $request)
    {
        try {

            $user = Auth::user();
            $iva = $request->subtotal * $user->iva;
            $price = $request->input('subtotal') + $iva;

            $part = new PartNo();
            $part->name = $request->input('name');
            $part->supplier_partno = $request->input('supplier_partno');
            $part->max_qty = $request->input('max_qty');
            $part->min_qty = $request->input('min_qty');
            $part->current_qty = $request->input('current_qty');
            $part->user_id = $user->id;
            $part->subtotal = $request->input('subtotal');
            $part->price = $price;


            if ($part->save()) {
                $response['message'] = "Guardado correctamente";
                $response['success'] = true;
                $response['id'] = $part->id;
                return $response;
            } else {
                $response['message'] = "Error al guardar";
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {
            $response['message'] = "Error en el api, cayo en el catch";
            $response['success'] = false;
            return $response;
        }
    }

    public function showLocation(Request $request)
    {
        $user = Auth::user()->organization_id;
        $loc = Location::orderBy('id', 'asc')->where('organization_id', $user)->get();
        return response()->json($loc);
    }
    public function showPartnos()

    {
        $user = Auth::user()->id;
        $partno = DB::table('part_nos')
            ->join('part_categories', 'part_nos.part_category_id', '=', 'part_categories.id')
            ->select(
                'part_nos.*',
                'part_categories.name as category'
            )
            ->where('part_nos.user_id', $user)
            ->get();


        return response()->json($partno);
    }
    public function consumoSupplier(Request $request)
    {
        $org = Auth::user()->organization_id;
        $rol = Auth::user()->role_id;
        $id = Auth::user()->id;

        $date1 = Carbon::now()->subMonth(1);
        $date2 = Carbon::now();

        if ($rol == 5) {
            $service = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('service_requests.user_id', 'service_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('service_requests.created_at', [$date1, $date2])
                ->whereBetween('users.role_id', [5, 6])
                ->where('users.organization_id', $org)
                //->get()
            ;

            $serviceMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('mro_requests.user_id', 'mro_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('mro_requests.created_at', [$date1, $date2])
                ->whereBetween('users.role_id', [5, 6])
                ->where('users.organization_id', $org)
                ->union($service)
                //->avg(DB::raw('final_cost'))
                ->get();
        } else if ($rol == 6) {
            $service = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('service_requests.user_id', 'service_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('service_requests.created_at', [$date1, $date2])
                ->where([['users.id', $id], ['users.organization_id', $org]])
                //->get()
            ;

            $serviceMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('mro_requests.user_id', 'mro_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('mro_requests.created_at', [$date1, $date2])
                ->where([['users.id', $id], ['users.organization_id', $org]])
                ->union($service)
                //->avg(DB::raw('final_cost'))
                ->get();
        } else {
            $serviceMro = "INTRUSO!";
        }

        //dd($serviceMro);
    }

    // public function ordenesTransitoOtros()
    // {

    //     $orga = Auth::user()->organization_id;
    //     $rol = Auth::user()->role_id;

    //     $ordenService = DB::table('service_requests')
    //         ->join('users', 'service_requests.user_id', 'users.id')
    //         ->select('service_requests.user_id')
    //         ->where('users.organization_id', $orga)
    //         //->get()
    //     ;

    // $ordenSerMro = DB::table('mro_requests')
    //         ->join('users', 'mro_requests.user_id', 'users.id')
    //         ->select('mro_requests.user_id')
    //         ->where('users.organization_id', $orga)
    //         ->union($ordenService)
    //         //->get()
    //     ;

    //     $req_foll = DB::table('request_followups')
    //         ->select('request_id')
    //         ->whereIn('request_id', $ordenSerMro)
    //         ->where(function ($query) {
    //             $query->where('request_type_id', 1)
    //                 ->orWhere('request_type_id', 2);
    //         })
    //         ->whereNotIn('step_id', [6, 7, 8])
    //         ->count()
    //         //->get()
    //     ;

    //     //dd($req_foll);
    //     return response()->json($req_foll);
    // }

    public function totalUsuarios()
    {
        $org = Auth::user()->organization_id;

        $usuarios = DB::table('users')
            ->where([['status', 1], ['organization_id', $org]])
            ->count()
            //->get()
        ;

        //dd($usuarios);
        return response()->json($usuarios);
    }

    public function profileLocationsAdmin()
    {
        $id = Auth::user()->id;
        $org = Auth::user()->organization_id;

        $locations = DB::table('users')
            ->join('locations', 'users.organization_id', 'locations.organization_id')
            ->join('colonies', 'locations.colony_id', 'colonies.id')
            ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
            ->join('cities', 'postal_codes.city_id', 'cities.id')
            ->join('states', 'cities.state_id', 'states.id')
            ->join('regions', 'states.region_id', 'regions.id')
            ->join('countries', 'regions.country_id', 'countries.id')
            ->select(
                'locations.name',
                'colonies.name as colonie',
                'postal_codes.name as CP',
                'cities.name as city',
                'states.name as state',
                'countries.name as country'
            )
            ->where([['locations.status', 1], ['locations.organization_id', $org]])
            ->get();

        // dd($locations);
        return response()->json($locations);
    }
    public function gastosPerfilAdmin()
    {
        $org = Auth::user()->organization_id;
        $current_year = date('Y');

        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name', 'request_types.id')
            ->groupBy('request_followups.request_type_id')
            ->get();
        $collection = collect();
        foreach ($request_follow_up as $req) {
            $table = $req->name . '_requests';

            $order = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('users', 'supplier_proposals.user_id', 'users.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.purchase_order',
                    'request_types.name as service',
                    'request_followups.created_at as fecha',
                    'supplier_proposals.cost as costo_servicio',
                    'users.organization_id'
                )
                ->selectRaw('MONTHNAME(request_followups.created_at) as month')
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['users.organization_id', '=', $org],
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
            return [
                'ahorro_ordenes' => $group->count('purchase_order'),
                'costo' => round($group->sum('costo_servicio'), 2),
                'promedio' => round($group->avg('costo_servicio'), 2),
                // 'desviacion' => round(($group->sum('costo_servicio') - $group->avg('costo_servicio')), 2),
                'month' => $month,
                'order' => $group,
            ];
        });



        return response()->json($groupwithcount);

        //dd($gastos);
    }
    public function reviewsAdmin()
    {
        $org = Auth::user()->organization_id;

        $users = DB::table('users')
            ->select('id')
            ->where('organization_id', $org);

        $rating = DB::table('ratings')
            ->join('request_followups', 'ratings.request_followup_id', 'request_followups.id')
            ->select(
                'ratings.id',
                'ratings.request_followup_id',
                'ratings.client_to_agent_comment',
                'request_followups.purchase_order'
            )
            ->whereIn('request_followups.request_id', $users)
            ->get();

        // dd($rating);
        return response()->json($rating);
    }

    public function productosServiciosAdmin(Request $request)
    {
        $org = Auth::user()->organization_id;
        $service = DB::table('request_followups')
            ->join('service_requests', 'request_followups.request_id', 'service_requests.id')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select(
                'request_followups.id',
                'users.organization_id as org',
                'request_followups.request_id',
                'request_followups.request_type_id',
                'service_requests.user_id as user',
                'service_requests.title as titulo',
                'request_types.name as tipo',
                DB::raw('COUNT(service_requests.title) as count'),
                DB::raw('TRUNCATE(SUM(IFNULL(service_requests.final_cost,0)),2) as gasto')
            )
            ->where([
                ['users.organization_id', $org],
                ['request_followups.status', 1]
            ])
            ->groupBy("service_requests.title");

        $mro = DB::table('request_followups')
            ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
            ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
            ->select(
                'request_followups.id',
                'users.organization_id as org',
                'request_followups.request_id',
                'request_followups.request_type_id',
                'mro_requests.user_id as user',
                'epno_parts.name as titulo',
                'request_types.name as tipo',
                DB::raw('COUNT(mro_requests.id) as count'),
                DB::raw('TRUNCATE(SUM(IFNULL(mro_requests.final_cost,0)),2) as gasto')
            )
            ->where([
                ['request_followups.request_type_id', 1],
                ['users.organization_id', $org],
                ['request_followups.status', 1]
            ])
            ->groupBy('epno_parts.name')
            // ->get();
            ->union($service);

        $productos = DB::query()->fromSub($mro, 'sr_mr')
            ->select(
                'id',
                'org',
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
    public function usuariosActivosAdmin()
    {
        $org = Auth::user()->organization_id;

        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select(
                'service_requests.id',
                'users.id as id_user',
                'users.name as name',
                'service_requests.final_cost'
            )
            ->where([['service_requests.status', 1], ['users.organization_id', $org]]);

        $ordenSerMro = DB::table('mro_parts')
            ->join('mro_requests', 'mro_parts.mro_request_id', 'mro_requests.id')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
            ->select(
                'mro_parts.mro_request_id as id',
                'users.id as id_user',
                'users.name as name',
                'mro_requests.final_cost'
            )
            ->where([['mro_parts.status', 0], ['users.organization_id', $org]])
            ->union($ordenService);

        $usuActivos = DB::query()->fromSub($ordenSerMro, 'sr_mr')
            ->select(
                'id',
                'name',
                DB::raw('COUNT(id) as ordenes'),
                DB::raw('TRUNCATE(SUM(IFNULL(final_cost,0)),2) as suma')
            )
            ->groupBy('name')
            ->orderBy('ordenes', 'DESC')
            ->take(5)
            ->get();

        return response()->json($usuActivos);
    }


    // public function GetOpenOrderSupplier($id, $type)
    // {
    //     //Tenemos que obtener el id del usuario loggeado
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->groupBy('request_followups.request_type_id')
    //         ->where('supplier_proposals.user_id', $user->id)
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->get();

    //     //  dd( $request_follow_up);
    //     $arrayOrder = array();
    //     $title = 'request_types.name as titulo';
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         if ($type == 1) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     ['supplier_proposals.user_id', $user->id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])
    //                 ->whereIn('request_followups.step_id', [1, 2, 3, 4, 5, 9])
    //                 // ->orWhere('request_followups.step_id', 9)
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 0) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     ['supplier_proposals.user_id', $user->id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 2) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     ['supplier_proposals.user_id', $user->id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         }

    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order);
    //         }
    //     }
    //     if ($arrayOrder !== null && $arrayOrder !== []) {

    //         return response()->json($arrayOrder[0]);
    //     } else {
    //         return response()->json($arrayOrder);
    //     }
    // }
    // public function GetOpenOrderSupplierAdmin($id, $type)
    // {
    //     //Tenemos que obtener el id del usuario loggeado
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->groupBy('request_followups.request_type_id')
    //         ->where('supplier_proposals.user_id', $user->id)
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->get();

    //     $arrayOrder = array();
    //     $title = 'request_types.name as titulo';
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';

    //         if ($type == 1) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('users', 'supplier_proposals.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', $user->organization_id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])
    //                 ->whereIn('request_followups.step_id', [1, 2, 3, 4, 5, 9])
    //                 // ->orWhere('request_followups.step_id', 9)
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 0) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('users', 'supplier_proposals.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', $user->organization_id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 2) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //                 ->join('users', 'supplier_proposals.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     $table . '.title as titulo',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     $table . '.total_days as dias',
    //                     'supplier_proposals.cost',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', $user->organization_id],
    //                     ['request_followups.request_type_id', $req->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],

    //                 ])

    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         }

    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order);
    //         }
    //     }

    //     if ($arrayOrder !== null && $arrayOrder !== []) {

    //         return response()->json($arrayOrder[0]);
    //     } else {
    //         return response()->json($arrayOrder);
    //     }
    // }
    // public function GetOpenOrderSupplierMro($id, $type)
    // {
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();
    //     if ($type == 1) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['supplier_proposals.user_id', $user->id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],

    //             ])
    //             ->whereBetween('request_followups.step_id', [1, 5])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 0) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'

    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['supplier_proposals.user_id', $user->id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],

    //             ])
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 2) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'

    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['supplier_proposals.user_id', $user->id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],

    //             ])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     }



    //     return response()->json($order);
    // }
    // public function GetOpenOrderSupplierMroAdmin($id, $type)
    // {
    //     //Tenemos que obtener el id del usuario loggeado
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();

    //     if ($type == 1) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('users', 'supplier_proposals.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'

    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', $user->organization_id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             ->whereBetween('request_followups.step_id', [1, 5])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 0) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('users', 'supplier_proposals.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'

    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', $user->organization_id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     } elseif ($type == 2) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('users', 'supplier_proposals.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'request_types.name as titulo',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.cost'

    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', $user->organization_id],
    //                 ['request_followups.request_type_id', 1],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //             ])
    //             ->groupBy('request_followups.request_id')
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //     }

    //     return response()->json($order);
    // }


    // public function GetCloseOrderSupplier(Request $request)
    // {
    //     $user = Auth::user();

    //     $request_follow_up = DB::table('request_followups')
    //     ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //     ->select('request_types.name as name', 'request_types.id')
    //     ->groupBy('request_followups.request_type_id')
    //     ->where('supplier_proposals.user_id', $user->id)
    //     ->where('request_followups.request_type_id','!=',1)
    //     // ->whereBetween('request_followups.step_id', [6, 8])
    //     ->get();

    // //  dd( $request_follow_up);
    // $arrayOrder = array();
    // $title = 'request_types.name as titulo';
    // foreach ($request_follow_up as $req) {
    //     $table = $req->name . '_requests';

    //     $order = DB::table('request_followups')
    //         ->join($table, 'request_followups.request_id', $table . '.id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select('request_followups.*', $table . '.user_id as user',  $table . '.title as titulo',$table . '.description as descripcion'
    //         , 'request_types.name as tipo', $table . '.total_days as dias',)
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             // ['request_followups.step_id', '>=', '1','<=','5'],
    //             [$table . '.status', '=', '1'],
    //             ['supplier_proposals.user_id', $user->id],
    //             ['request_followups.request_type_id', $req->id],
    //             ['request_types.status', '=', '1'],
    //             ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],

    //         ])
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();
    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order);
    //         }
    //     }

    //     return response()->json($arrayOrder);
    // }
    // public function GetCloseOrderSupplierAdmin(Request $request)
    // {
    //     $user = Auth::user();

    //     $request_follow_up = DB::table('request_followups')
    //     ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //     ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //     ->join('users','supplier_proposals.user_id','users.id')
    //     ->select('request_types.name as name', 'request_types.id')
    //     ->groupBy('request_followups.request_type_id')
    //     ->where('users.organization_id', $user->organization_id)
    //     ->where('request_followups.request_type_id','!=',1)
    //     // ->whereBetween('request_followups.step_id', [6, 8])
    //     ->get();

    // //  dd( $request_follow_up);
    // $arrayOrder = array();
    // $title = 'request_types.name as titulo';
    // foreach ($request_follow_up as $req) {
    //     $table = $req->name . '_requests';

    //     $order = DB::table('request_followups')
    //         ->join($table, 'request_followups.request_id', $table . '.id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
    //         ->join('users','supplier_proposals.user_id','users.id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select('request_followups.*', 'users.organization_id',  $table . '.title as titulo',$table . '.description as descripcion'
    //         , 'request_types.name as tipo', $table . '.total_days as dias',)
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             // ['request_followups.step_id', '>=', '1','<=','5'],
    //             [$table . '.status', '=', '1'],
    //             ['users.organization_id', $user->organization_id],
    //             ['request_followups.request_type_id', $req->id],
    //             ['request_types.status', '=', '1'],
    //             ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],

    //         ])
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();

    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order);
    //         }
    //     }

    //     return response()->json($arrayOrder);
    // }
    // public function GetCloseOrderSupplierMro(Request $request)
    // {
    //     $user = Auth::user();      


    //     $order = DB::table('request_followups')
    //         ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select('request_followups.*', 'mro_requests.user_id as user', 'request_types.name as titulo'
    //         , 'request_types.name as tipo')
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             // ['request_followups.step_id', '>=', '1','<=','5'],
    //             ['mro_requests.status', '=', '1'],
    //             ['supplier_proposals.user_id', $user->id],
    //             ['request_followups.request_type_id', 1],
    //             ['request_types.status', '=', '1'],
    //             ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],

    //         ])
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();


    // return response()->json($order);
    // }
    // public function GetCloseOrderSupplierMroAdmin(Request $request)
    // {
    //     $user = Auth::user();


    //     $order = DB::table('request_followups')
    //         ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //           ->join('users','supplier_proposals.user_id','users.id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select('request_followups.*', 'users.organization_id', 'request_types.name as titulo', 'request_types.name as tipo')
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             // ['request_followups.step_id', '>=', '1','<=','5'],
    //             ['mro_requests.status', '=', '1'],
    //             ['users.organization_id', $user->organization_id],
    //             ['request_followups.request_type_id',1],
    //             ['request_types.status', '=', '1'],
    //             ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],

    //         ])
    //         ->whereBetween('request_followups.step_id', [6, 8])
    //         ->groupBy('request_followups.request_id')
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();



    // return response()->json($order);
    // }
    // public function AddCotizacionMro(Request $request)
    // {
    //     $user = Auth::user();

    //     if ($user) {
    //         $file = $request->file('myFile');
    //         $originalname = $file->getClientOriginalName();
    //         $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
    //         $urlFile = Storage::url($pathFile);

    //         $supplier = SupplierProposal::where('request_followup_id', $request->request_followup_id)
    //             ->where('user_id', 84)
    //             // real bd->where('user_id',129)
    //             ->update(['cotization_file' => $urlFile, 'total_days' => $request->input('time')]);

    //         if ($supplier) {
    //             $response['success'] = true;
    //             return $response;
    //         } else {
    //             $response['message'] = "Error al guardar";
    //             $response['success'] = false;
    //             return $response;
    //         }
    //     } else {

    //         $response['message'] = "Usuario no encontrado";
    //         $response['success'] = false;
    //         return $response;
    //     }
    // }
    public function AddSupplierCot(Request $request)
    {
        try {

            $user = Auth::user();
            $org = Organization::where('id', $user->organization_id)->first();

            $buyerVsM = VsUser::join('users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id',
                    'users.role_id',
                    'users.name',
                    'users.email',
                    'users.phone',
                )
                ->where('users.role_id', 5)
                ->orWhere('users.role_id', 3)
                ->where('vs_users.vs_id', $request->client_vs)
                ->get();

            $subtotal = $request->costo * $request->cantidad;
            $iva = $subtotal * $user->iva;
            $total_cost = $subtotal + $iva;
            // Se logro enviar el purchase desde el front.
            // $purchase_order = 'C-' . date_format(new DateTime($request->created_at), 'ymd') . '-' . $request->client_org_id . $request->type_code . '-' . $request->order_id;

            if ($user) {

                $file = $request->file('cotizacion');
                $originalname = $file->getClientOriginalName();
                $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $urlFile = Storage::url($pathFile);

                $supplier = SupplierProposal::where('service_id', $request->service_id)
                    ->where('user_id', $user->id)
                    ->where('subservice_id', $request->subservice_id)
                    ->increment('rev', 1, array(
                        'quote_file' => $urlFile, 
                        'total_cost' => $total_cost,
                        'unitary_subtotal_cost' => $request->costo,
                        'supplier_deadline' => $request->fecha_entrega,
                        'iva' => $iva,
                        'qty' => $request->cantidad
                    ));


                if ($supplier) {
                    $supp = SupplierProposal::where('service_id', $request->service_id)
                        ->where('user_id', $user->id)
                        ->where('subservice_id', $request->subservice_id)
                        ->first();

                    $log = SupplierProposalLog::create([
                        'supplier_proposal_id' => $supp->id,
                        'rev' => $supp->rev,
                        'total_cost' => $supp->total_cost,
                        'unitary_subtotal_cost'=>$supp->unitary_subtotal_cost,
                        'quote_file'=>$supp->quote_file,
                        'qty'=>$supp->qty,
                        'iva'=>$supp->iva,
                        'supplier_deadline'=>$supp->supplier_deadline,
                    ]);

                    if ($log) {
                        foreach ($buyerVsM as $bvm) {

                            $notification = new Notify();
                            $notification->user_id = $bvm->id;
                            $notification->notification_type_id = 4;
                            $notification->table_name = "services";
                            $notification->table_id = $request->order_id;
                            if ($notification->save()) {
                                DB::select('call limitNotificationCount (?)', array($bvm->id));
                            }

                            Notification::route('mail', $bvm->email)
                                ->notify(new OrderDetailsNotification(
                                    4,
                                    $bvm->role_id,
                                    $request->order_id,
                                    $request->purchase,
                                    $request->subservice_title,
                                    $user->name,
                                    $user->phone,
                                    $user->email,
                                    $org->name

                                ));
                        }

                        $response['message'] = "Supplier proposal info actualizada correctamente";
                        $response['success'] = true;
                        return $response;
                    } else {
                        $response['message'] = "Error al guardar en logs";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "Error al guardar";
                    $response['success'] = false;
                    return $response;
                }
            } else {

                $response['message'] = "Usuario no encontrado";
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function RechazarCotSupplier(Request $request)
    {
        // return $request;
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first();

        $buyerVsM = VsUser::join('users', 'vs_users.user_id', 'users.id')
            ->select(
                'users.id',
                'users.role_id',
                'users.name',
                'users.email',
                'users.phone',
            )
            ->where('users.role_id', 5)
            ->orWhere('users.role_id', 3)
            ->where('vs_users.vs_id', $request->client_vs)
            ->get();

        $purchase_order = 'C-' . date_format(new DateTime($request->created_at), 'ymd') . '-' . $request->client_org_id . $request->type_code . '-' . $request->order_id;

        $supplier = SupplierProposal::where('service_id', $request->service)
            ->where('user_id', $user->id)
            ->where('subservice_id', $request->subservice)
            ->update([
                'status' => 0,
            ]);

        if ($supplier) {

            foreach ($buyerVsM as $bvm) {
                Notification::route('mail', $bvm->email)
                    ->notify(new OrderDetailsNotification(
                        10,
                        $bvm->role_id,
                        $request->order_id,
                        $purchase_order,
                        $request->service_title,
                        $user->name,
                        $request->comentario,
                        $user->email,
                        $org->name

                    ));
            }

            $response['message'] = "Supplier proposal info actualizada correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar supplier";
            $response['success'] = false;
            return $response;
        }
    }
    public function RequestSupplierProposal($id, $tipo)
    {
        $user = Auth::user();
        if ($user) {
            if ($tipo == 1) {
                $cotizacionUser = DB::table('request_followups')
                    ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                    ->select('supplier_proposals.user_id')
                    ->where('supplier_proposals.user_id', $user->id)
                    ->where('supplier_proposals.status', 1)
                    ->get();

                if ($cotizacionUser) {
                    $response['supplier_proposal_notExist'] = false;
                    return $response;
                } else {
                    $response['supplier_proposal_notExist'] = true;
                    return $response;
                }
            } else {

                $cotizacionUser = SupplierProposal::where('request_followup_id', $id)
                    ->where('user_id', $user->id)
                    ->where('status', 1)
                    ->first('user_id');
                if ($cotizacionUser !== null) {
                    $cotizacionF = SupplierProposal::where('request_followup_id', $id)->where('user_id', $user->id)->first('cotization_file');
                    // dd($cotizacionF);
                    if ($cotizacionF->cotization_file !== null) {
                        $response['supplier_proposal_cotF'] = true;
                        return $response;
                    } else {
                        $response['supplier_proposal_cotF'] = false;
                        return $response;
                    }
                } else {
                    $response['supplier_proposal_notExist'] = true;
                    return $response;
                }
            }
        } else {
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetCotizationFiles($service, $id)
    {

        try {
            $user = Auth::user();
            if ($user) {
                // if ($service == 'mro') {
                //     $cotFiles = RequestFollowup::where('id', $id)->get();
                //     if ($cotFiles[0]->client_po_file !== null && $cotFiles[0]->supplier_po_file !== null) {
                //         return response()->json(['cotFile'=>$cotFiles[0]->client_po_file,'cotFile'=> $cotFiles[0]->supplier_po_file]);
                //     } elseif ($cotFiles[0]->client_po_file !== null) {
                //         return response()->json(['cotFile'=>$cotFiles[0]->client_po_file]);
                //     } elseif ($cotFiles[0]->supplier_po_file !== null) {
                //         return response()->json(['cotFile'=>$cotFiles[0]->supplier_po_file]);
                //     } else {
                //         $response['success'] = false;
                //         return $response;
                //     }

                // } else {
                // $cotFileRequest = DB::table('request_followups')
                //     ->join($service . '_requests', 'request_followups.request_id', $service . '_requests.id')
                //     ->select($service . '_requests.specifications_file as cotFile')
                //     ->where('request_followups.id', $id)
                //     ->get();
                $cotFileRequest = OrderFiles::where('request_followup_id', $id)->get('file as cotFile');

                if (!$cotFileRequest->isEmpty()) {

                    $cotFileSupplier = SupplierProposal::where('request_followup_id', $id)->where('user_id', $user->id)->get('cotization_file as cotFile');
                    // dd($cotFileSupplier);

                    if (!$cotFileSupplier->isEmpty()) {
                        if ($cotFileSupplier[0]->cotFile !== null) {

                            $supplierPoFile = RequestFollowup::where('id', $id)->get('supplier_po_file as cotFile');

                            if (!$supplierPoFile->isEmpty()) {
                                if ($supplierPoFile[0]->cotFile !== null) {

                                    return response()->json([$cotFileSupplier, $cotFileRequest, $supplierPoFile]);
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
                    $response['message'] = "No hay archivo de especificaciones";
                    $response['success'] = false;
                    return $response;
                }
                // }
            } else {
                $response['message'] = "Usuario no encontrado";
                $response['success'] = false;
                return $response;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $response['error'] = true;
            return $response;
        }
    }
    public function ordenesPerfilAdmin()
    {
        $orga = Auth::user()->organization_id;

        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select('users.id', 'service_requests.final_cost', 'users.name', 'users.role_id')
            ->where([
                ['users.organization_id', $orga],
                ['users.status', 1],
                ['service_requests.status', 1]
            ]);

        $ordenSerMro = DB::table('mro_requests')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->select('users.id', 'mro_requests.final_cost', 'users.name', 'users.role_id')
            ->where([
                ['users.organization_id', $orga],
                ['users.status', 1],
                ['mro_requests.status', 1]
            ])
            ->union($ordenService);

        $costo = DB::query()->fromSub($ordenSerMro, 'sr_mr')
            ->select('id as id_user', 'role_id', 'name', DB::raw('SUM(IFNULL(final_cost,0)) as suma'), DB::raw('COUNT(id) as ordenes'))
            ->groupBy('id')
            ->get();

        // dd($costo);
        return response()->json($costo);
    }

    public function Ganancias(Request $request)
    {
        $user = Auth::user();
        $current_year = date('Y');
        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name', 'request_types.id')
            ->groupBy('request_followups.request_type_id')
            ->get();
        $arrayOrder = array();
        $arrayOrderSum = array();
        foreach ($request_follow_up as $req) {
            $table = $req->name . '_requests';
            $order = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.request_type_id',
                    'request_followups.id',
                    'request_followups.purchase_order',
                    'request_followups.created_at',
                    'supplier_proposals.cost',
                )
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['supplier_proposals.user_id', '=', $user->id],
                    ['supplier_proposals.status', 1],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                ->orderBy('request_followups.created_at', 'asc')
                ->get();


            $orderSum = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    // 'request_followups.id',
                    'request_types.name as service',
                    // 'request_followups.purchase_order',
                    // 'request_followups.created_at',
                    // $table . '.final_cost',
                    DB::raw('sum(supplier_proposals.cost) as ganado, MONTHNAME(request_followups.created_at) month'),
                )
                // ->selectRaw('max(supplier_proposals.cost)  - '.$table.'.final_cost as ahorro')
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['supplier_proposals.user_id', '=', $user->id],
                    ['supplier_proposals.status', 1],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                ->groupBy('month')
                ->groupBy('service')
                ->get();

            array_push($arrayOrder, $order);
            array_push($arrayOrderSum, $orderSum);
        }
        return response()->json(['order' => $arrayOrder, 'orderSum' => $arrayOrderSum]);
    }

    public function GananciasResumen(Request $request)
    {
        $user = Auth::user();
        $current_year = date('Y');
        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name', 'request_types.id')
            ->groupBy('request_followups.request_type_id')
            ->get();
        // dd($request_follow_up);

        $collection = collect();
        foreach ($request_follow_up as $req) {
            $table = $req->name . '_requests';
            $order = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.purchase_order',
                    'request_followups.created_at',
                    'supplier_proposals.cost as costo',
                )
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    ['supplier_proposals.user_id', '=', $user->id],
                    ['supplier_proposals.status', 1],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                ->groupBy('supplier_proposals.request_followup_id')
                ->get();

            // array_push($arrayOrder,$order);

            $collection->push($order);
        }

        $newArr = $collection->collapse();
        $ganancia = $newArr->sum('costo');
        $servicios = $newArr->count('purchase_order');
        $gananciaAvg = round($newArr->avg('costo'), 2);
        return response()->json(['ganancia' => $ganancia,  'servicios' => $servicios, 'gananciaAvg' => $gananciaAvg]);
    }

    public function EditSupplierPartno(Request $request)
    {
        $upPart = PartNo::where('id', $request->id)
            ->update(['name' => $request->nombre, 'supplier_partno' => $request->partno, 'current_qty' => $request->qty]);
        if ($upPart) {
            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function SendProductAnswer(Request $request)
    {
        $user = Auth::user()->id;
        $upComment = ProductComment::where('id', $request->id)->update([
            'user_answer' => $user,
            'answer' => $request->respuesta
        ]);

        if ($upComment) {
            $client_user = Partno::select('user_id', 'name', 'supplier_partno', 'part_category_id')
                ->where('epno_part_id', $request->epno_part_id)
                ->first();

            $client_mail = User::where('id', $request->user_comment)->first('email');

            Notification::route('mail', $client_mail->email)
                ->notify(new ProductNotification(
                    2,
                    $client_user->name,
                    $client_user->supplier_partno,
                    $request->epno_part_id,
                    $client_user->part_category_id,
                ));

            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }
}
