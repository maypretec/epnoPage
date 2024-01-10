<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CommentsFiles;
use App\Models\AgentRating;
use App\Models\Category;
use App\Models\City;
use App\Models\Colony;
use App\Models\Complaint;
use App\Models\ComplaintClientToEpnoEvidence;
use App\Models\ComplaintEpnoToSupplierEvidence;
use App\Models\ComplaintLog;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\Message;
use App\Models\MessageFile;
use App\Models\PostalCode;
use App\Models\State;
use App\Models\SupplierRating;
use App\Models\VsUser;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Organization;
use App\Models\ServiceComment;
use App\Models\SubserviceComplaint;
use App\Models\SupplierProposalComplaint;
use App\Models\SupplierProposalComplaintLog;
use App\Notifications\OrderDetailsNotification;
use App\PartNo;
use App\RequestFollowup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\RequestFollowupLogs;
use App\ServiceCategory;
use App\RequestFollowupComment;
// use App\Ratings;
use App\RequestType;
use App\ServiceRequest;
use App\Models\User;
use App\Notifications\ComplaintDetailsNotification;
use App\Notifications\OrderComment;
use App\Notifications\RatingNotification;
use Illuminate\Support\Facades\Notification as Notify;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PharIo\Manifest\Author;
use phpseclib3\System\SSH\Agent;
use App\Http\Traits\ComplaintNotificationsTrait;

class GeneralController extends Controller
{
    use ComplaintNotificationsTrait;

    function ping() {
        return 'pong';
    }

    public function showCountry()
    {
        $countries = Country::orderBy('id', 'asc')->get();
        return response()->json($countries);
        // dd($countries);
    }
    public function showState($id)
    {
        $states = State::orderBy('id', 'asc')->where('region_id', $id)->get();
        return response()->json($states);
    }

    public function showCity($id)
    {
        $cities = City::orderBy('id', 'asc')->where('state_id', $id)->get();
        return response()->json($cities);
    }
    public function showPC($id)
    {
        $pc = PostalCode::orderBy('id', 'asc')->where('city_id', $id)->get();
        return response()->json($pc);
    }
    public function showColony($id)
    {
        $pc = Colony::orderBy('id', 'asc')->where('postal_code_id', $id)->get();
        return response()->json($pc);
    }

    // -------------------------------------------------------AREA DE NOTIFICACIONES----------------------------------------------------
    public function GetNotifications($flag)
    {
        $user_loggin = Auth::user();
        if ($user_loggin) {
            $notif = Notification::where('status', 1)->where('user_id', $user_loggin->id)->orderBy('created_at', 'desc')->get();
            $notificationNumber = Notification::where([
                ['notifications.seen', 0],
                ['notifications.status', 1],
                ['notifications.user_id', $user_loggin->id]
            ])->count();

            $notifications = collect();
            foreach ($notif as $ntf) {
                $n = DB::table('notifications')
                    ->join($ntf->table_name, 'notifications.table_id', $ntf->table_name . '.id')
                    ->join('notification_types', 'notifications.notification_type_id', 'notification_types.id')
                    ->select(
                        'notifications.*',
                        'notification_types.description as description',
                        $ntf->table_name . '.title',
                    )
                    ->where([
                        ['notifications.user_id', $user_loggin->id],
                        ['notifications.status', 1],
                        ['notifications.id', $ntf->id]
                    ])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $notifications->push($n);
            }
            if ($flag == 1) {
                return response()->json([
                    'notificaciones' => $notifications->collapse()->take(5),
                    'total' => $notificationNumber,
                    'user' => $user_loggin->id
                ]);
            }
            if ($flag == 2) {
                return response()->json([
                    'notificaciones' => $notifications->collapse(),
                    'total' => $notificationNumber,
                    'user' => $user_loggin->id
                ]);
            }
        } else {
            $response['message'] = "Usuario no encontrado";
            $response['success'] = false;
            return $response;
        }
    }

    // public function GetNotificationsTotal()
    // {
    //     $user_loggin = Auth::user();
    //     if ($user_loggin) {

    //         $notificationNumber = Notification::where([['notifications.seen', 0], ['notifications.status', 1], ['notifications.user_id', $user_loggin->id]])->count();

    //         return response()->json($notificationNumber);
    //     } else {
    //         $response['message'] = "Usuario no encontrado";
    //         $response['success'] = false;
    //         return $response;
    //     }
    // }

    public function ChangeNotificationStatus($id)
    {
        $changeStatus = Notification::find($id)->update(['seen' => 1]);

        if ($changeStatus) {
            $response['message'] = "Actualizado correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "Error al actualizar";
            $response['success'] = false;
            return $response;
        }
    }

    public function ServiceChangeStep(Request $request)
    {
        try {
            // return $request;
        $user = Auth::user();

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
            ->where('vs_users.vs_id', $request->client_info['vs'])
            ->get();


        if ($user->role_id == 5 || $user->role_id == 3) {
            // Se modifico a sp pero falta validar como retorna la info para saber si requiere un decode
            $changeStep = DB::select('CALL processOrder(?,?,?)', array($request->service_info['order_id'],$user->id,$request->subservice_id));
            $nextStep = json_decode($changeStep[0]->response);
            if ($nextStep->step == 2) {
                $ntf_user = 3;
                $ntf_agent = 3;
                $ntf_supp = 20;
            } else if ($nextStep->step == 3) {
                $ntf_user = 5;
                $ntf_agent = 5;
                $ntf_supp = 5;
            } else if ($nextStep->step == 4) {
                $ntf_user = 6;
                $ntf_agent = 6;
                $ntf_supp = 6;
            } else if ($nextStep->step == 5) {
                $ntf_user = 8;
                $ntf_agent = 12;
                $ntf_supp = 8;
            } else if ($nextStep->step == 6) {
                $ntf_user = 7;
                $ntf_agent = 7;
                $ntf_supp = 7;
            } else if ($nextStep->step == 7) {
                $ntf_user = 9;
                $ntf_agent = 9;
                $ntf_supp = 9;
            }

            //notificacion al usuario
            $notificationUser = new Notification();
            $notificationUser->user_id = $request->client_info['user_id'];
            $notificationUser->notification_type_id = $ntf_user;
            $notificationUser->table_name = "services";
            $notificationUser->table_id = $request->service_info['order_id'];
            if ($notificationUser->save()) {
                DB::select('call limitNotificationCount (?)', array($request->client_info['user_id']));
            }

            Notify::route('mail', $request->client_info['contact_email'])
                ->notify(new OrderDetailsNotification(
                    $ntf_user,
                    4,
                    $request->service_info['order_id'],
                    $request->service_info['order_num'],
                    $request->service_info['title'],
                    $user->name,
                    $user->phone,
                    $user->email,
                    $org->name,
                ));

            //    Notificacion al comprador y vs manager
            foreach ($buyerVsM as $bm) {
                $notificationAgent = new Notification();
                $notificationAgent->user_id = $bm->id;
                $notificationAgent->notification_type_id = $ntf_agent;
                $notificationAgent->table_name = "services";
                $notificationAgent->table_id = $request->service_info['order_id'];
                if ($notificationAgent->save()) {
                    DB::select('call limitNotificationCount (?)', array($bm->id));
                }

                Notify::route('mail', $bm->email)
                    ->notify(new OrderDetailsNotification(
                        $ntf_agent,
                        $bm->role_id,
                        $request->service_info['order_id'],
                        $request->service_info['order_num'],
                        $request->service_info['title'],
                        $user->name,
                        $user->phone,
                        $user->email,
                        $org->name,
                    ));
            }

            // NOTIFICACION A SUPPLIERS ASIGNADOS 
            foreach ($nextStep->suppliers as $sp) {
                if (($sp->is_winner == 1 || $sp->is_winner == 2) && $sp->status == 1) {
                    $notificationSupplier = new Notification();
                    $notificationSupplier->user_id = $sp->user_id;
                    $notificationSupplier->notification_type_id = $ntf_supp;
                    $notificationSupplier->table_name = "services";
                    $notificationSupplier->table_id = $request->service_info['order_id'];
                    if ($notificationSupplier->save()) {
                        DB::select('call limitNotificationCount (?)', array($sp->user_id));
                    }

                    Notify::route('mail', $sp->user_email)
                        ->notify(new OrderDetailsNotification(
                            $ntf_supp,
                            6,
                            $request->service_info['order_id'],
                            $request->service_info['order_num'],
                            $request->service_info['title'],
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name,
                        ));
                }
            }
            $response['step_id'] = $nextStep->step;
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "No cuentas con los permisos para realizar esta acción.";
            $response['success'] = false;
            return $response;
        }
    } catch (\Throwable $th) {
        $response['message'] = $th->getMessage();
        $response['success'] = false;
        return $response;
   }
    }

    public function GetCategories()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function SendRate(Request $request)
    {
        $user = Auth::user();
        if ($user->role_id == 4) {
            $vs = VsUser::where('user_id', $user->id)->first('vs_id');

            $buyer = VsUser::join('users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id',
                    'users.email',
                )
                ->where('users.role_id', 5)
                ->where('vs_users.vs_id', $vs->vs_id)
                ->first();
            // return $request;

            $rate = AgentRating::Create(
                [
                    'user_id' => $user->id,
                    'service_id' => $request->order['id'],
                    'table_name' => "services",
                    'rating' => $request->valor['value'],
                    'comment' => $request->valor['comentario'],
                ]
            );

            if ($rate) {
                Notify::route('mail', $buyer->email)
                    ->notify(new RatingNotification(
                        $request->valor['comentario'],
                        $request->valor['value'],
                        $request->order['order_num'],
                        $request->order['order_id']
                    ));


                $response['message'] = "Rate Guardado correctamente";
                $response['success'] = true;
                return $response;
            } else {
                $response['message'] = "Error al guardar rate";
                $response['success'] = false;
                return $response;
            }
        } else if ($user->role_id == 5 || $user->role_id == 1 || $user->role_id == 3) {
            $rate = SupplierRating::Create(
                [
                    'user_id' => $user->id,
                    'service_id' => $request->order['service_id'],
                    'supplier_proposal_id' => $request->order['supplier_id'],
                    'subservice_id' => $request->order['subservice_id'],
                    'table_name' => "services",
                    'rating' => $request->valor['value'],
                    'comment' => $request->valor['comentario'],
                ]
            );

            if ($rate) {

                Notify::route('mail', $request->order['email'])
                    ->notify(new RatingNotification(
                        $request->valor['comentario'],
                        $request->valor['value'],
                        $request->order['order_num'],
                        $request->order['order_id']
                    ));


                $response['message'] = "Rate Guardado correctamente";
                $response['success'] = true;
                return $response;
            } else {
                $response['message'] = "Error al guardar rate";
                $response['success'] = false;
                return $response;
            }
        }
    }

    public function GetRateById($id, $type)
    {
        if ($type == "4") {

            $rate = Ratings::where('request_followup_id', $id)->first(['client_to_agent_rating as rating', 'client_to_agent_comment as comment']);
            return response()->json($rate);
        } else if ($type == "6" || $type == "5") {
            $rate = Ratings::where('request_followup_id', $id)->first(['supplier_to_agent_rating as rating', 'supplier_to_agent_comment as comment']);
            // dd($rate);
            if ($rate !== null) {
                return response()->json($rate);
            } else {
                $response['rating'] = 0;
                $response['comment'] = '';
                return [$response];
            }
        } else if ($type == "1") {
            $rate = Ratings::where('request_followup_id', $id)->first(['agent_to_supplier_rating as rating', 'agent_to_supplier_comment as comment']);
            return response()->json($rate);
        }
    }

    public function SendOrderCommentById(Request $request)
    {
        try {

            $maxPeso = false;

            $validator = Validator::make($request->all(), [
                'files' => 'size:max:10240',
            ]);

            if ($validator->fails()) {
                $maxPeso = true;
            }


            $user = Auth::user();

            if ($user) {

                if ($request->comment !== null) {
                    $comentario = $request->comment;
                } else {
                    $comentario = 'Archivo adjunto';
                }

                if ($request->conversacion == "false") {
                    $cvn = Conversation::create([
                        'service_id' => $request->service_id,
                        'first_participant' => $user->id,
                        'second_participant' => $request->receptor,
                    ]);
                    $conversation = $cvn->id;
                } else {

                    $conversation = $request->conversacion;
                }

                $comment = Message::create([
                    'conversation_id' => $conversation,
                    'comment' => $comentario,
                    'user_id' => $user->id,
                    'step_id' => $request->step_id,
                ]);


                if ($comment) {
                    if (isset($request['files']) && $request['files'] !== []) {
                        $files = array();

                        foreach ($request['files'] as $file) {
                            $file = $file;
                            $originalname = $file->getClientOriginalName();
                            $pathFile = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                            $urlFile = Storage::url($pathFile);

                            MessageFile::create([
                                'message_id' => $comment->id,
                                'file' => $urlFile,
                                'file_name' => $originalname,
                            ]);
                            array_push($files, $urlFile);
                        }
                    } else {
                        $files = [];
                    }

                    $notificationReceptor = new Notification();
                    $notificationReceptor->user_id = $request->receptor;
                    $notificationReceptor->notification_type_id = 19;
                    $notificationReceptor->table_name = "services";
                    $notificationReceptor->table_id = $request->order_id;
                    if ($notificationReceptor->save()) {
                        DB::select('call limitNotificationCount (?)', array($request->receptor));
                    }

                    if ($maxPeso == false) {
                        Notify::route('mail', $request->receptor_mail)
                            ->notify(new OrderComment($comentario, $files, $request->order_num, $user->name, $request->order_id));
                    }

                    $response['message'] = "Guardado correctamente";
                    $response['success'] = true;
                    return $response;
                } else {
                    $response['message'] = "Error al guardar comentario";
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['message'] = "Usuario no encontrado";
                $response['success'] = false;
                return $response;
            }
        } catch (\Throwable $th) {

            $response['message'] = $th->getMessage();
            $response['success'] = false;
            return $response;
        }
    }

    public function GetConversationMessages(Request $request)
    {
        $user = Auth::user();
        // return $user->id;
        $messages = Conversation::with(['Messages' => function ($query) use ($user) {
            $query->select('*', DB::raw('(CASE WHEN user_id=' . $user->id . ' THEN "true" ELSE "false" END) as my_comment'));
        }, 'Messages.User.Organization', 'Messages.Files'])
            ->where('service_id', $request->order)
            ->whereIn('first_participant', [$user->id, $request->user])
            ->whereIn('second_participant', [$user->id, $request->user])
            ->where('status', 1)
            ->first();

        return response()->json($messages);
    }

    public function GetReviews()
    {
        $user = Auth::user();
        $role = Auth::user()->role_id;
        if ($role == 1) {

            $rating = AgentRating::join('services', 'agent_ratings.service_id', 'services.id')
                ->join('users', 'agent_ratings.user_id', 'users.id')
                ->join('vs_users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'services.title',
                    'services.description',
                    'services.order_num',
                    'services.created_at',
                    'vs_users.vs_id',
                    'agent_ratings.rating',
                    'agent_ratings.id',
                    'agent_ratings.comment',
                    DB::raw('(CASE WHEN agent_ratings.table_name="services" THEN "Servicio"
                    WHEN agent_ratings.table_name="mro_requests" THEN "MRO" WHEN agent_ratings.table_name="software_requests"               
                    THEN "Software" END) as tipo')
                )
                ->where('services.step_id', '=', 7)
                ->get();
        } else if ($role == 3) {
            $vs = VsUser::where('user_id', $user->id)->first('vs_id');

            $rating = AgentRating::join('services', 'agent_ratings.service_id', 'services.id')
                ->join('users', 'agent_ratings.user_id', 'users.id')
                ->join('vs_users', 'vs_users.user_id', 'users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'services.title',
                    'services.description',
                    'services.order_num',
                    'services.created_at',
                    'vs_users.vs_id',
                    'agent_ratings.rating',
                    'agent_ratings.id',
                    'agent_ratings.comment',
                    DB::raw('(CASE WHEN agent_ratings.table_name="services" THEN "Servicio"
                    WHEN agent_ratings.table_name="mro_requests" THEN "MRO" WHEN agent_ratings.table_name="software_requests"               
                    THEN "Software" END) as tipo')
                )
                ->where('vs_users.vs_id', '=', $vs->vs_id)
                ->where('services.step_id', '=', 7)
                ->get();
        } else if ($role == 6) {

            $rating = SupplierRating::join('services', 'supplier_ratings.service_id', 'services.id')
                ->join('subservices', 'supplier_ratings.subservice_id', 'subservices.id')
                ->join('supplier_proposals', 'supplier_ratings.supplier_proposal_id', 'supplier_proposals.id')
                ->select(
                    'services.title as ser_title',
                    'services.description as desc_service',
                    'services.order_num',
                    'services.created_at',
                    'subservices.name as title',
                    'subservices.qty',
                    'supplier_proposals.description',
                    'supplier_ratings.rating',
                    'supplier_ratings.id',
                    'supplier_ratings.comment',
                    DB::raw('(CASE WHEN supplier_ratings.table_name="services" THEN "Servicio"
                WHEN supplier_ratings.table_name="mro_requests" THEN "MRO" WHEN supplier_ratings.table_name="software_requests"               
                THEN "Software" END) as tipo')
                )
                ->where('subservices.step_id', '=', 7)
                ->where('supplier_proposals.user_id', '=', $user->id)
                ->get();
        }


        return response()->json($rating);
    }

    public function GetAllOrders($type)
    {
        $user = Auth::user();
        if ($type == 0) {
            if ($user->role_id == 4) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($user) {
                        $query->whereBetween('step_id', [7, 9]);
                        $query->where('user_id', $user->id);
                    })
                    ->get();
            } else if ($user->role_id == 6) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($user) {
                        $query->join('supplier_proposals', 'services.id', 'supplier_proposals.service_id')->where(
                            'supplier_proposals.user_id',
                            $user->id
                        )->whereBetween('services.step_id', [7, 9])->where('supplier_proposals.status', 1);
                    })
                    ->get();
            } else if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 10) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) {
                        $query->whereBetween('step_id', [7, 9]);
                    })
                    ->get();
            } else if ($user->role_id == 3 || $user->role_id == 5) {
                $vs = VsUser::where('user_id', $user->id)->first('vs_id');

                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($vs) {
                        $query->join('users', 'users.id', 'services.user_id')
                            ->join('vs_users', 'vs_users.user_id', 'users.id')
                            ->whereBetween('services.step_id', [7, 9])
                            ->where('vs_users.vs_id', '=', $vs->vs_id);
                    })
                    ->get();
            }
        }
        if ($type == 1) {
            if ($user->role_id == 4) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($user) {
                        $query->whereIn('step_id', [1, 2, 3, 4, 5, 6, 11, 12]);
                        $query->where('user_id', $user->id);
                    })
                    ->get();
            } else if ($user->role_id == 6) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($user) {
                        $query->join('supplier_proposals', 'services.id', 'supplier_proposals.service_id')->where(
                            'supplier_proposals.user_id',
                            $user->id
                        // )->whereIn('services.step_id', [1, 2, 3, 4, 5, 6, 11, 12])->where('supplier_proposals.status', 1);
                        )->whereIn('services.step_id', [2, 3, 4, 5, 6, 11, 12])->where('supplier_proposals.status', 1);
                    })
                    ->get();
            } else if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 10) {
                $orders = Order::with('service')
                    ->whereHas('service', function ($query) {
                        $query->whereIn('step_id', [1, 2, 3, 4, 5, 6, 11, 12])
                            ->orderBy('prioridad', 'ASC');
                    })
                    ->get();
            } else if ($user->role_id == 3 || $user->role_id == 5) {
                $vs = VsUser::where('user_id', $user->id)->first('vs_id');

                $orders = Order::with('service')
                    ->whereHas('service', function ($query) use ($vs) {
                        $query->join('users', 'users.id', 'services.user_id')
                            ->join('vs_users', 'vs_users.user_id', 'users.id')
                            ->whereIn('services.step_id', [1, 2, 3, 4, 5, 6, 11, 12])
                            ->where('vs_users.vs_id', '=', $vs->vs_id)
                            ->orderBy('prioridad', 'ASC');
                    })
                    ->get();
            }
        }
        return response()->json($orders);
    }

    //Borrar funcion
    // public function GetCloseOrdersReviewsSTD(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($user->role_id == 4) {
    //         $rate = 'ratings.client_to_supplier_rating as rate';
    //         $comment = 'ratings.client_to_supplier_comment as comment';
    //         $rating = 'ratings.client_to_supplier_rating';
    //         $comments = 'ratings.client_to_supplier_comment';
    //     } else if ($user->role_id == 6) {
    //         $rate = 'ratings.supplier_to_client_rating as rate';
    //         $comment = 'ratings.supplier_to_client_comment as comment';
    //         $rating = 'ratings.supplier_to_client_rating';
    //         $comments = 'ratings.supplier_to_client_comment';
    //     }
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
    //             ->join('users', $table . '.user_id', 'users.id')
    //             ->join('ratings', 'ratings.request_followup_id', 'request_followups.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //             ->join('steps', 'request_followups.step_id', 'steps.id')
    //             ->select(
    //                 'request_followups.*',
    //                 $table . '.user_id as user',
    //                 $title,
    //                 'request_types.name as tipo',
    //                 $rate,
    //                 $comment
    //             )
    //             ->where([
    //                 ['request_followups.status', '=', '1'],
    //                 [$rating, '!=', null],
    //                 [$comments, '!=', null],
    //                 [$table . '.status', '=', '1'],
    //                 [$table . '.id', '=', $req_id],
    //                 ['request_followups.request_type_id', $req->id],
    //                 ['request_types.status', '=', '1'],
    //                 ['supplier_proposals.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //                 ['users.id', '=', $user->id],
    //             ])
    //             ->whereBetween('request_followups.step_id', [6, 8])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
    //         if (!$order->isEmpty()) {
    //             array_push($arrayOrder, $order[0]);
    //         }
    //     }

    //     return response()->json($arrayOrder);
    // }

    public function GetOrderById($id)
    {
        $user = Auth::user();

        if ($user->role_id == 3 || $user->role_id == 1 || $user->role_id == 5 || $user->role_id == 2) {
            $orderId = DB::select('CALL orderDetails(?)', array($id));
        } else if ($user->role_id == 4) {
            $orderId = DB::select('CALL orderDetailsClient(?,?)', array($id,$user->id));
        } else if ($user->role_id == 6) {
            $orderId = DB::select('CALL orderDetailsSupplier(?,?)', array($id,$user->id));
        }

        $order = json_decode($orderId[0]->response);

        return $order;
    }

    public function GetPartNumbersOrder($role, $id, $tipo)
    {
        $user = Auth::user();
        if ($role == 1 || $role == 3 || $role == 4) {
            $partnos = DB::table('request_followups')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.request_id',
                    'mro_parts.epno_part_id',
                    'epno_parts.name',
                    'epno_parts.part_no as partno',
                    'epno_parts.image',
                    'mro_parts.part_cost',
                    'mro_parts.qty',
                    'steps.name as step',
                    'steps.id as step_id'
                )
                ->where('request_followups.request_id', $id)
                ->where('request_followups.request_type_id', $tipo)
                ->whereColumn('supplier_proposals.user_id', '=', 'part_nos.user_id')
                ->get();
        } else if ($role == 5) {
            $partnos = DB::table('request_followups')
                ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->join('users', 'users.id', 'supplier_proposals.user_id')
                ->select(
                    'request_followups.id',
                    'request_followups.request_id',
                    'mro_parts.part_no_id',
                    'part_nos.name',
                    'part_nos.supplier_partno as partno',
                    'epno_parts.image',
                    'mro_parts.part_cost',
                    'mro_parts.qty',
                    'steps.name as step',
                    'steps.id as step_id'
                )
                ->where('request_followups.request_id', $id)
                ->where('request_followups.request_type_id', $tipo)
                ->whereColumn('supplier_proposals.user_id', '=', 'part_nos.user_id')
                ->where('users.organization_id', $user->organization_id)
                ->get();
        }


        return response()->json($partnos);
    }

    public function GetOrderLogById($id, $role)
    {
        $user = Auth::user();
        if ($role == 1 || $role == 2 || $role == 3 || $role == 4) {
            $orderLog = DB::table('request_followup_logs')
                ->join('request_followups', 'request_followup_logs.request_followup_id', 'request_followups.id')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->select(
                    'request_followup_logs.*',
                    'epno_parts.part_no as partno',
                    'part_nos.id as sp_partno_id'
                )
                ->where('request_followups.id', $id)
                // ->where('request_followups.request_id', $id)
                ->whereColumn('supplier_proposals.user_id', '=', 'part_nos.user_id')
                ->get();
        } else if ($role == 6) {
            $orderLog = DB::table('request_followup_logs')
                ->join('request_followups', 'request_followup_logs.request_followup_id', 'request_followups.id')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->select(
                    'request_followup_logs.*',
                    'request_followups.request_type_id as tipo',
                    'part_nos.supplier_partno as partno',
                    'part_nos.id as sp_partno_id'
                )
                ->where('request_followups.id', $id)
                // ->where('request_followups.request_id', $id)
                ->where('part_nos.user_id', $user->id)
                ->where('supplier_proposals.user_id', $user->id)
                ->orderBy('request_followup_id', 'asc')
                ->get();
        } else if ($role == 5) {
            $orderLog = DB::table('request_followup_logs')
                ->join('request_followups', 'request_followup_logs.request_followup_id', 'request_followups.id')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->join('users', 'users.id', 'supplier_proposals.user_id')
                ->select(
                    'request_followup_logs.*',
                    'request_followups.request_type_id as tipo',
                    'part_nos.supplier_partno as partno',
                    'part_nos.id as sp_partno_id'
                )
                ->where('request_followups.id', $id)
                // ->where('request_followups.request_id', $id)
                ->whereColumn('supplier_proposals.user_id', '=', 'part_nos.user_id')
                ->where('users.organization_id', $user->organization_id)
                ->orderBy('request_followup_id', 'asc')
                ->get();
        }

        return response()->json($orderLog);
    }

    // public function GetOrderService($servicio)
    // {
    //     $request_follow_up = DB::table('request_followups')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->select('request_types.name as name', 'request_types.id')
    //         ->where('request_followups.request_type_id', '!=', 1)
    //         ->groupBy('request_followups.request_type_id')
    //         ->get();
    //     $arrayOrder = array();
    //     foreach ($request_follow_up as $req) {
    //         $table = $req->name . '_requests';

    //         $order = DB::table('request_followups')
    //             ->join($table, 'request_followups.request_id', $table . '.id')
    //             ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //             // ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
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
    //                 [$table . '.status', '=', '1'],
    //                 [$table . '.title', '=', $servicio],
    //                 ['request_types.status', '=', '1'],
    //                 ['steps.status', '=', '1'],
    //                 ['request_followups.request_type_id', $req->id],
    //             ])
    //             ->orderBy('request_followups.created_at', 'desc')
    //             ->get();
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
    // public function GetOrderMroService($servicio)
    // {

    //     $order = DB::table('request_followups')
    //         ->join('mro_requests', 'request_followups.request_id', 'mro_requests.id')
    //         ->join('mro_parts', 'mro_requests.id', 'mro_parts.mro_request_id')
    //         ->join('epno_parts', 'epno_parts.id', 'mro_parts.epno_part_id')
    //         ->join('request_types', 'request_followups.request_type_id', 'request_types.id')
    //         ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
    //         ->join('steps', 'request_followups.step_id', 'steps.id')
    //         ->select(
    //             'request_followups.*',
    //             'supplier_proposals.total_days as dias',
    //             'mro_requests.user_id as user',
    //             'request_types.name as titulo',
    //             'request_types.name as tipo',
    //             'epno_parts.name'
    //         )
    //         ->where([
    //             ['request_followups.status', '=', '1'],
    //             ['mro_requests.status', '=', '1'],
    //             ['epno_parts.status', '=', '1'],
    //             ['epno_parts.name', '=', $servicio],
    //             ['request_types.status', '=', '1'],
    //             ['supplier_proposals.status', '=', '1'],
    //             ['steps.status', '=', '1'],
    //             ['request_followups.request_type_id', 1],
    //         ])
    //         ->groupBy('request_followups.request_id')
    //         ->orderBy('request_followups.created_at', 'desc')
    //         ->get();

    //     return response()->json($order);
    // }

    public function CancelarCotizacion(Request $request)
    {
        $user_id = Auth::user()->id;
        if ($request->request_type_id == 1) {
            $partnos = DB::table('request_followups')
                ->join('mro_parts', 'request_followups.request_id', 'mro_parts.mro_request_id')
                ->join('epno_parts', 'mro_parts.epno_part_id', 'epno_parts.id')
                ->join('part_nos', 'mro_parts.part_no_id', 'part_nos.id')
                ->join('supplier_proposals', 'request_followups.supplier_proposal_id', 'supplier_proposals.id')
                ->join('steps', 'request_followups.step_id', 'steps.id')
                ->select(
                    'request_followups.id',
                    'request_followups.request_id',
                    'mro_parts.epno_part_id',
                    'part_nos.id as partno',
                    'part_nos.current_qty',
                    'epno_parts.image',
                    'mro_parts.part_cost',
                    'mro_parts.qty',
                    'steps.name as step',
                    'steps.id as step_id'
                )
                ->where('request_followups.request_id', $request->request_id)
                ->where('request_followups.request_type_id', 1)
                ->whereColumn('supplier_proposals.user_id', '=', 'part_nos.user_id')
                ->get();

            foreach ($partnos as $part) {
                PartNo::where('id', $part->partno)->update([
                    'current_qty' => $part->current_qty + $part->qty
                ]);
            }
        }

        $cancelarCotizacion = RequestFollowup::where('id', $request->request_followup_id)
            ->update(['step_id' => 8]);
        if ($cancelarCotizacion) {
            $newComment = RequestFollowupComment::create([
                'request_followup_id' => $request->request_followup_id,
                'user_id' => $request->user,
                'comment' => $request->razon,
                'step_id' => 8,
            ]);

            if ($newComment) {
                $request_follow_up_logs = new RequestFollowupLogs();
                $request_follow_up_logs->request_followup_id = $request->request_followup_id;
                $request_follow_up_logs->step_id = 8;
                $request_follow_up_logs->user_id = $user_id;

                if ($request_follow_up_logs->save()) {

                    $supplier_info = DB::table('request_followups')
                        ->join('supplier_proposals', 'request_followups.id', 'supplier_proposals.request_followup_id')
                        ->select('supplier_proposals.user_id as id')
                        ->where('request_followups.id', $request->request_followup_id)
                        ->where('supplier_proposals.status', 1)
                        ->get('id');

                    $user_info = User::select('email', 'role_id')->where('id', $request->user)->first();

                    $notificationUser = new Notification();
                    $notificationUser->user_id = $request->user;
                    $notificationUser->type_notification_id = 11;
                    $notificationUser->table_name = "request_followups";
                    $notificationUser->table_name_id = $request->request_followup_id;
                    if ($notificationUser->save()) {
                        DB::select('call limitNotificationCount (?)', array($request->user));
                    }

                    Notify::route('mail', $user_info->email)
                        ->notify(new OrderDetailsNotification(11, $user_info->role_id, $request->request_followup_id, $request->request_type_id, $request->purchase_order));

                    $AgentId = User::where('role_id', 1)->get('id');

                    foreach ($AgentId as $Agid) {
                        $user_agent = User::select('email', 'role_id')->where('id', $Agid->id)->first();

                        $notificationAgent = new Notification();
                        $notificationAgent->user_id = $Agid->id;
                        $notificationAgent->type_notification_id = 11;
                        $notificationAgent->table_name = "request_followups";
                        $notificationAgent->table_name_id = $request->request_followup_id;
                        if ($notificationAgent->save()) {
                            DB::select('call limitNotificationCount (?)', array($Agid->id));
                        }

                        Notify::route('mail', $user_agent->email)
                            ->notify(new OrderDetailsNotification(11, $user_agent->role_id, $request->request_followup_id, $request->request_type_id, $request->purchase_order));
                    }

                    foreach ($supplier_info as $sp_info) {
                        $user_supp = User::select('email', 'role_id')->where('id', $sp_info->id)->first();

                        $notificationSupplier = new Notification();
                        $notificationSupplier->user_id = $sp_info->id;
                        $notificationSupplier->type_notification_id = 11;
                        $notificationSupplier->table_name = "request_followups";
                        $notificationSupplier->table_name_id = $request->request_followup_id;
                        if ($notificationSupplier->save()) {
                            DB::select('call limitNotificationCount (?)', array($sp_info->id));
                        }

                        Notify::route('mail', $user_supp->email)
                            ->notify(new OrderDetailsNotification(11, $user_supp->role_id, $request->request_followup_id, $request->request_type_id, $request->purchase_order));
                    }
                } else {
                    $response['success'] = false;
                    return $response;
                }
            } else {
                $response['success'] = false;
                return $response;
            }
        } else {
            $response['success'] = false;
            return $response;
        }
    }

    public function markAsRead(Request $request)
    {
        foreach ($request->keys as $ntf) {
            Notification::where('id', $ntf)->update([
                'seen' => 1
            ]);
        }
        $response['success'] = true;
        return $response;
    }

    public function ChangeProfileImage(Request $request)
    {
        $file =  $request->file('img');
        $originalname = $file->getClientOriginalName();
        $pathImage = Storage::putFileAs('/public/uploads/', $file,  $originalname);
        $urlImg = Storage::url($pathImage);

        $imgFile = Organization::where('id', $request->org_id)->update(['logo' => $urlImg]);

        if ($imgFile) {
            $response['message'] = "foto cambiada correctamente";
            $response['success'] = true;
            return $response;
        } else {
            $response['message'] = "no se pudo actualizar la imagen";
            $response['success'] = false;
            return $response;
        }
    }

    public function GetAllComplaints()
    {
        $user = Auth::user();

        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 5 || $user->role_id == 10) {
            $quejas = Complaint::with([
                'User', 'Organization', 'Logs'
            ])->get();
        } elseif ($user->role_id == 4) {
            $quejas = Complaint::with([
                'User', 'Organization'
            ])->where('user_id', $user->id)->get();
        } elseif ($user->role_id == 6) {
            $quejas = Complaint::with([
                'User', 'Organization', 'Subservices.Suppliers.Proposal'
            ])->whereHas('Subservices.Suppliers.Proposal', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('user_id', $user->id)->get();
        }

        return response()->json($quejas);
    }

    public function GetComplaintById($id)
    {
        $user = Auth::user();
        if ($user->role_id == 4) {
            $queja = Complaint::with([
                'Step', 'User', 'Organization.colony.postalCode.city.state.country',
                'Order', 'Service', 'Subservices.Sub.step', 'Subservices.Sub.category', 'Subservices.Suppliers.Proposal', 'Subservices.Suppliers.User.organization',
                'SubserviceComplaintClient' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }, 'SubserviceComplaintClient.Complaint',
                'SubserviceComplaintClient.User.organization', 'Logs.Step', 'Logs.User'
            ])->where('id', $id)->first();

            $users = User::join(
                'organizations',
                'users.organization_id',
                'organizations.id'
            )->where('users.role_id', 10)->select(
                'users.id',
                'users.email',
                'users.name as user_name',
                'users.role_id as role_id',
                'organizations.id as org_id',
                'organizations.name as org_name',
                'organizations.logo',
            )->get();
        } elseif (
            $user->role_id == 5 ||
            $user->role_id == 3 || $user->role_id == 1 || $user->role_id == 2 || $user->role_id == 10
        ) {
            $queja = Complaint::with([
                'Step', 'User', 'Organization.colony.postalCode.city.state.country',
                'Order', 'Service', 'Subservices.Sub.step', 'Subservices.Sub.category', 'Subservices.Suppliers.Proposal', 'Subservices.Suppliers.User.organization',
                'SubserviceComplaintClient.Complaint', 'SubserviceComplaintClient.User.organization',
                'SubserviceComplaintEpno.Complaint', 'SubserviceComplaintEpno.User.organization',
                'Logs.Step', 'Logs.User',
            ])->where('id', $id)->first();

            $client = Complaint::join('users', 'complaints.user_id', 'users.id')
                ->join(
                    'organizations',
                    'users.organization_id',
                    'organizations.id'
                )->where('complaints.id', $id)->select(
                    'users.id',
                    'users.email',
                    'users.name as user_name',
                    'users.role_id as role',
                    'organizations.id as org_id',
                    'organizations.name as org_name',
                    'organizations.logo',
                )->get();

            $coll_client = collect($client);

            $suppliers = Complaint::join('subservice_complaints', 'subservice_complaints.complaint_id', 'complaints.id')
                ->join('supplier_proposal_complaints', 'supplier_proposal_complaints.subservice_complaint_id', 'subservice_complaints.id')
                ->join('supplier_proposals', 'supplier_proposal_complaints.supplier_proposal_id', 'supplier_proposals.id')
                ->join('users', 'supplier_proposals.user_id', 'users.id')
                ->join(
                    'organizations',
                    'users.organization_id',
                    'organizations.id'
                )->where('complaints.id', $id)->select(
                    'users.id',
                    'users.email',
                    'users.name as user_name',
                    'users.role_id as role',
                    'organizations.id as org_id',
                    'organizations.name as org_name',
                    'organizations.logo',
                )->get();
            $coll_supp = collect($suppliers);


            $users = $coll_client->concat($coll_supp);
        } else {
            $queja = Complaint::with([
                'Step', 'User', 'Organization.colony.postalCode.city.state.country', 'Order', 'Service',
                'Subservices.Sub.category', 'Subservices' => function ($query) use ($user) {
                    $query->join('supplier_proposal_complaints', 'supplier_proposal_complaints.subservice_complaint_id', 'subservice_complaints.id')
                        ->join('supplier_proposals', 'supplier_proposal_complaints.supplier_proposal_id', 'supplier_proposals.id')
                        ->join('users','supplier_proposals.user_id','users.id')
                        ->addSelect('*','supplier_proposal_complaints.id as supplier_id')->where('supplier_proposals.user_id', $user->id);
                }, 'SubserviceComplaintEpno' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }, 'SubserviceComplaintEpno.Complaint', 'SubserviceComplaintEpno.User.organization', 'Logs.Step', 'Logs.User'
            ])->where('id', $id)->first();

            $users = User::join(
                'organizations',
                'users.organization_id',
                'organizations.id'
            )->where('users.role_id', 10)->select(
                'users.id',
                'users.email',
                'users.name as user_name',
                'users.role_id as role',
                'organizations.id as org_id',
                'organizations.name as org_name',
                'organizations.logo',
            )->get();
        }

        return response()->json([
            'queja' => $queja,
            'users' => $users,
        ]);
    }

    public function ResponseEvidence(Request $request)
    {
        try {
            // return $request;

            $user = Auth::user();
            $org = Organization::where('id', $user->organization_id)->first('name');

            if ($request->tabla == "complaint_epno_to_supplier_evidence") {
                if ($user->role_id == 6) {

                    $file = $request->evidencia;
                    $originalname = $file->getClientOriginalName();
                    $path = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                    $url = Storage::url($path);

                    $respuesta = ComplaintEpnoToSupplierEvidence::where('id', $request->evidencia_id)
                        ->update([
                            'supplier_description' => $request->descripcion,
                            'supplier_file' => $url,
                            'supplier_file_name' => $originalname,
                        ]);

                    if ($respuesta) {
                        Notify::route('mail', 'larissa.jasso@epno.com.mx')
                            ->notify(new ComplaintDetailsNotification(
                                26,
                                5,
                                $request->complaint_id,
                                $request->complaint_num,
                                $request->service_title,
                                $user->name,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));

                        $response['message'] = "Respuesta guardada correctamente.";
                        $response['success'] = true;
                        return $response;
                    } else {
                        $response['message'] = "Hubo un error al dar respues a esta evidencia.";
                        $response['success'] = false;
                        return $response;
                    }
                } else {
                    $response['message'] = "No cuentas con los permisos para realizar esta acción.";
                    $response['success'] = false;
                    return $response;
                }
            } else if ($request->tabla == "complaint_client_to_epno_evidence") {

                if ($user->role_id == 10) {
                    $file = $request->evidencia;
                    $originalname = $file->getClientOriginalName();
                    $path = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                    $url = Storage::url($path);

                    $respuesta = ComplaintClientToEpnoEvidence::where('id', $request->evidencia_id)
                        ->update([
                            'epno_description' => $request->descripcion,
                            'epno_file' => $url,
                            'epno_file_name' => $originalname,
                        ]);

                    if ($respuesta) {

                        $notificationClient = new Notification();
                        $notificationClient->user_id = $request->user_id;
                        $notificationClient->notification_type_id = 26;
                        $notificationClient->table_name = "complaints";
                        $notificationClient->table_id = $request->complaint_id;
                        if ($notificationClient->save()) {
                            DB::select('call limitNotificationCount (?)', array($request->user_id));
                        }

                        Notify::route('mail', $request->client_mail)
                            ->notify(new ComplaintDetailsNotification(
                                26,
                                4,
                                $request->complaint_id,
                                $request->complaint_num,
                                $request->service_title,
                                $user->name,
                                $user->phone,
                                $user->email,
                                $org->name,
                            ));

                        $response['success'] = true;
                        $response['message'] = "Respuesta guardada correctamente.";
                        return $response;
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Hubo un error al dar respues a esta evidencia.";
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "No cuentas con los permisos para realizar esta acción.";
                    return $response;
                }
            }
        } catch (\Throwable $th) {
            $response['message'] = "Hubo un error al seguir el proceso.";
            $response['success'] = false;
            return $response;
        }
    }

    public function ChangeStep(Request $request)
    {

        return $request;

        $user = Auth::user();

        $org = Organization::where('id', $user->organization_id)->first('name');

        $user_epno = User::where('role_id', 10)->first();


        if ($request->opcion == 13) {
            $ntf_type = 28;
            $ntf_agent = 28;
            $desc = "Queja enviada a disputa.";
        } else if ($request->opcion == 3) {
            $ntf_type = 5;
            $ntf_agent = 5;
            $desc = "Queja enviada a pendiente de aprobación.";
            if (isset($request->po) && isset($request->nueva_cotizacion)) {

                $file = $request->nueva_cotizacion;
                $originalname = $file->getClientOriginalName();
                $path = Storage::putFileAs('/public/uploads/', $file,  $originalname);
                $url = Storage::url($path);

                Complaint::join('subservice_complaints', 'subservice_complaints.complaint_id', 'complaints.id')
                    ->join('supplier_proposal_complaints', 'supplier_proposal_complaints.subservice_complaint_id', 'subservice_complaints.id')
                    ->where('complaints.id', $request->complaint_id)
                    ->update([
                        'complaints.new_po' => 1,
                        'complaints.epno_cot_file' => $url,
                        'supplier_proposal_complaints.new_po' => 1
                    ]);
            }
        } else if ($request->opcion == 4) {
            $ntf_type = 6;
            $ntf_agent = 6;
            $desc = "Orden en construcción";
        }
        // else if ($request->opcion == 5) {
        //     $ntf_type = 8;
        //     $ntf_agent = 12;
        //     $desc = "Orden en inspección";
        // } else if ($request->opcion == 6) {
        //     $ntf_type = 7;
        //     $ntf_agent = 7;
        //     $desc = "Orden en camino para entrega.";
        // } else if ($request->opcion == 7) {
        //     $ntf_type = 9;
        //     $ntf_agent = 9;
        //     $desc = "Orden entregada correctamente.";
        // }

        if (isset($request->costo)) {
            $costo = $request->costo;
        } else {
            $costo = 0.00;
        }
        if (isset($request->descripcion)) {
            $descripcion = $request->descripcion;
        } else {
            $descripcion = $desc;
        }

        $changeStep = Complaint::join('subservice_complaints', 'subservice_complaints.complaint_id', 'complaints.id')
            ->where('id', $request->complaint_id)
            ->increment('complaints.rework_cost', $costo, array(
                'complaints.step_id' => $request->opcion,
                'subservice_complaints.step_id' => $request->opcion,
            ));

        if ($changeStep) {

            $log = ComplaintLog::create([
                'complaint_id' => $request->complaint_id,
                'user_id' => $user->id,
                'step_id' => $request->opcion,
                'cost' => $costo,
                'description' => $descripcion,
            ]);

            if ($log) {

                $notificationClient = new Notification();
                $notificationClient->user_id = $request->user_id;
                $notificationClient->notification_type_id = $ntf_type;
                $notificationClient->table_name = "complaints";
                $notificationClient->table_id = $request->complaint_id;
                if ($notificationClient->save()) {
                    DB::select('call limitNotificationCount (?)', array($request->user_id));
                }

                Notify::route('mail', $request->user_email)
                    ->notify(new ComplaintDetailsNotification(
                        $ntf_type,
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
                $notificationEpno->notification_type_id = $ntf_agent;
                $notificationEpno->table_name = "complaints";
                $notificationEpno->table_id = $request->complaint_id;
                if ($notificationEpno->save()) {
                    DB::select('call limitNotificationCount (?)', array($user_epno->id));
                }

                Notify::route('mail', $user_epno->email)
                    ->notify(new ComplaintDetailsNotification(
                        $ntf_agent,
                        10,
                        $request->complaint_id,
                        $request->complaint_num,
                        $request->service_title,
                        $user->name,
                        $user->phone,
                        $user->email,
                        $org->name,
                    ));

                if ($user->role_id !== 6 && isset($request->suppliers)) {

                    foreach ($request->suppliers as $supp) {

                        SupplierProposalComplaint::where('id', $supp->id)->update(['step_id' => $request->opcion]);

                        SupplierProposalComplaintLog::create([
                            'supplier_proposal_complaint_id' => $supp->id,
                            'user_id' => $user->id,
                            'step_id' => $request->opcion,
                        ]);

                        $sp_info = json_decode($supp);
                        if ($sp_info->user->id !== $user->id) {

                            $notificationSupplier = new Notification();
                            $notificationSupplier->user_id = $sp_info->user->id;
                            $notificationSupplier->notification_type_id = $ntf_type;
                            $notificationSupplier->table_name = "complaints";
                            $notificationSupplier->table_id = $request->complaint_id;
                            if ($notificationSupplier->save()) {
                                DB::select('call limitNotificationCount (?)', array($sp_info->user->id));
                            }

                            Notify::route('mail', $sp_info->user->email)
                                ->notify(new ComplaintDetailsNotification(
                                    $ntf_type,
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
                } else {
                    $notificationSupplier = new Notification();
                    $notificationSupplier->user_id = $user->id;
                    $notificationSupplier->notification_type_id = $ntf_type;
                    $notificationSupplier->table_name = "complaints";
                    $notificationSupplier->table_id = $request->complaint_id;
                    if ($notificationSupplier->save()) {
                        DB::select('call limitNotificationCount (?)', array($user->id));
                    }

                    Notify::route('mail', $user->email)
                        ->notify(new ComplaintDetailsNotification(
                            $ntf_type,
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
                $response['success'] = false;
                $response['message'] = "Hubo un error al guardar el log del movimiento.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Hubo un error al actualizar el step de la queja.";
            return $response;
        }
    }

    public function ChangeStepSupplier(Request $request)
    {
        return $request;

        $user = Auth::user();
        $org = Organization::where('id', $user->organization_id)->first('name');
        $user_epno = User::where('role_id', 10)->first();

        $countSupplierComplaint = SupplierProposalComplaint::where('subservice_complaint_id', $request->subservice_complaint)->count();
        $subserviceCount = SubserviceComplaint::where('complaint_id', $request->complaint_id)->count();

        if ($request->opcion == 5) {
            $ntf_type = 8;
            $ntf_agent = 12;
            $desc = "Orden en inspección";
        } else if ($request->opcion == 6) {
            $ntf_type = 7;
            $ntf_agent = 7;
            $desc = "Orden en camino para entrega.";
        } else if ($request->opcion == 7) {
            $ntf_type = 9;
            $ntf_agent = 9;
            $desc = "Orden entregada correctamente.";
        }


        if (isset($request->costo)) {
            $costo = $request->costo;
        } else {
            $costo = 0.00;
        }
        if (isset($request->descripcion)) {
            $descripcion = $request->descripcion;
        } else {
            $descripcion = $desc;
        }

        $changeStep = SupplierProposalComplaint::join('subservice_complaints', 'subservice_complaints.id', 'supplier_proposal_complaints.subservice_complaint_id')
            ->join('complaints', 'subservice_complaints.complaint_id', 'complaints.id')
            ->where('id', $request->supplier_complaint_id)
            ->update([
                'supplier_proposal_complaints.step_id' => $request->opcion,
                'supplier_proposal_complaints.rework_cost' => DB::raw('supplier_proposal_complaints.rework_cost + ' . $costo),
                'subservice_complaints.rework_cost' => DB::raw('subservice_complaints.rework_cost + ' . $costo),
                'complaints.rework_cost' => DB::raw('complaints.rework_cost + ' . $costo),
            ]);

        if ($changeStep) {
            $Supplierlog = SupplierProposalComplaintLog::create([
                'supplier_proposal_complaint_id' => $request->supplier_complaint_id,
                'user_id' => $user->id,
                'step_id' => $request->opcion,
                'cost' => $costo,
                'description' => $descripcion,
            ]);

            if ($Supplierlog) {
                $this->callNotification(
                    $request->user_id,
                    $ntf_type,
                    $request->complaint_id,
                    $request->user_email,
                    4,
                    $request->complaint_num,
                    $request->supp_desc,
                    $user->name,
                    $user->phone,
                    $user->email,
                    $org->name
                );

                $this->callNotification(
                    $user_epno->id,
                    $ntf_agent,
                    $request->complaint_id,
                    $user_epno->email,
                    10,
                    $request->complaint_num,
                    $request->supp_desc,
                    $user->name,
                    $user->phone,
                    $user->email,
                    $org->name
                );

                $this->callNotification(
                    $request->supplier_id,
                    $ntf_type,
                    $request->complaint_id,
                    $request->supplier_email,
                    6,
                    $request->complaint_num,
                    $request->supp_desc,
                    $user->name,
                    $user->phone,
                    $user->email,
                    $org->name
                );


                $countSupplierComplaintStep = SupplierProposalComplaint::where('subservice_complaint_id', $request->subservice_complaint)
                    ->where('step_id', $request->opcion)->count();

                if ($countSupplierComplaint == $countSupplierComplaintStep) {

                    $subserviceStep = SubserviceComplaint::where('id', $request->subservice_complaint)->update(['step_id' => $request->opcion]);

                    if ($subserviceStep) {
                        $this->callNotification(
                            $request->user_id,
                            $ntf_type,
                            $request->complaint_id,
                            $request->user_email,
                            4,
                            $request->complaint_num,
                            $request->subservice_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name
                        );

                        $this->callNotification(
                            $user_epno->id,
                            $ntf_agent,
                            $request->complaint_id,
                            $user_epno->email,
                            10,
                            $request->complaint_num,
                            $request->subservice_title,
                            $user->name,
                            $user->phone,
                            $user->email,
                            $org->name
                        );

                        foreach ($request->suppliers as $supp) {

                            $sp_info = json_decode($supp);
                            if ($sp_info->user->id !== $user->id) {

                                $this->callNotification(
                                    $sp_info->user->id,
                                    $ntf_type,
                                    $request->complaint_id,
                                    $sp_info->user->email,
                                    6,
                                    $request->complaint_num,
                                    $request->subservice_title,
                                    $user->name,
                                    $user->phone,
                                    $user->email,
                                    $org->name
                                );
                            }
                        }

                        $subserviceCountStep = SubserviceComplaint::where('id', $request->subservice_complaint)->update(['step_id' => $request->opcion]);

                        if ($subserviceCount == $subserviceCountStep) {
                            $complaintStep = Complaint::where('id', $request->complaint_id)->update(['step_id' => $request->opcion]);

                            if ($complaintStep) {
                                $log = ComplaintLog::create([
                                    'complaint_id' => $request->complaint_id,
                                    'user_id' => $user->id,
                                    'step_id' => $request->opcion,
                                    'description' => $descripcion,
                                ]);

                                if ($log) {
                                    $this->callNotification(
                                        $request->user_id,
                                        $ntf_type,
                                        $request->complaint_id,
                                        $request->user_email,
                                        4,
                                        $request->complaint_num,
                                        $request->service_title,
                                        $user->name,
                                        $user->phone,
                                        $user->email,
                                        $org->name
                                    );

                                    $this->callNotification(
                                        $user_epno->id,
                                        $ntf_agent,
                                        $request->complaint_id,
                                        $user_epno->email,
                                        10,
                                        $request->complaint_num,
                                        $request->service_title,
                                        $user->name,
                                        $user->phone,
                                        $user->email,
                                        $org->name
                                    );

                                    foreach ($request->suppliers as $supp) {

                                        $sp_info = json_decode($supp);
                                        if ($sp_info->user->id !== $user->id) {

                                            $this->callNotification(
                                                $sp_info->user->id,
                                                $ntf_type,
                                                $request->complaint_id,
                                                $sp_info->user->email,
                                                6,
                                                $request->complaint_num,
                                                $request->subservice_title,
                                                $user->name,
                                                $user->phone,
                                                $user->email,
                                                $org->name
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $response['success'] = true;
                $response['message'] = "Cambios hechos correctamente.";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "Hubo un error al guardar el registro del cambio.";
                return $response;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Hubo un error al actualizar el step.";
            return $response;
        }
    }
    public function registerInvited(Request $request)
    {
        try {
            $request->validate([
                'invited_name'      => 'required',
                'invited_dish'      => 'required',
                'invited_dessert'   => 'required'
            ]);
            DB::table('wedding')->insert([
                'name'      => $request->invited_name,
                'plate'     => $request->invited_dish,
                'dessert'   => $request->invited_dessert
            ]);
            $success = true;
            $message = 'Gracias por tu registro. Esperamos verte ese día.';
            
        } catch (\Throwable $th) {
            $success = false;
            $message = $th;
        }
        $response = ['message' => $message, 'success' => $success];
        return $response;
    }
    public function getRegistered(Request $request)
    {
        $response = DB::table('wedding')->select('name')->get();
        return $response;
    }
}
