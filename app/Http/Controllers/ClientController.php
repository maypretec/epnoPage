<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Models\Organization;
use App\Location;
use App\SoftwareRequest;
use App\Models\MroPart;
use App\EpnoPart;
use App\Models\PartNo;
use App\Models\MroRequest;
use App\RequestFollowup;
use App\RequestType;
use App\Models\Bundle;
use App\Models\BundlePart;
use App\Models\Complaint;
use App\Models\ComplaintClientToEpnoEvidence;
use App\Models\ComplaintLog;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceFile;
use App\Models\ServiceLog;
use App\Models\User;
use App\RequestFollowupLogs;
use App\RequestFollowupComment;
use App\Ratings;
use App\Models\Notification;
use App\Models\Subservice;
use App\Models\SubserviceLog;
use App\Models\VsUser;
use App\Notifications\OrderDetailsNotification;
use App\Notifications\ProductNotification;
use App\Notifications\RegisterRequestNotification;
use App\Notifications\SupplierNotification;
use App\OrderFiles;
use App\Models\ProductComment;
use App\Models\ServiceComment;
use App\Models\SubserviceComplaint;
use App\ServiceRequest;
use App\Models\SupplierProposal;
use App\Models\SupplierProposalLog;
use App\Notifications\ComplaintDetailsNotification;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification as Notify;
// use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function perfilCustomer(Request $request)
    {
        // return $request;
        $userAuth = Auth::user();
        // dd($userAuth);
        if ($userAuth) {
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

                    Notify::route('mail', 'contacto@epno.com.mx')
                        ->notify(new RegisterRequestNotification(
                            $request->input('organizacion'),
                            $request->input('rfc'),
                            $request->input('nombre_planta'),
                            $request->input('calle')
                        ));

                    $response['message'] = "Guardado correctamente";
                    $response['success'] = true;
                    $response['id'] = $organization->id;
                    return $response;
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
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }
    public function solicitudSoftware(Request $request)
    {
        // dd($request[0]['fecha']);
        $solicitude = new SoftwareRequest();
        $solicitude->user_id = 1;
        $solicitude->title = $request[0]['formValue']['title'];
        $solicitude->description = $request[0]['formValue']['description'];
        $solicitude->initial_meeting = $request[0]['fecha'];


        if ($solicitude->save()) {
            $service_id = RequestType::where('name', 'Software')->pluck('id');

            $nextStep = collect(DB::select('SELECT onChangeFollowUp_NextStepByType(?,?) AS nextStep', [$solicitude->id, $service_id[0]]))->first()->nextStep;

            $request_follow_up = new RequestFollowup();
            $request_follow_up->request_id = $solicitude->id;
            $request_follow_up->request_type_id = $service_id[0];
            $request_follow_up->step_id = $nextStep;
            $request_follow_up->supplier_proposal_id = 10;
            $request_follow_up->purchase_order = 'COT' . $solicitude->id . $nextStep;


            if ($request_follow_up->save()) {
                $request_follow_up_logs = new RequestFollowupLogs();
                $request_follow_up_logs->request_followup_id = $request_follow_up->id;
                $request_follow_up_logs->step_id = $request_follow_up->step_id;
                $request_follow_up_logs->user_id = $solicitude->user_id;

                if ($request_follow_up_logs->save()) {
                    $response['message'] = "Guardado correctamente";
                    $response['success'] = true;
                    $response['purchase_order'] = $request_follow_up->purchase_order;
                    return $response;
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
        }
    }

    public function GetCatalogoById($id)
    {
        // $id = DB::table('epno_parts')
        //     ->join('ranges', 'ranges.epno_part_id', '=', 'epno_parts.id')
        //     ->select('ranges.id')
        //     ->whereRaw('? between ranges.min_qty and ranges.max_qty', 1)
        //     ->where('ranges.status', '1')
        //     ->where('epno_parts.status', '1')
        //     ->get();


        // $arrayProd = array();

        // foreach ($id as $range) {
        // $catalogo = DB::table('epno_parts')
        //     ->join('ranges', 'epno_parts.id', '=', 'ranges.epno_part_id')
        //     ->join('range_markets', 'ranges.id', '=', 'range_markets.range_id')
        //     ->select('epno_parts.*')
        //     ->selectRaw('MIN(range_markets.price) as price')
        //     ->where('range_markets.status', '1')
        //     ->where('range_markets.range_id', $range->id)
        //     ->where('epno_parts.status', '1')
        //     ->get();
        //     array_push($arrayProd, $catalogo);
        // }

        $catalogo = DB::table('epno_parts')
            ->join('part_nos', 'part_nos.epno_part_id', '=', 'epno_parts.id')
            ->join('part_categories', 'part_categories.id', '=', 'epno_parts.part_category_id')
            ->select(
                'epno_parts.*',
                'part_nos.price as supp_price',
                'part_nos.subtotal as supp_subtotal',
                'part_nos.supplier_partno',
                'part_categories.name as categoria',
                'part_nos.id as sp_id',
                'part_nos.current_qty'
            )
            ->where('part_nos.status', '1')
            ->where('epno_parts.status', '1')
            ->where('epno_parts.part_category_id', $id)
            ->get();

        return response()->json($catalogo);
    }

    public function GetRelatedProducts($id, $category)
    {
        $catalogo = DB::table('epno_parts')
            ->join('part_nos', 'part_nos.epno_part_id', '=', 'epno_parts.id')
            ->join('part_categories', 'part_categories.id', '=', 'epno_parts.part_category_id')
            ->select(
                'epno_parts.*',
                'part_nos.price',
                'part_nos.supplier_partno',
                'part_categories.name as categoria',
                'part_nos.id as sp_id',
                'part_nos.current_qty'
            )
            ->where('part_nos.status', '1')
            ->where('epno_parts.status', '1')
            ->where('epno_parts.part_category_id', $category)
            ->where('epno_parts.id', '!=', $id)
            ->limit(5)
            ->get();

        return response()->json($catalogo);
    }
    public function GetProductoById($id)
    {
        $product = DB::table('epno_parts')
            ->join('part_nos', 'part_nos.epno_part_id', '=', 'epno_parts.id')
            ->join('part_categories', 'part_categories.id', '=', 'epno_parts.part_category_id')
            ->select(
                'epno_parts.*',
                'part_nos.price',
                'part_nos.supplier_partno',
                'part_categories.name as categoria',
                'part_nos.id as sp_id',
                'part_nos.current_qty'
            )
            ->where('part_nos.status', '1')
            ->where('epno_parts.status', '1')
            ->where('epno_parts.id', $id)
            ->first();

        return response()->json($product);
    }

    public function AddProducts(Request $request)
    {
        $user = Auth::user();
        $current = PartNo::where('id', $request->part_no_id)->first('current_qty');
        $qty = $request->qty;
        if ($qty > $current->current_qty) {
            $qty = $current->current_qty;
        }

        $deleteDuplicates = MroPart::where('epno_part_id', '=', $request->epno_part_id)->where('status', '=', 1)->where('user_id', $user->id)->first();

        if ($deleteDuplicates) {
            $cant = $deleteDuplicates->qty + $qty;
            $deleteDuplicates->qty = $cant;
            if ($deleteDuplicates->save()) {

                $response['message'] = "Actualizado correctamente";
                $response['success'] = true;
                return $response;
            } else {
                $response['message'] = "Error al actulizar";
                $response['success'] = false;
                return $response;
            }
        } else {
            $part = new MroPart();
            $part->user_id = $user->id;
            $part->epno_part_id = $request->epno_part_id;
            $part->part_no_id = $request->part_no_id;
            $part->part_cost = $request->cost;
            $part->qty = $qty;

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
    }

    public function AddBundleProducts($id)
    {
        $bundleInsert =  DB::select('call onCreateCart_AddBundleToCart (?)', array($id));

        if ($bundleInsert) {
            $response['message'] = "Agregado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al guardar";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetProducts()
    {
        $user = Auth::user()->id;

        $products = DB::table('mro_parts')
            ->join('epno_parts', 'mro_parts.epno_part_id', '=', 'epno_parts.id')
            ->join('units', 'epno_parts.unit_id', '=', 'units.id')
            ->join('part_nos', 'mro_parts.part_no_id', '=', 'part_nos.id')
            ->join('users', 'part_nos.user_id', '=', 'users.id')
            ->join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->join('colonies', 'organizations.colony_id', 'colonies.id')
            ->join('postal_codes', 'colonies.postal_code_id', 'postal_codes.id')
            ->join('cities', 'postal_codes.city_id', 'cities.id')
            ->join('states', 'cities.state_id', 'states.id')
            ->join('regions', 'states.region_id', 'regions.id')
            ->join('countries', 'regions.country_id', 'countries.id')
            ->select(
                'mro_parts.*',
                'epno_parts.name as name',
                'epno_parts.part_no as part_no',
                'epno_parts.part_category_id',
                'epno_parts.unit_id',
                'epno_parts.image as image',
                'epno_parts.price as epno_price',
                'part_nos.price as supp_price',
                'part_nos.subtotal as supp_subtotal',
                'part_nos.user_id as supp_user',
                'part_nos.name as supp_name',
                'part_nos.current_qty',
                'users.iva as supp_iva',
                'users.name as supp_username',
                'users.email as supp_email',
                'users.phone as supp_phone',
                'organizations.name as supp_org',
                'organizations.logo as supp_logo',
                'organizations.external_number as supp_ext_num',
                'organizations.street as supp_calle',
                'units.name as um',
                'colonies.name as supp_colonia',
                'postal_codes.name as supp_CP',
                'cities.name as supp_ciudad',
                'states.name as supp_estado',
                'countries.name as supp_pais'
            )
            ->where([
                ['mro_parts.status', '=', '1'],
                ['epno_parts.status', '=', '1'],
                ['mro_parts.user_id', '=', $user],
            ])
            ->get();

        $total = DB::table('mro_parts')
            ->where('mro_parts.status', 1)
            ->where('mro_parts.user_id', $user)
            ->sum(DB::raw('mro_parts.part_cost * mro_parts.qty'));

        $supp_total = DB::table('mro_parts')
            ->join('part_nos', 'mro_parts.part_no_id', '=', 'part_nos.id')
            ->where('mro_parts.status', 1)
            ->where('mro_parts.user_id', $user)
            ->sum(DB::raw('part_nos.price * mro_parts.qty'));

        $supp_subtotal = DB::table('mro_parts')
            ->join('part_nos', 'mro_parts.part_no_id', '=', 'part_nos.id')
            ->where('mro_parts.status', 1)
            ->where('mro_parts.user_id', $user)
            ->sum(DB::raw('part_nos.subtotal * mro_parts.qty'));

        $count = MroPart::where('status', 1)->where('user_id', $user)->count();

        return response()->json([
            'products' => $products,
            'total' => $total,
            'supp_total' => $supp_total,
            'supp_subtotal' => $supp_subtotal,
            'count' => $count
        ]);
    }
    // public function GetTotal()
    // {
    //     $user_id = Auth::user()->id;
    //     $total = DB::table('mro_parts')
    //         ->where('mro_parts.status', 1)
    //         ->where('mro_parts.user_id', $user_id)
    //         ->sum(DB::raw('mro_parts.part_cost * mro_parts.qty'));

    //     // dd($total);

    //     return response()->json($total);
    // }

    public function DeleteProducts($id)
    {
        // dd($id);
        $delete = MroPart::where('id', $id)->update(['status' => 0]);

        if ($delete) {
            $response['message'] = "Actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar";
            $response['success'] = false;
            return $response;
        }
    }

    function AddRequest(Request $request)
    {
        try {
            $user = Auth::user();
            $org = Organization::where('id', $user->organization_id)->first('name');
            $cot_date = new DateTime();
            $return_amount = $request->subtotal * 0.35;
            $vs = VsUser::where('user_id', $user->id)->first('vs_id');
            $product_array = array();

            $buyer = VsUser::join('users', 'vs_users.user_id', 'users.id')
                // ->join('organizations', 'users.organization_id', 'organizations.id')
                ->select(
                    'users.id',
                    'users.role_id',
                    // 'users.name',
                    'users.email',
                    // 'users.phone',
                    // 'organizations.name as org'
                )
                ->where('users.role_id', 5)
                ->where('vs_users.vs_id', $vs->vs_id)
                ->first();


            $iva_supplier = 0;

            $sp_array = array();

            foreach ($request->id_mro_parts as $sp) {

                array_push($sp_array, $sp['supp_username']);

                $products = [
                    'precio_unitario' => $sp['epno_price'],
                    'descripcion' => $sp['name'],
                    'qty' => $sp['qty'],
                    'um' => $sp['um'],
                ];

                array_push($product_array, $products);

                $iva_supplier = $iva_supplier + ($sp['supp_subtotal'] * $sp['qty'] * $sp['supp_iva']);
            }
            $suppliers = json_encode($sp_array);



            $order = Order::create([
                'cot_date' => $cot_date->format('Y-m-d'),
                'concept_order' => 'Requerimiento de MRO',
                'iva' => $request->precio_iva,
                'total_client' => $request->finalCost,
                'subtotal_client' => $request->subtotal,
                'iva_supplier' => $iva_supplier,
                'total_supplier' => $request->supp_total,
                'subtotal_supplier' => $request->supp_subtotal,
                'return_amount' => $return_amount,
                'buyer' => $buyer->id,
                'supplier' => $suppliers,
                'client_org' => $user->organization_id,
                'client_name' => $org->name,
                'is_po' => 1,
                'service_type_id' => 1,
            ]);


            if ($order) {
                $purchase_order = 'O-' . date_format($order->created_at, 'ymd') . '-' . $user->organization_id . '03' . '-' . $order->id;

                $data = [
                    'org' => $org->name,
                    'client_name' => $user->name,
                    'iva' => 8,
                    'precio_iva' => $request->precio_iva,
                    'user_name' => $user->name,
                    'fecha_entrega' => Carbon::now()->add(14, 'day')->format('Y-m-d'),
                    'dias_validos' => 90,
                    'vigencia' => 2,
                    'total' => $request->finalCost,
                    'currency' => "MXN",
                    'purchase' => $purchase_order,
                    'tipo_cambio' => 0,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'subtotal' => $request->subtotal,
                    'products' => $product_array,
                ];

                $fila_name = $org->name . '-' . $purchase_order . '.pdf';
                PDF::loadView('cotizacionEN', $data)
                    ->save(storage_path('app/public/uploads/') . $fila_name);
                $url = '/storage/uploads//' . $fila_name;


                $mro_request = new Service();
                $mro_request->order_id = $order->id;
                $mro_request->order_num = $purchase_order;
                $mro_request->user_id = $user->id;
                $mro_request->title = "Servicio de MRO";
                $mro_request->description = "Requerimientos del catalogo de MRO.";
                $mro_request->step_id = 4;
                $mro_request->type = "MRO";
                $mro_request->client_cost = $request->finalCost;
                $mro_request->supplier_cost = $request->supp_total;
                $mro_request->quote_file = $url;


                if ($mro_request->save()) {
                    $service_logs = new ServiceLog();
                    $service_logs->service_id = $mro_request->id;
                    $service_logs->step_id = $mro_request->step_id;
                    $service_logs->user_id = $mro_request->user_id;

                    if ($service_logs->save()) {

                        foreach ($request->id_mro_parts as $id) {
                            $iva_spp = $id['supp_subtotal'] * $id['qty'] * $id['supp_iva'];

                            $subservice = Subservice::create([
                                'service_id' => $mro_request->id,
                                'name' => $id['name'],
                                'step_id' => $mro_request->step_id,
                                'qty' => $id['qty'],
                                'category_id' => $id['part_category_id'],
                                'unit_id' => $id['unit_id'],
                                'specs_file' => $id['image'],
                            ]);
                            if ($subservice) {
                                SubserviceLog::create([
                                    'subservice_id' => $subservice->id,
                                    'step_id' => $subservice->step_id,
                                    'user_id' => $user->id,
                                ]);

                                $countFiles = ServiceFile::where('service_id', $mro_request->id)->count();
                                if ($countFiles < 25) {
                                    ServiceFile::create([
                                        'service_id' => $mro_request->id,
                                        'file' => $id['image'],
                                    ]);
                                }

                                $prod_spp = array(
                                    [
                                        'precio_unitario' => $id['supp_subtotal'],
                                        'descripcion' => $id['supp_name'],
                                        'qty' => $id['qty'],
                                        'um' => $id['um'],
                                    ]
                                );
                                $logo = explode("/storage/uploads//", $id['supp_logo']);

                                $data_spp = [
                                    'org' => 'EPNO CORPORATION S DE RL DE CV',
                                    'org_supp' => $id['supp_org'],
                                    'iva' => $id['supp_iva'] * 100,
                                    'precio_iva' => $iva_spp,
                                    'user_name' =>  $id['supp_username'],
                                    'client_name' => 'Veronica Mata',
                                    'fecha_entrega' => Carbon::now()->add(14, 'day')->format('Y-m-d'),
                                    'dias_validos' => 90,
                                    'vigencia' => 2,
                                    'total' => $id['supp_subtotal'] * $id['qty'] + $iva_spp,
                                    'currency' => "MXN",
                                    'purchase' => $purchase_order,
                                    'tipo_cambio' => 0,
                                    'date' => Carbon::now()->format('Y-m-d'),
                                    'subtotal' => $id['supp_subtotal'] * $id['qty'],
                                    'products' => $prod_spp,
                                    'logo' => $logo[1],
                                    'calle' => $id['supp_calle'],
                                    'num_ext' => $id['supp_ext_num'],
                                    'ciudad' => $id['supp_ciudad'],
                                    'estado' => $id['supp_estado'],
                                    'pais' => $id['supp_pais'],
                                ];

                                $fila_name_sp = $id['supp_org'] . '-' . $purchase_order . '.pdf';
                                PDF::loadView('cotizacionENSUPP', $data_spp)
                                    ->save(storage_path('app/public/uploads/') . $fila_name_sp);
                                $url_spp = '/storage/uploads//' . $fila_name_sp;

                                $supp = SupplierProposal::create([
                                    'service_id' => $mro_request->id,
                                    'subservice_id' => $subservice->id,
                                    'user_id' => $id['supp_user'],
                                    // 'supplier_code'=>,
                                    'unitary_subtotal_cost' => $id['supp_subtotal'],
                                    'description' => $id['supp_name'],
                                    'epno_cost' => $id['epno_price'],
                                    'quote_file' => $url_spp,
                                    'total_cost' => $id['supp_subtotal'] * $id['qty'] + $iva_spp,
                                    'qty' => $id['qty'],
                                    'iva' => $iva_spp,
                                    // 'epno_po_file'=>,
                                    'is_winner' => 1,
                                ]);

                                if ($supp) {
                                    SupplierProposalLog::create([
                                        'supplier_proposal_id' => $supp->id,
                                        'rev' => 1,
                                        'cost' => $supp->total_cost,
                                    ]);

                                    $notification = new Notification();
                                    $notification->user_id = $id['supp_user'];
                                    $notification->notification_type_id = 2;
                                    $notification->table_name = "services";
                                    $notification->table_id = $order->id;
                                    if ($notification->save()) {
                                        DB::select('call limitNotificationCount (?)', array($id['supp_user']));
                                    }

                                    Notify::route('mail', $id['supp_email'])
                                        ->notify(new OrderDetailsNotification(
                                            2,
                                            6,
                                            $order->id,
                                            $purchase_order,
                                            $mro_request->title,
                                            $user->name,
                                            $user->phone,
                                            $user->email,
                                            $org->name,
                                        ));

                                    MroPart::where('id', $id['id'])->update(['status' => 0, 'service_id' => $mro_request->id]);
                                    PartNo::where('id', $id['part_no_id'])->decrement('current_qty', $id['qty']);
                                    $Part_info_up = PartNo::select('current_qty', 'min_qty', 'supplier_partno', 'name', 'user_id')->where('id', $id['part_no_id'])->first();

                                    if ($Part_info_up->current_qty == $Part_info_up->min_qty) {
                                        Notify::route('mail', $id['supp_email'])
                                            ->notify(new SupplierNotification(
                                                1,
                                                $Part_info_up->supplier_partno,
                                                $Part_info_up->name,

                                            ));
                                    } elseif ($Part_info_up->current_qty == ($Part_info_up->min_qty * 0.1)) {
                                        Notify::route('mail', $id['supp_email'])
                                            ->notify(new SupplierNotification(
                                                2,
                                                $Part_info_up->supplier_partno,
                                                $Part_info_up->name,

                                            ));
                                    } elseif ($Part_info_up->current_qty == 0) {
                                        Notify::route('mail', $id['supp_email'])
                                            ->notify(new SupplierNotification(
                                                3,
                                                $Part_info_up->supplier_partno,
                                                $Part_info_up->name,

                                            ));
                                    }
                                }
                            }
                        }

                        //notificacion al usuario
                        $notificationUser = new Notification();
                        $notificationUser->user_id = $user->id;
                        $notificationUser->notification_type_id = 6;
                        $notificationUser->table_name = "services";
                        $notificationUser->table_id = $order->id;
                        if ($notificationUser->save()) {
                            DB::select('call limitNotificationCount (?)', array($user->id));
                        }

                        Notify::route('mail', $user->email)
                            ->notify(new OrderDetailsNotification(
                                6,
                                $user->role_id,
                                $order->id,
                                $purchase_order,
                                $mro_request->title,
                                $user->name,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));

                        //    Notificacion al comprador 
                        $notificationBuyer = new Notification();
                        $notificationBuyer->user_id = $buyer->id;
                        $notificationBuyer->notification_type_id = 6;
                        $notificationBuyer->table_name = "services";
                        $notificationBuyer->table_id = $order->id;
                        if ($notificationBuyer->save()) {
                            DB::select('call limitNotificationCount (?)', array($buyer->id));
                            Notify::route('mail', $buyer->email)
                                ->notify(new OrderDetailsNotification(
                                    6,
                                    $buyer->role_id,
                                    $order->id,
                                    $purchase_order,
                                    $mro_request->title,
                                    $user->name,
                                    $user->phone,
                                    $user->email,
                                    $org->name,
                                ));

                            $response['purchase_order'] = $purchase_order;
                            $response['cotFile'] = $url;
                            $response['success'] = true;
                            return $response;
                        }
                    } else {
                        $response['message'] = "Error al guardar en mro_request log";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "Error al guardar en mro_request";
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['message'] = "Error al guardar en orders";
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    // public function GetNumberProducts()
    // {
    //     $user = Auth::user()->id;
    //     $count = MroPart::where('status', 1)->where('user_id', $user)->count();
    //     return response()->json($count);
    //     // dd($count);
    // }
    public function AddPackage(Request $request)
    {
        // return $request;
        $user = Auth::user();
        if ($user) {
            $req = $request->all();
            $package = Bundle::where('name', $req['name'])->where('user_id', $user->id)->first();

            if ($package) {
                $response['existe_paquete'] = true;
                return $response;
            } else {
                // dd('entro en crear nuevo paquete');
                $newPackage = new Bundle();
                $newPackage->user_id = $user->id;
                $newPackage->name = $request->input('name');
                if ($newPackage->save()) {
                    $response['message'] = "Guardado correctamente";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "Error al guardar";
                    $response['success'] = false;
                    return $response;
                }
            }
        } else {
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetPackage(Request $request)

    {
        $user = Auth::user();
        if ($user) {

            $packages = DB::table('bundles')
                ->leftJoin(DB::raw('(select * from bundle_parts where status = 1) as b'), 'b.bundle_id', '=', 'bundles.id')
                ->select('bundles.*', 'b.id as bundle_part_id', DB::raw('count(b.bundle_id) as count'))
                ->where([['bundles.status', '1'], ['user_id', $user->id]])
                ->orderBy('bundles.created_at', 'desc')
                ->groupBy('bundles.name')
                ->get();

            //    dd($packages);
            return response()->json($packages);
        } else {
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    public function AddProductToPackage(Request $request)
    {
        try {

            $deleteDuplicates = BundlePart::where('epno_part_id', '=', $request->epno_part_id)->where('bundle_id', '=', $request->bundle_id)->where('status', '=', 1)->first();

            if ($deleteDuplicates) {
                $cant = $deleteDuplicates->qty + $request->qty;
                $deleteDuplicates->qty = $cant;
                if ($deleteDuplicates->save()) {
                    $response['message'] = "Producto actualizado correctamente en tu bundle.";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "Hubo un error al actualizar tu producto en el bundle seleccionado.";
                    $response['success'] = false;
                    return $response;
                }
            } else {

                $part = new BundlePart();
                $part->bundle_id = $request->bundle_id;
                $part->epno_part_id = $request->epno_part_id;
                $part->qty = $request->qty;

                if ($part->save()) {
                    $response['message'] = "El producto se agrego correctamente a tu paquete";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "Hubo un problema al agregar el producto.";
                    $response['success'] = false;
                    return $response;
                }
            }
        } catch (\Throwable $th) {
            $response['message'] = "Hubo un problema al agregar el producto.";
            $response['success'] = false;
            return $response;
        }
    }
    public function GetBundleProducts($id)
    {
        $bundles = DB::select('CALL getBundlePartInfo(?)', array($id));
        // dd($bundles);
        return response()->json($bundles);
    }
    public function DeleteBundlePart($id)
    {
        // dd($id);
        $delete = BundlePart::where('id', $id)->update(['status' => 0]);
        if ($delete) {
            $response['message'] = "Producto borrado correctamente.";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Hubo un error al borrar el producto.";
            $response['success'] = false;
            return $response;
        }
    }

    // public function GetOpenOrders($id, $type)
    // {
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->groupBy('request_followups.request_type_id')
    //         ->get();
    //     $arrayOrder = array();
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';

    //         if ($type == 1) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     [$table . '.user_id', '=', $user->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
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
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     [$table . '.user_id', '=', $user->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 2) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     [$table . '.user_id', '=', $user->id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
    //                 ])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         }


    //         array_push($arrayOrder, $order);
    //     }

    //     if ($arrayOrder !== null && $arrayOrder !== []) {

    //         return response()->json($arrayOrder[0]);
    //     } else {
    //         return response()->json($arrayOrder);
    //     }
    // }
    // public function GetOpenOrdersAdmin($id, $type)
    // {
    //     $user = User::where('id', $id)->first();
    //     // return $user;
    //     // $user = Auth::user();
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->groupBy('request_followups.request_type_id')
    //         ->get();
    //     $arrayOrder = array();
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';
    //         if ($type == 1) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('users', $table . '.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     'users.id as user_id',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', $user->organization_id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
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
    //                 ->join('users', $table . '.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     'users.id as user_id',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', '=', $user->organization_id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //         } elseif ($type == 2) {
    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('service_categories', 'service_categories.id', $table . '.service_category_id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('users', $table . '.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     'users.organization_id',
    //                     'users.id as user_id',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo',
    //                     'service_categories.name as categoria'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', '=', $user->organization_id],
    //                     ['request_types.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
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
    // public function GetOpenOrdersMro($id, $type)
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
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.total_days as dias',
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['mro_requests.user_id', '=', $user->id],
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
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.total_days as dias',
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['mro_requests.user_id', '=', $user->id],
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
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'mro_requests.user_id as user',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo',
    //                 'supplier_proposals.total_days as dias',
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['mro_requests.user_id', '=', $user->id],
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
    // public function GetOpenOrdersMroAdmin($id, $type)
    // {
    //     $user = User::where('id', $id)->first();
    //     // $user = Auth::user();

    //     if ($type == 1) {
    //         $order = DB::table('request_followups')
    //             ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('users', 'mro_requests.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', '=', $user->organization_id],
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
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('users', 'mro_requests.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', '=', $user->organization_id],
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
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('users', 'mro_requests.user_id', 'users.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->select(
    //                 'request_followups.*',
    //                 'users.organization_id',
    //                 'supplier_proposals.total_days as dias',
    //                 'request_types.name as titulo',
    //                 'request_types.name as tipo'
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 // ['request_followups.step_id', '>=', '1','<=','5'],
    //                 ['mro_requests.status', '=', '1'],
    //                 ['users.organization_id', '=', $user->organization_id],
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

    //     public function GetCloseOrders(Request $request)
    //     {
    //         $user = Auth::user();
    //         $request_follow_up = DB::table('request_followups')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->select('request_types.name as name', 'request_followups.request_id as request_id', 'request_types.id')
    //             ->where('request_followups.request_type_id', '!=', 1)
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->groupBy('request_followups.request_type_id')
    //             ->get();

    //         // dd($request_follow_up);

    //         $arrayOrder = array();
    //         $title = 'request_types.name as titulo';
    //         foreach ($request_follow_up as $req) {
    //             $table = $req->name . '_requests';
    //             $req_id = $req->request_id;

    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     [$table . '.user_id', '=', $user->id],
    //                     ['request_types.status', '=', '1'],
    //                     // ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //             // dd($order);
    //             if (!$order->isEmpty()) {
    //                 array_push($arrayOrder, $order);
    //             }
    //         }

    //         return response()->json($arrayOrder);
    //     }
    //     public function GetCloseOrdersAdmin(Request $request)
    //     {
    //         $user = Auth::user();
    //         $request_follow_up = DB::table('request_followups')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->select('request_types.name as name', 'request_followups.request_id as request_id', 'request_types.id')
    //             ->where('request_followups.request_type_id', '!=', 1)
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->groupBy('request_followups.request_type_id')
    //             ->get();

    //         // dd($request_follow_up);

    //         $arrayOrder = array();
    //         $title = 'request_types.name as titulo';
    //         foreach ($request_follow_up as $req) {
    //             $table = $req->name . '_requests';
    //             $req_id = $req->request_id;

    //             $order = DB::table('request_followups')
    //                 ->join($table, 'request_followups.request_id', $table . '.id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('users', $table . '.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select(
    //                     'request_followups.*',
    //                     $table . '.user_id as user',
    //                     $table . '.title as titulo',
    //                     $table . '.total_days as dias',
    //                     $table . '.description as descripcion',
    //                     'request_types.name as tipo'
    //                 )
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     [$table . '.status', '=', '1'],
    //                     ['users.organization_id', '=', $user->organization_id],
    //                     ['request_types.status', '=', '1'],
    //                     // ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', $req->id],
    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();
    //             // dd($order);
    //             if (!$order->isEmpty()) {
    //                 array_push($arrayOrder, $order);
    //             }
    //         }

    //         return response()->json($arrayOrder);
    //     }
    //     public function GetCloseOrdersMro(Request $request)
    //     {
    //         $user = Auth::user();
    //         $order = DB::table('request_followups')
    //      ->join('mro_requests', 'request_followups.request_id','mro_requests.id')
    //      ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //      // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //      ->join('steps', 'request_followups.step_id', 'steps.id')
    //      ->select('request_followups.*', 'mro_requests.user_id as user','request_types.name as titulo'
    //      , 'request_types.name as tipo')
    //      ->where([
    //          ['request_followups.status', '=', '1'],
    //          ['mro_requests.status', '=', '1'],
    //          ['mro_requests.user_id', '=', $user->id],
    //          ['request_types.status', '=', '1'],
    //          // ['supplier_proposals.status', '=', '1'],
    //          ['steps.status', '=', '1'],
    //          ['request_followups.request_type_id',1],
    //      ])
    //      ->whereBetween('request_followups.step_id', [6, 8])
    //      ->groupBy('request_followups.request_id')
    //      ->orderBy('request_followups.created_at', 'desc')
    //      ->get();


    // return response()->json($order);
    //     }
    //     public function GetCloseOrdersMroAdmin(Request $request)
    //     {
    //         $user = Auth::user();


    //             $order = DB::table('request_followups')
    //                 ->join('mro_requests', 'request_followups.request_id','mro_requests.id')
    //                 ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //                 ->join('users', 'mro_requests.user_id', 'users.id')
    //                 ->join('steps', 'request_followups.step_id', 'steps.id')
    //                 ->select('request_followups.*', 'users.organization_id', 'request_types.name as titulo', 'request_types.name as tipo')
    //                 ->where([
    //                     ['request_followups.status', '=', '1'],
    //                     // ['request_followups.step_id', '>=', '1','<=','5'],
    //                     ['mro_requests.status', '=', '1'],
    //                     ['users.organization_id', '=', $user->organization_id],
    //                     ['request_types.status', '=', '1'],
    //                     // ['supplier_proposals.status', '=', '1'],
    //                     ['steps.status', '=', '1'],
    //                     ['request_followups.request_type_id', 1],
    //                 ])
    //                 ->whereBetween('request_followups.step_id', [6, 8])
    //                 ->groupBy('request_followups.request_id')
    //                 ->orderBy('request_followups.created_at', 'desc')
    //                 ->get();


    //         return response()->json($order);
    //     }


    public function GetOrderById($role, $id)
    {
        $user = Auth::user()->id;
        $request_follow_up = DB::table('request_followups')
            ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
            ->select('request_types.name as name')
            ->where('request_followups.id', $id)
            ->get();

        // dd($request_follow_up);
        // if ($request_follow_up[0]->name == "MRO") {

        //     $orderId = DB::table('request_followups')
        //         ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
        //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
        //         // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
        //         ->join('steps', 'request_followups.step_id', 'steps.id')
        //         ->select('request_followups.*',  'mro_requests.user_id as user',  'mro_requests.final_cost as price', 'request_types.name as tipo')
        //         ->where('request_followups.id', $id)
        //         ->get();
        // } else {
        $table = $request_follow_up[0]->name . '_requests';
        if ($role == 3 || $role == 1 || $role == 4 || $role == 2) {
            $orderId = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('users',  $table . '.user_id', 'users.id')
                ->join('organizations',  'users.organization_id', 'organizations.id')
                // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.*',
                    $table . '.user_id as user',
                    'users.name as user_name',
                    'users.email as user_mail',
                    'users.role_id as user_role',
                    'organizations.name as org',
                    $table . '.final_cost as price',
                    $table . '.qty',
                    'request_types.name as tipo',
                    $table . '.title as titulo',
                    $table . '.total_days as dias',
                    $table . '.description as descripcion',
                    $table . '.service_category_id',

                )
                ->where('request_followups.id', $id)
                ->get();
        } else if ($role == 6 || $role == 5) {
            $orderId = DB::table('request_followups')
                ->join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('supplier_proposals', 'supplier_proposals.request_followup_id', 'request_followups.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.*',
                    $table . '.user_id as user',
                    // 'supplier_proposals.cost as price_unitario',
                    'request_types.name as tipo',
                    $table . '.title as titulo',
                    $table . '.qty',
                    $table . '.total_days as dias',
                    $table . '.description as descripcion',
                    DB::raw('SUM(supplier_proposals.cost*' . $table . '.qty) as price')
                )

                ->where('request_followups.id', $id)
                ->where('supplier_proposals.user_id', $user)
                ->get();
        }
        // }

        // dd($orderId);
        return response()->json($orderId);
    }
    public function GetOrderLogById($id)
    {
        $orderLog = RequestFollowupLogs::where('request_followup_id', $id)->get();
        // dd($orderLog);
        return response()->json($orderLog);
    }

    public function consumoClient(Request $request)
    {
        $org = Auth::user()->organization_id;
        $rol = Auth::user()->role_id;
        $id = Auth::user()->id;

        $date1 = Carbon::now()->subMonth(1);
        $date2 = Carbon::now();

        if ($rol == 3) {
            $service = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('service_requests.user_id', 'service_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('service_requests.created_at', [$date1, $date2])
                ->whereBetween('users.role_id', [3, 4])
                ->where('users.organization_id', $org)
            ;

            $serviceMro = DB::table('mro_requests')
                ->join('users', 'mro_requests.user_id', 'users.id')
                ->select('mro_requests.user_id', 'mro_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('mro_requests.created_at', [$date1, $date2])
                ->whereBetween('users.role_id', [3, 4])
                ->where('users.organization_id', $org)
                ->union($service)
                //->avg(DB::raw('final_cost'))
                ->get();
        } else if ($rol == 4) {
            $service = DB::table('service_requests')
                ->join('users', 'service_requests.user_id', 'users.id')
                ->select('service_requests.user_id', 'service_requests.final_cost', 'users.role_id', 'users.organization_id')
                ->whereBetween('service_requests.created_at', [$date1, $date2])
                ->where([['users.id', $id], ['users.organization_id', $org]])
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

        dd($serviceMro);
    }

    public function profileLocationStd(Request $request)
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
                'locations.id',
                'locations.name',
                'colonies.name as colonie',
                'postal_codes.name as CP',
                'cities.name as city',
                'states.name as state',
                'countries.name as country'
            )
            ->where([['users.id', $id], ['locations.organization_id', $org]])
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
                ->join('users',  $table . '.user_id', 'users.id')
                ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_types.name as service',
                    'request_followups.purchase_order',
                    'request_followups.created_at as fecha',
                    $table . '.final_cost',
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
                    ['users.organization_id', '=', $org],
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
                // 'desviacion' => round(($group->sum('final_cost') - $group->avg('final_cost')), 2),
                'month' => $month,
                'order' => $group,
            ];
        });

        return response()->json($groupwithcount);
    }

    public function AddService(Request $request)
    {
        #region Validations
        $auth = Auth::user();
        $days_to_add=1;
        $strLenght=40;
        $date=date("Y-m-d");
        $fileQty=10;
        $fileSize=2;
        $mimeTypes=['text/csv','text/plain','application/msword','application/pdf','image/jpeg','application/rtf','application/vnd.ms-excel'];
        if  (strlen($request->title)>$strLenght){
            Log::error('Service Title to long at clientController->addService. user: '. $auth->name);
            return response('Invalid data on title', 400);
        }
        // if ($request->time <= date("Y-m-d",("Y-m-d") + strtotime(1 ."days"))){
        //     Log::error('Invalid date at clientController->addService. user: '. $auth->name);
        //    return response($request->time); 
            
            // return response('Invalid data on time', 400);
        //}
        if($request->collect('fileList')->count()>$fileQty){
            Log::error('To many files at clientController->addService. user: '. $auth->name);
            return response('to many files', 413);
        }
        else {
            foreach ($request->collect('fileList') as $file) {
                if($file->getSize()>($fileSize*1024)){
                    Log::error('Loaded file to big at clientController->addService. user: '. $auth->name);
                    return response('file size to big', 413);
                }
                if(!in_array($file->getMimeType(),$mimeTypes)){
                    Log::error('Wrong file type at clientController->addService. user: '. $auth->name);
                    return response('wrong file type', 415);
                }
            }
        }

        #endregion
        
        $user = User::join('organizations', 'users.organization_id', 'organizations.id')
            ->join('vs_users', 'vs_users.user_id', 'users.id')
            ->where('users.id', $auth->id)
            ->select('users.*', 'organizations.name as org', 'vs_users.vs_id')
            ->first();
        $order = Order::create([
            'service_type_id'   => 2,
            'client_org'        => $user->organization_id,
            'client_name'       => $user->org,
            'concept_order'     => $request->title,
            'buyer'             => 1,
            'cot_date'          => date("Y-m-d"),
            'expiration_date'   => $request->time,
        ]);

        if (!$order) {
            Log::error('Couldn\'t save order at clientController->addService. user: '. $auth->name);
            return response('Order not saved', 400);
        }

        $solicitude = new Service();
        $solicitude->order_id = $order->id;
        $solicitude->user_id = $auth->id;
        $solicitude->title = $request->title;
        $solicitude->description = $request->description;
        $solicitude->step_id = 1;
        $solicitude->client_deadline = $request->time;

        if (!$solicitude->save()) {
            Log::error('Couldn\'t save order at clientController->addService. user: '. $auth->name);
            return response('Order not saved', 400);
        }

        $purchase_order = 'C-' . date_format($order->created_at, 'ymd') . '-' . $user->organization_id . '00' . '-' . $order->id;
        if ($request->file('files')) {
            
            // $files = [];
            // if ($request->file('files')){
            //     foreach($request->file('files') as $key => $file)
            //     {
            //         $fileName = time().rand(1,99).'.'.$file->extension();  
            //         $file->move(public_path('uploads'), $fileName);
            //         $files[]['name'] = $fileName;
            //     }
            // }

        }
        foreach ($request->collect('fileList') as $file) {
            $countFiles = ServiceFile::where('service_id', $solicitude->id)->count();
            if ($countFiles < 25) {
                $originalname = $file->getClientOriginalName();
                $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $urlFile = Storage::url($pathFile);
                ServiceFile::create([
                    'service_id' => $solicitude->id,
                    'file' => $urlFile,
                ]);
            }
        }

        $notificationUser = new Notification();
        $notificationUser->user_id = $solicitude->user_id;
        $notificationUser->notification_type_id = 21;
        $notificationUser->table_name = "services";
        $notificationUser->table_id = $order->id;
        if (!$notificationUser->save()) {
            Log::error('Couldn\'t save order at clientController->addService. user: '. $auth->name);
            return response('Order not saved', 400);
        }
        DB::select('call limitNotificationCount (?)', array($solicitude->user_id));
        
        // Notify::route('mail', $user->email)
        //     ->notify(new OrderDetailsNotification(
        //         21,
        //         $user->role_id,
        //         $order->id,
        //         $purchase_order,
        //         $solicitude->title,
        //         $user->name,
        //         $user->phone,
        //         $user->email,
        //         $user->org,
        //     ));

        $service_logs = new ServiceLog();
        $service_logs->service_id = $solicitude->id;
        $service_logs->step_id = $solicitude->step_id;
        $service_logs->user_id = $solicitude->user_id;

        if (!$service_logs->save()) {
            Log::error('Couldn\'t save order at clientController->addService. user: '. $auth->name);
            return response('Order not saved', 400);
        }
        $ComprasId = User::join('vs_users', 'vs_users.user_id', 'users.id')
            ->select('users.id', 'users.email')
            ->where('role_id', 5)
            ->where('vs_users.vs_id', $user->vs_id)
            ->get();

        foreach ($ComprasId as $id) {

            $notification = new Notification();
            $notification->user_id = $id->id;
            $notification->notification_type_id = 21;
            $notification->table_name = "services";
            $notification->table_id = $order->id;
            if ($notification->save()) {
                DB::select('call limitNotificationCount (?)', array($id->id));
            }

            // Notify::route('mail', $id->email)
            //     ->notify(new OrderDetailsNotification(
            //         21,
            //         5,
            //         $order->id,
            //         $purchase_order,
            //         $solicitude->title,
            //         $user->name,
            //         $user->phone,
            //         $user->email,
            //         $user->org,
            //     ));
        }
        Log::info('Order generated:'.$order->id. ' user: '. $auth->name);
        $response['purchase_order'] = $purchase_order;
        $response['service_id'] = $order->id;
            
    }
        
    

    public function CreateGeneralServiceFiles(Request $request, $order, $type)
    {
        $countFiles = OrderFiles::where('request_followup_id', $order)->count();
        if ($countFiles < 10) {
            $file = $request->file('file');
            $originalname = $file->getClientOriginalName();
            $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlFile = Storage::url($pathFile);

            $req_file = OrderFiles::create([
                'request_type_id' => $type,
                'file' => $urlFile,
            ]);

            if ($req_file) {
                $response['success'] = true;
                return $response;
            } else {
                $response['success'] = false;
                return $response;
            }
        } else {
            $response['success'] = false;
            return $response;
        }
    }
    public function GetCotizationFile($id)
    {
        $cotFile = RequestFollowup::where('id', $id)->first('epno_cot_file');
        // dd($cotFile);

        if ($cotFile->epno_cot_file != null) {
            return response()->json($cotFile);
        } else {
            // $response['message'] = "No hay archivo de cotización";
            $response['epno_cot_file'] = false;
            return $response;
        }
    }
    public function GetCotizationFiles($service, $id)
    {
        // dd($service,$id);
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

            $cotFileRequest = OrderFiles::where('request_followup_id', $id)->get('file as cotFile');

            //  $cotFileRequest =DB::table('request_followups')
            //     ->join($service . '_requests', 'request_followups.request_id', $service . '_requests.id')
            //     ->select($service . '_requests.specifications_file as cotFile')
            //     ->where('request_followups.id', $id)
            //     ->get();


            if (!$cotFileRequest->isEmpty()) {

                $POFileClient = RequestFollowup::where('id', $id)->get('client_po_file as cotFile');

                if (!$POFileClient->isEmpty()) {
                    if ($POFileClient[0]->cotFile !== null) {
                        $cotFileAgent = RequestFollowup::where('id', $id)->get('epno_cot_file as cotFile');
                        //    dd();
                        if (!$cotFileAgent->isEmpty()) {
                            if ($cotFileAgent[0]->cotFile !== null) {

                                return response()->json([$POFileClient, $cotFileRequest, $cotFileAgent]);
                            } else {
                                return response()->json([$POFileClient, $cotFileRequest]);
                            }
                        } else {
                            return response()->json([$POFileClient, $cotFileRequest]);
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
        } catch (\Illuminate\Database\QueryException $e) {
            $response['error'] = true;
            return $response;
        }
    }

    public function UpClientMroPo(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $file = $request->file('po');
            $originalname = $file->getClientOriginalName();
            $pathPO = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $urlPO = Storage::url($pathPO);

            $cotizacion = RequestFollowup::where('id', $request->id)->update([
                'client_po_file' => $urlPO
            ]);
            if ($cotizacion) {
                $response['success'] = true;
                return $response;
            } else {
                $response['success'] = false;
                return $response;
            }
        }
    }
    public function AceptDeclineSuppier(Request $request)
    {
        try {
            //code...
       
        $user = Auth::user();
        // return $request;
        if ($user->role_id == 4) {
            if ($request->check == true) {
                $value = 1;
            } else {
                $value = 0;
            }
            // En esta validacion, con el id del supp se puede hacer, solo se opto por validar los 3
            $supplier = SupplierProposal::where('service_id', $request->service_id)
                ->where('id', $request->supplier_id)
                ->where('subservice_id', $request->subservice_id)
                ->update([
                    'check' => $value,
                    'is_winner' => $value,
                ]);

            // return $request->check;

            if ($supplier) {

                if ($request->check == true) {
                    $msg = "La cotización fue agregada a tu lista de aceptadas.";
                } else {
                    $msg = "La cotización fue eliminada de tu lista de aceptadas.";
                }

                $response['success'] = true;
                $response['message'] = $msg;
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "Hubo un problema al aceptar la cotización.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos requeridos para realizar esta acción.";
            return $response;
        }
    } catch (\Throwable $th) {
        $response['success'] = false;
        $response['message'] = $th->getMessage();
        return $response;
    }
    }

    public function FechaEntrega($id)
    {
        // $fechaEntrega = DB::table('request_followups')
        //     ->join('supplier_proposals', 'supplier_proposals.request_followup_id', 'request_followups.id')
        //     ->where('request_followups.id', $id)
        //     ->select('supplier_proposals.updated_at as fecha', 'supplier_proposals.total_days')
        //     ->first();
        // Se modifico de esta manera, ya que la fecha de entrga debe de mostrar la info, cuando ya se haya seleccionado un ganador.
        $fechaEntrega = DB::table('request_followups')
            ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
            ->where('request_followups.id', $id)
            ->select('supplier_proposals.updated_at as fecha', 'supplier_proposals.total_days')
            ->first();

        if ($fechaEntrega) {
            if ($fechaEntrega->fecha !== null && $fechaEntrega->total_days !== null) {
                // $fechaFormat=date("d-m-Y", strtotime($fechaEntrega->fecha));
                $date_future = strtotime('+' . $fechaEntrega->total_days . ' day', strtotime(date("d-m-Y", strtotime($fechaEntrega->fecha))));
                return response()->json(date('d-m-Y', $date_future));
            }
        }
        // $cost = SupplierProposal::where('request_followup_id', $id)->min('cost');
        // if ($cost) {

        //     // $finalCost=SupplierProposal::where('cost','>',$cost)->where('request_followup_id',$id)->min('cost');
        //     $finalCost = DB::table('supplier_proposals')
        //         ->where('cost', '>', $cost)->where('request_followup_id', $id)
        //         ->select('total_days', 'created_at')->get();
        //     // dd($finalCost);
        //     if (!$finalCost->isEmpty()) {
        //         return response()->json($finalCost[0]);
        //     } else {
        //         // $response['message'] = "No hay ordenes con ese id";
        //         $response['success'] = false;
        //         return $response;
        //     }
        // } else {

        //     $response['success'] = false;
        //     return $response;
        // }
    }
    public function AceptSupplierProposalWinnerCost($id, $role)
    {
        $cost = SupplierProposal::where('request_followup_id', $id)->min('cost');

        if ($cost) {

            $dias = SupplierProposal::where('cost', '=', $cost)->where('request_followup_id', $id)->first('total_days');

            if ($role == 4) {
                $finalCost = RequestFollowup::where('id', $id)->first('cot_price as cost');
            } elseif ($role == 1 || $role == 2 || $role || 6 || $role == 5) {
                // $finalCost = DB::table('supplier_proposals')
                //     ->where('cost', '>', $cost)->where('request_followup_id', $id)
                //     ->min('cost');

                // $finalCost = DB::table('supplier_proposals')
                //     ->where('request_followup_id', $id)
                //     ->orderBy('cost', 'asc')
                //     ->skip(1)->first('cost');

                $finalCost = ['cost' => $cost];
            }
            if ($finalCost && $dias) {
                if ($dias->total_days != null) {
                    return response()->json(['finalCost' => $finalCost['cost'], 'dias' => $dias->total_days]);
                } else {

                    return response()->json(['finalCost' => $finalCost['cost'], 'dias' => '']);
                }
            } else if ($dias) {
                if ($dias->total_days != null) {
                    return response()->json(['finalCost' => $cost, 'dias' => $dias->total_days]);
                } else {

                    return response()->json(['finalCost' => $cost, 'dias' => '']);
                }
            }
        } else {

            $response['success'] = false;
            return $response;
        }
    }

    // public function SupplierProposalRechazo($id)
    // {
    //     $user = Auth::user();
    //     if ($user) {
    //         $rechazar = RequestFollowup::where('id', $id)->update(['step_id' => 7]);
    //         if ($rechazar) {
    //             $request_follow_up_logs = new RequestFollowupLogs();
    //             $request_follow_up_logs->request_followup_id = $id;
    //             $request_follow_up_logs->step_id = 7;
    //             $request_follow_up_logs->user_id = $user->id;

    //             $AgentId = User::where('role_id', 1)->get('id');

    //             foreach ($AgentId->id as $id) {
    //                 $notification = new Notification();
    //                 $notification->user_id = $id->id;
    //                 $notification->type_notification_id = 10;
    //                 $notification->table_name = "request_followups";
    //                 $notification->table_name_id = $id;
    //                 if ($notification->save()) {
    //                     DB::select('call limitNotificationCount (?)', array($id->id));
    //                 }
    //             }

    //             if ($request_follow_up_logs->save()) {
    //                 $response['message'] = "Actualizada correctamente";
    //                 $response['success'] = true;
    //                 return $response;
    //             } else {
    //                 $response['message'] = "Error al actualizar";
    //                 $response['success'] = false;
    //                 return $response;
    //             }
    //         } else {
    //             $response['message'] = "Error al actualizar";
    //             $response['success'] = false;
    //             return $response;
    //         }
    //     } else {
    //         $response['message'] = "Usuario no encontrado";
    //         $response['success'] = false;
    //         return $response;
    //     }
    // }

    public function SubirClientPO(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id == 4) {

            $org = Organization::where('id', $user->organization_id)->first('name');

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

            $validator = Validator::make($request->all(), ['po' => 'required|mimes:pdf,doc,docx,png,jpg,jpeg,pptx,pptm,xlsx']);
            if ($validator->fails()) {
                $response['message'] = "el formato no esta permitido";
                $response['success'] = false;
                return $response;
            } else {
                $purchase_order = 'O-' . date_format(new DateTime($request->created_at), 'ymd') . '-' . $request->client_org_id . $request->type_code . '-' . $request->order_id;
                $today = Carbon::now()->format('Y-m-d');

                $file = $request->file('po');
                $originalname = $file->getClientOriginalName();
                $pathPo = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $urlPO = Storage::url($pathPo);

                $addPo = Order::join('services', 'orders.id', 'services.order_id')
                    ->where('orders.id', $request->order_id)
                    ->update([
                        "orders.po_date" => $today,
                        "orders.client_po_file" => $urlPO,
                        "orders.order_num" => $purchase_order,
                        "orders.is_po" => 1,
                        "services.order_num" => $purchase_order,
                    ]);

                if ($addPo) {
                    $notificationUser = new Notification();
                    $notificationUser->user_id = $user->id;
                    $notificationUser->notification_type_id = 6;
                    $notificationUser->table_name = "services";
                    $notificationUser->table_id = $request->order_id;
                    if ($notificationUser->save()) {
                        DB::select('call limitNotificationCount (?)', array($user->id));
                    }

                    // Notify::route('mail', $user->email)
                    //     ->notify(new OrderDetailsNotification(
                    //         6,
                    //         $user->role_id,
                    //         $request->order_id,
                    //         $purchase_order,
                    //         $request->title,
                    //         $user->name,
                    //         $user->phone,
                    //         $user->email,
                    //         $org->name,
                    //     ));

                    //    Notificacion al comprador 
                    foreach ($buyerVsM as $bm) {
                        $notificationAgent = new Notification();
                        $notificationAgent->user_id = $bm->id;
                        $notificationAgent->notification_type_id = 6;
                        $notificationAgent->table_name = "services";
                        $notificationAgent->table_id = $request->order_id;
                        if ($notificationAgent->save()) {
                            DB::select('call limitNotificationCount (?)', array($bm->id));
                        }

                        // Notify::route('mail', $bm->email)
                        //     ->notify(new OrderDetailsNotification(
                        //         6,
                        //         $bm->role_id,
                        //         $request->order_id,
                        //         $purchase_order,
                        //         $request->title,
                        //         $user->name,
                        //         $user->phone,
                        //         $user->email,
                        //         $org->name,
                        //     ));
                    }
                    $changeStep = DB::select('CALL processOrder(?,?,?)', array($request->order_id, $user->id, null));
                    dd($changeStep);
                    $getSuppliers = json_decode($changeStep[0]->response);                 

                    foreach ($getSuppliers->suppliers as $id) {
                        if ($id->is_winner == 1) {
                            $type_nft = 2;

                            $notification = new Notification();
                            $notification->user_id = $id->user_id;
                            $notification->type_notification_id = $type_nft;
                            $notification->table_name = "services";
                            $notification->table_name_id = $request->order_id;
                            if ($notification->save()) {
                                DB::select('call limitNotificationCount (?)', array($id->user_id));
                            }
                        } else {
                            $type_nft = 16;
                        }

                        // Notify::route('mail', $id->user_email)
                        //     ->notify(new OrderDetailsNotification(
                        //         $type_nft,
                        //         6,
                        //         $request->order_id,
                        //         $purchase_order,
                        //         $request->title,
                        //         $user->name,
                        //         $user->phone,
                        //         $user->email,
                        //         $org->name,
                        //     ));
                    }

                    $response['message'] = "archivo subido correctamente";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "no se pudo guardar";
                    $response['success'] = false;
                    return $response;
                }
            }
        } else {
            $response['message'] = "no tienes permiso para realizar esta acción";
            $response['success'] = false;
            return $response;
        }
    }

    public function ordenesPerfilStd()
    {
        $id = Auth::user()->id;

        $ordenService = DB::table('service_requests')
            ->join('users', 'service_requests.user_id', 'users.id')
            ->select('users.id', 'service_requests.final_cost', 'users.name', 'users.role_id')
            ->where([['users.id', $id], ['service_requests.status', 1]]);

        $ordenSerMro = DB::table('mro_requests')
            ->join('users', 'mro_requests.user_id', 'users.id')
            ->select('users.id', 'mro_requests.final_cost', 'users.name', 'users.role_id')
            ->where([['users.id', $id], ['mro_requests.status', 1]])
            ->union($ordenService)
            //->get()

        ;

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
                ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.request_type_id',
                    'request_followups.purchase_order',
                    'request_followups.created_at',
                    $table . '.final_cost',
                    DB::raw(
                        '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)                      
                        ELSE ' . $table . '.final_cost
                        END) AS costo_mercado',
                        // 'max(supplier_proposals.cost) as costo_mercado'
                    ),
                )
                ->selectRaw(
                    '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)  - ' . $table . '.final_cost                      
                        ELSE "0" 
                        END) AS ahorro'
                    // 'max(supplier_proposals.cost)  - ' . $table . '.final_cost as ahorro'
                )
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    [$table . '.user_id', '=', $user->id],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                // ->groupBy('supplier_proposals.request_followup_id')
                ->groupBy('request_followups.request_id')
                ->orderBy('request_followups.created_at', 'asc')
                ->get();

            $orderSum = RequestFollowup::join($table, 'request_followups.request_id', $table . '.id')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                // ->join('supplier_proposals','request_followups.id','supplier_proposals.request_followup_id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    [
                        'request_followups.id',
                        'request_types.name as service',
                        // 'request_followups.purchase_order',
                        // 'request_followups.created_at',
                        // $table . '.final_cost',
                        // DB::raw('DISTINCT(request_followups.request_id)'),
                        DB::raw(
                            'TRUNCATE(sum(' . $table . '.final_cost),2) as pagado, MONTHNAME(request_followups.created_at) month',
                        )
                        // DISTINCT (request_followups.request_id)
                        // DB::raw('DISTINCT (request_followups.request_id)')

                    ]
                )
                // ->selectRaw('max(supplier_proposals.cost)  - '.$table.'.final_cost as ahorro')
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    [$table . '.user_id', '=', $user->id],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                // ->distinct('request_followups.request_id')
                ->groupBy('month', 'service')
                ->get();

            array_push($arrayOrder, $order);
            array_push($arrayOrderSum, $orderSum);
        }
        return response()->json([
            'order' => $arrayOrder,
            'orderSum' => $arrayOrderSum
        ]);
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
                ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.purchase_order',
                    'request_followups.created_at',
                    $table . '.final_cost',
                    DB::raw(
                        '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)                      
                        ELSE ' . $table . '.final_cost
                        END) AS costo_mercado',
                        // 'max(supplier_proposals.cost) as costo_mercado'
                    )
                    // DB::raw('max(supplier_proposals.cost) as costo_mercado'),
                )
                ->selectRaw(
                    '(CASE 
                        WHEN request_followups.request_type_id != "1" THEN max(supplier_proposals.cost)  - ' . $table . '.final_cost                      
                        ELSE "0" 
                        END) AS ahorro'
                    // 'max(supplier_proposals.cost)  - ' . $table . '.final_cost as ahorro'
                )
                // ->selectRaw('max(supplier_proposals.cost)  - ' . $table . '.final_cost as ahorro')
                ->where([
                    ['request_followups.status', '=', '1'],
                    [$table . '.status', '=', '1'],
                    [$table . '.user_id', '=', $user->id],
                    ['request_types.status', '=', '1'],
                    ['steps.status', '=', '1'],
                    ['request_followups.request_type_id', $req->id],
                ])
                ->where('request_followups.step_id', 6)
                ->whereYear('request_followups.created_at', $current_year)
                // ->groupBy('supplier_proposals.request_followup_id')
                ->groupBy('request_followups.request_id')
                ->get();

            // array_push($arrayOrder,$order);

            $collection->push($order);
        }

        $newArr = $collection->collapse();
        $costo = round($newArr->sum('costo_mercado'), 2);
        $pago = round($newArr->sum('final_cost'), 2);
        $ahorro = round($newArr->sum('ahorro'), 2);
        $servicios = $newArr->count('purchase_order');
        $ahorroAvg = round($newArr->avg('ahorro'), 2);
        // return response()->json($newArr);
        return response()->json(['costo' => $costo, 'pago' => $pago, 'ahorro' => $ahorro, 'servicios' => $servicios, 'Avgahorro' => $ahorroAvg]);
    }

    public function gastosPerfilClient()
    {
        $user = Auth::user()->id;
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
                    ['users.id', '=', $user],
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
                'costo' => $group->sum('costo_servicio'),
                'promedio' => $group->avg('costo_servicio'),
                'desviacion' => ($group->sum('costo_servicio') - $group->avg('costo_servicio')),
                'month' => $month,
                'order' => $group,
            ];
        });



        return response()->json($groupwithcount);

        //dd($gastos);
    }

    public function MroPartUpProductQty(Request $request)
    {

        $user = Auth::user()->id;
        $updateQty = MroPart::where('id', $request->id)->where('user_id', $user)
            ->update(['qty' => $request->qty]);

        if ($updateQty) {
            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function GetProductComments($id)
    {
        $user = Auth::user()->id;
        $comments = DB::table('product_comments')
            ->join('users', 'users.id', 'product_comments.user_comment')
            ->join('users as user_sp', 'user_sp.id', 'product_comments.user_answer')
            ->join('organizations', 'users.organization_id', 'organizations.id')
            ->join('organizations as orgs', 'user_sp.organization_id', 'orgs.id')
            ->where('product_comments.epno_part_id', $id)
            ->select(
                'product_comments.*',
                'organizations.logo',
                'orgs.logo as sp_logo',
                'users.name',
                'user_sp.name as sp_name',
                DB::raw('(CASE WHEN product_comments.user_comment=' . $user . ' THEN "true" ELSE "false" END) as my_comment')
            )
            ->orderBy('product_comments.created_at', 'desc')
            ->get();
        return response()->json($comments);
    }

    public function SendProductComment(Request $request)
    {
        $user = Auth::user()->id;
        $newComment = ProductComment::create([
            'epno_part_id' => $request->epno_part_id,
            'user_comment' => $user,
            'comment' => $request->comment
        ]);

        if ($newComment) {

            $supplier_user = Partno::select('user_id', 'name', 'supplier_partno', 'part_category_id')
                ->where('epno_part_id', $request->epno_part_id)
                ->first();
            $supplier_mail = User::where('id', $supplier_user->user_id)->first('email');

            // Notify::route('mail', $supplier_mail->email)
            //     ->notify(new ProductNotification(
            //         1,
            //         $supplier_user->name,
            //         $supplier_user->supplier_partno,
            //         $request->epno_part_id,
            //         $supplier_user->part_category_id,
            //     ));

            $response['success'] = true;
            return $response;
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function OrdenListaShowSupp($service)
    {
        $user = Auth::user();

        if ($user->role_id == 4) {
            $callback = function ($query) {
                $query->where('check', 1);
            };
           
            $suppliers = Subservice::whereHas('supplierProposal',$callback)->with(['supplierProposal' => $callback])->where('service_id', $service)->get();


            return response()->json($suppliers);
        }
    }

    public function OrderCancelRequest(Request $request)
    {
        // return $request;
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');

        if ($user->role_id == 4 || $user->role_id == 3 || $user->role_id == 5) {

            if ($request->option == 1) {
                $step = 11;
                $ntf_type = 23;
            } else if ($request->option == 2) {
                $step = 9;
                $ntf_type = 11;
            } else {
                $step = 8;
                $ntf_type = 10;
            }


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
                ->where('vs_users.vs_id', $request->client_info['vs'])
                ->get();

            $upService = Service::leftJoin('subservices', 'subservices.service_id', 'services.id')
                ->where('services.id', $request->service_info['id'])
                ->update([
                    "services.step_id" => $step,
                    "subservices.step_id" => $step,
                ]);

            if ($upService) {

                $serviceLog = ServiceLog::create([
                    'service_id' => $request->service_info['id'],
                    'step_id' => $step,
                    'user_id' => $user->id,
                ]);

                if ($serviceLog) {

                    $comment = ServiceComment::create([
                        'service_id' => $request->service_info['id'],
                        'comment' => $request->comentario,
                        'user_id' => $user->id,
                        'step_id' => $step,
                        'file' => "",
                        'file_name' => "",
                    ]);

                    if ($comment) {

                        foreach ($request->subservices as $sp) {
                            SubserviceLog::create([
                                'subservice_id' => $sp['id'],
                                'step_id' => $step,
                                'user_id' => $user->id,
                            ]);
                        }

                        //notificacion al usuario
                        $notificationUser = new Notification();
                        $notificationUser->user_id = $request->client_info['user_id'];
                        $notificationUser->notification_type_id = $ntf_type;
                        $notificationUser->table_name = "services";
                        $notificationUser->table_id = $request->service_info['order_id'];
                        if ($notificationUser->save()) {
                            DB::select('call limitNotificationCount (?)', array($request->client_info['user_id']));
                        }

                        Notify::route('mail', $request->client_info['contact_email'])
                            ->notify(new OrderDetailsNotification(
                                $ntf_type,
                                4,
                                $request->service_info['order_id'],
                                $request->service_info['order_num'],
                                $request->service_info['title'],
                                $request->comentario,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));

                        //    Notificacion al comprador y vs manager
                        foreach ($buyerVsM as $bm) {
                            $notificationAgent = new Notification();
                            $notificationAgent->user_id = $bm->id;
                            $notificationAgent->notification_type_id = $ntf_type;
                            $notificationAgent->table_name = "services";
                            $notificationAgent->table_id = $request->service_info['order_id'];
                            if ($notificationAgent->save()) {
                                DB::select('call limitNotificationCount (?)', array($bm->id));
                            }

                            Notify::route('mail', $bm->email)
                                ->notify(new OrderDetailsNotification(
                                    $ntf_type,
                                    $bm->role_id,
                                    $request->service_info['order_id'],
                                    $request->service_info['order_num'],
                                    $request->service_info['title'],
                                    $request->comentario,
                                    $user->phone,
                                    $user->email,
                                    $org->name,
                                ));
                        }

                        if ($request->option == 2) {

                            foreach ($request->subservices as $sub) {
                                foreach ($sub['proposals'] as $sp) {
                                    if (($sp['is_winner'] == 1 || $sp['is_winner'] == 2) && $sp['status'] == 1) {
                                        $notificationSupplier = new Notification();
                                        $notificationSupplier->user_id = $sp['user_id'];
                                        $notificationSupplier->notification_type_id = $ntf_type;
                                        $notificationSupplier->table_name = "services";
                                        $notificationSupplier->table_id = $request->service_info['order_id'];
                                        if ($notificationSupplier->save()) {
                                            DB::select('call limitNotificationCount (?)', array($sp['user_id']));
                                        }

                                        Notify::route('mail', $sp['user_email'])
                                            ->notify(new OrderDetailsNotification(
                                                $ntf_type,
                                                6,
                                                $request->service_info['order_id'],
                                                $request->service_info['order_num'],
                                                $request->service_info['title'],
                                                $request->comentario,
                                                $user->phone,
                                                $user->email,
                                                $org->name,
                                            ));
                                    }
                                }
                            }
                        }

                        $response['success'] = true;
                        $response['message'] = "Informacion enviada correctamente.";
                        return $response;
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Hubo un error al agregar el comentario.";
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "Hubo un error al registrar el log.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Hubo un error al actualizar los datos del servicio.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No cuentas con los permisos necesarios para realizar esta acción.";
            return $response;
        }
    }

    public function ComplaintRequest(Request $request)
    {
        // return $request;
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');
        $complaint_num = 'Q-' . date_format(Carbon::now(), 'ymd') . '-' . $request->client_org . '00' . '-' . $request->order_id;
        $queja = Complaint::create([
            'order_id' => $request->order_id,
            'service_id' => $request->service_id,
            'title' =>  $request->service_title,
            'user_id' => $request->client_id,
            'organization_id' => $request->client_org,
            'complaint_num' => $complaint_num,
            'responsible_user' => "Veronica Mata",
            'order_num' => $request->order_num,
            'supplier_cost' => $request->supplier_cost,
            'client_cost' => $request->client_cost,
            'return_amount' => $request->return_amount,
            'step_id' => 1,
        ]);

        if ($queja) {
            $ComplaintLog = ComplaintLog::create([
                'complaint_id' => $queja->id,
                'user_id' => $request->client_id,
                'step_id' => 1,
                'description' => "En revisión"
            ]);
            if ($ComplaintLog) {

                foreach ($request->subservices as $sub) {
                    SubserviceComplaint::create([
                        'complaint_id' => $queja->id,
                        'subservice_id' => $sub,
                        'step_id' => 1
                    ]);

                    Subservice::where('id', $sub)->update(['step_id' => 12]);

                    SubserviceLog::create([
                        'subservice_id' => $sub,
                        'step_id' => 12,
                        'user_id' => $request->client_id,
                    ]);
                }

                $file = $request->evidencia;
                $originalname = $file->getClientOriginalName();
                $path = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $url = Storage::url($path);

                $evidencia = ComplaintClientToEpnoEvidence::create([
                    'complaint_id' => $queja->id,
                    'user_id' => $request->client_id,
                    'client_description' => $request->desc_evidencia,
                    'client_file' => $url,
                    'client_file_name' => $originalname,
                ]);

                if ($evidencia) {
                    if (isset($request->evidencias) && isset($request->descs)) {

                        $descs = collect($request->descs);
                        $evidencias = collect($request->evidencias);
                        $more_quejas = $descs->zip($evidencias);

                        foreach ($more_quejas as $q) {
                            $fileE = $q[1];
                            $originalnameE = $fileE->getClientOriginalName();
                            $pathE = Storage::putFileAs('/public/uploads/', $fileE,  $originalnameE);
                            $urlE = Storage::url($pathE);

                            ComplaintClientToEpnoEvidence::create([
                                'complaint_id' => $queja->id,
                                'client_id' => $request->client_id,
                                'client_description' => $q[0],
                                'client_file' => $urlE,
                                'client_file_name' => $originalnameE,
                            ]);
                        }
                    }

                    $upService = Service::where('id', $request->service_id)->update(['step_id' => 12]);

                    if ($upService) {
                        $serviceLog = ServiceLog::create([
                            'step_id' => 12,
                            'user_id' => $request->client_id,
                            'service_id' => $request->service_id,
                        ]);

                        if ($serviceLog) {

                            $notificationClient = new Notification();
                            $notificationClient->user_id = $request->client_id;
                            $notificationClient->notification_type_id = 24;
                            $notificationClient->table_name = "complaints";
                            $notificationClient->table_id = $queja->id;
                            if ($notificationClient->save()) {
                                DB::select('call limitNotificationCount (?)', array($request->client_id));
                            }

                            Notify::route('mail', $request->client_mail)
                                ->notify(new ComplaintDetailsNotification(
                                    24,
                                    4,
                                    $queja->id,
                                    $complaint_num,
                                    $request->service_title,
                                    $user->name,
                                    $user->phone,
                                    $user->email,
                                    $org->name,
                                ));

                            $notificationEpno = new Notification();
                            $notificationEpno->user_id = 4;
                            $notificationEpno->notification_type_id = 24;
                            $notificationEpno->table_name = "complaints";
                            $notificationEpno->table_id = $queja->id;
                            if ($notificationEpno->save()) {
                                DB::select('call limitNotificationCount (?)', array(4));
                            }

                            Notify::route('mail', 'larissa.jasso@epno.com.mx')
                                ->notify(new ComplaintDetailsNotification(
                                    24,
                                    5,
                                    $queja->id,
                                    $complaint_num,
                                    $request->service_title,
                                    $user->name,
                                    $user->phone,
                                    $user->email,
                                    $org->name,
                                ));

                            $response['success'] = true;
                            $response['message'] = "Queja creada correctamente.";
                            return $response;
                        } else {
                            $response['success'] = false;
                            $response['message'] = "No se pudo crear el log del servicio.";
                            return $response;
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = "No se pudo actualizar el servicio.";
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "No se pudo crear la evidencia.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "No se pudo crear el log de la queja.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No se pudo crear la queja.";
            return $response;
        }
    }

    public function ClientNewPO(Request $request)
    {
        // return $request;
        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');
        $user_epno = User::where('role_id', 10)->first();
        $subservices = json_decode($request->subservices);
        if ($user->role_id == 4) {


            $file = $request->po;
            $originalname = $file->getClientOriginalName();
            $path = Storage::putFileAs('/public/uploads/', $file,  $originalname);
            $url = Storage::url($path);
            // return $request;

            $changeStep = Complaint::where('id', $request->complaint_id)
                ->update([
                    'step_id' => 4,
                    'client_po_file' => $url,
                ]);

            if ($changeStep) {

                $log = ComplaintLog::create([
                    'complaint_id' => $request->complaint_id,
                    'user_id' => $user->id,
                    'step_id' => 4,
                    'cost' => 0.00,
                    'description' => "Orden en construcción",
                ]);

                if ($log) {

                    $notificationClient = new Notification();
                    $notificationClient->user_id = $request->user_id;
                    $notificationClient->notification_type_id = 6;
                    $notificationClient->table_name = "complaints";
                    $notificationClient->table_id = $request->complaint_id;
                    if ($notificationClient->save()) {
                        DB::select('call limitNotificationCount (?)', array($request->user_id));
                    }

                    Notify::route('mail', $request->user_email)
                        ->notify(new ComplaintDetailsNotification(
                            6,
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
                    $notificationEpno->notification_type_id = 6;
                    $notificationEpno->table_name = "complaints";
                    $notificationEpno->table_id = $request->complaint_id;
                    if ($notificationEpno->save()) {
                        DB::select('call limitNotificationCount (?)', array($user_epno->id));
                    }

                    Notify::route('mail', $user_epno->email)
                        ->notify(new ComplaintDetailsNotification(
                            6,
                            10,
                            $request->complaint_id,
                            $request->complaint_num,
                            $request->service_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));


                    foreach ($subservices as $sub) {
                        foreach ($sub->suppliers as $supplier) {

                            $notificationSupplier = new Notification();
                            $notificationSupplier->user_id = $supplier->user_id;
                            $notificationSupplier->notification_type_id = 6;
                            $notificationSupplier->table_name = "complaints";
                            $notificationSupplier->table_id = $request->complaint_id;
                            if ($notificationSupplier->save()) {
                                DB::select('call limitNotificationCount (?)', array($supplier->user_id));
                            }

                            Notify::route('mail', $supplier->user->email)
                                ->notify(new ComplaintDetailsNotification(
                                    6,
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
                    }


                    $response['success'] = true;
                    $response['message'] = "archivo guardado correctamente.";
                    return $response;
                } else {
                    $response['success'] = false;
                    $response['message'] = "hubo un error al guardar el log del movimiento.";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "hubo un error al actualizar el step de la queja.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "no cuentas con los permisos para realizar esta acción.";
            return $response;
        }
    }
}
