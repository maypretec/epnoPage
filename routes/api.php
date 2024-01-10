<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/ping', [GeneralController::class, 'ping']);
Route::post('/solicitudSoftware', 'ClientController@solicitudSoftware');
Route::get('/country', [GeneralController::class, 'showCountry']);
Route::get('/state/{id}', [GeneralController::class, 'showState']);
Route::get('/city/{id}', [GeneralController::class, 'showCity']);
Route::get('/pc/{id}', [GeneralController::class, 'showPC']);
Route::get('/colony/{id}', [GeneralController::class, 'showColony']);

Route::post('/registerInvited', [GeneralController::class, 'registerInvited']);
Route::get('/registered', [GeneralController::class, 'getRegistered']);

Route::post('/add_bundle_products/{id}', [ClientController::class, 'AddBundleProducts']);

Route::post('/delete_products/{id}', [ClientController::class, 'DeleteProducts']);
Route::get('/get_catalogo/{id}', [ClientController::class, 'GetCatalogoById']);
Route::get('/get_related_products/{id}/{category}', [ClientController::class, 'GetRelatedProducts']);
Route::get('/get_product_details/{id}', [ClientController::class, 'GetProductoById']);
Route::get('/get_bundle_products/{id}', [ClientController::class, 'GetBundleProducts']);
Route::post('/delete_bundle_part/{id}', [ClientController::class, 'DeleteBundlePart']);
Route::get('/get_order_supplier_proposals/{id}', 'AgentController@GetSupplierProposalsOrderById');
Route::get('/get_order_log/{id}', 'ClientController@GetOrderLogById');
Route::get('/get_epno_parts', [AgentController::class, 'GetEpnoParts']);
Route::get('/get_partnos', [AgentController::class, 'GetPartnos']);
Route::post('/send_epno_part', [AgentController::class, 'SendEpnoPart']);
Route::post('/change_notification_status/{id}', [GeneralController::class, 'ChangeNotificationStatus']);
Route::get('/get_new_users_request', [AgentController::class, 'GetNewUsersRequest']);
Route::get('/get_vs', [AgentController::class, 'GetVS']);
Route::post('/response_new_user_request', [AgentController::class, 'ResponseNewUserRequest']);
// Route::get('/get_new_users_request_supplier', 'AgentController@GetNewUsersRequestSupplier');
Route::get('/new_user_request_notification', [AgentController::class, 'GetNewUserRequestNotification']);

//Auth Routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkResponse']);
Route::post('/password/reset', [AuthController::class, 'sendResetResponse']);
Route::get('/categories', [GeneralController::class, 'GetCategories']);
Route::get('/email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify');
Route::post('/add_product_to_package', [ClientController::class, 'AddProductToPackage']);

Route::get('/profile_locationsAgent/{id}', 'AgentController@profileLocationsAgent');


// Seguimiento de ordenes

// Borrar todas esas apis de ordenes 
// Route::get('/get_open_orders_mro/{id}/{type}', 'ClientController@GetOpenOrdersMro');
// Route::get('/get_open_orders_supplier/{id}/{type}', 'SupplierController@GetOpenOrderSupplier');
// Route::get('/get_open_orders_supplier_mro/{id}/{type}', 'SupplierController@GetOpenOrderSupplierMro');
// Route::get('/get_open_orders_supplier_admin/{id}/{type}', 'SupplierController@GetOpenOrderSupplierAdmin');
// Route::get('/get_open_orders_supplier_mro_admin/{id}/{type}', 'SupplierController@GetOpenOrderSupplierMroAdmin');
// Route::get('/get_open_orders_admin/{id}/{type}', 'ClientController@GetOpenOrdersAdmin');
// Route::get('/get_open_orders_mro_admin/{id}/{type}', 'ClientController@GetOpenOrdersMroAdmin');
// Route::get('/get_close_orders_client_reviews/{role}', 'ClientController@GetCloseOrdersReviews');
// Route::get('/get_close_orders_supplier', 'SupplierController@GetCloseOrderSupplier');
// Route::get('/get_close_orders', 'ClientController@GetCloseOrders');
// Route::get('/get_open_orders_agent/{type}', 'AgentController@GetOpenOrders');
// Route::get('/get_close_orders_agent', 'AgentController@GetCloseOrders');
// Route::get('/get_close_orders_supplier_mro', 'SupplierController@GetCloseOrderSupplierMro');
// Route::get('/get_close_orders_mro', 'ClientController@GetCloseOrdersMro');
// Route::get('/get_open_orders_agent_mro/{type}', 'AgentController@GetOpenOrdersMro');
// Route::get('/get_close_orders_agent_mro', 'AgentController@GetCloseOrdersMro');
//Borrar Route::get('/get_close_orders_agent_mro_reviews/{role}', 'AgentController@GetCloseOrdersMroReviews');
//Borrar Route::get('/get_close_orders_agent_reviews/{role}', 'AgentController@GetCloseOrdersReviews');
//Borrar Route::get('/get_close_orders_admin_mro_reviews/{role}', 'GeneralController@GetCloseOrdersMroReviews');
//Borrar Route::get('/get_close_orders_std_reviews/{role}', 'GeneralController@GetCloseOrdersReviewsSTD');
// Route::get('/get_close_orders_supplier_admin', 'SupplierController@GetCloseOrderSupplierAdmin');
// Route::get('/get_close_orders_admin', 'ClientController@GetCloseOrdersAdmin');
// Route::get('/get_close_orders_supplier_mro_admin', 'SupplierController@GetCloseOrderSupplierMroAdmin');
// Route::get('/get_close_orders_mro_admin', 'ClientController@GetCloseOrdersMroAdmin');
// Route::get('/get_open_orders_service/{service}', 'GeneralController@GetOrderService');
// Route::get('/get_open_orders_mro_service/{service}', 'GeneralController@GetOrderMroService');

//Borrar Route::post('/new_service', [AgentController::class, 'NewService']);
Route::post('/edit_epno_part', [AgentController::class, 'EditEpnoPart']);
Route::post('/create_general_service_files/{order}/{type}', 'ClientController@CreateGeneralServiceFiles');
Route::post('/edit_supplier_partno', [SupplierController::class, 'EditSupplierPartno']);


//Consumo
//Route::get('/all_consumo', 'AgentController@consumoAgent');
// Route::get('/supp_consumo', 'SupplierController@consumoSupplier');
// Route::get('/client_consumo', 'ClientController@consumoClient');

//Ordenes
// Route::get('/ordenes_Trans', 'AgentController@ordenesTransito');

// APIS QUE DEBEN IR EN EL MIDDLEWARE
Route::post('/change_service_info', [AgentController::class, 'ChangeServiceInfo']);



Route::middleware('auth:api')->group(function () {
    Route::post('/orders/new', [ClientController::class, 'AddService']);
    Route::post('/order_cancel_request', [ClientController::class, 'OrderCancelRequest']);
    Route::post('/subir_client_nueva_po', [ClientController::class, 'ClientNewPO']);
    Route::post('/add_new_subservice', [AgentController::class, 'AddNewSubservice']);
    Route::post('/epno_select_suppliers', [AgentController::class, 'EpnoSelectSuppliers']);
    Route::post('/supp_cot_again', [AgentController::class, 'SuppCotAgain']);
    Route::get('/get_all_orders/{type}', [GeneralController::class, 'GetAllOrders']);
    Route::post('/response_evidence', [GeneralController::class, 'ResponseEvidence']);
    // Cambia el estado de toda la queja
    Route::post('/change_step', [GeneralController::class, 'ChangeStep']);
    // Cambio de estado individual por cada supplier que haya agregado
    Route::post('/change_step_supplier', [GeneralController::class, 'ChangeStepSupplier']);
    Route::post('/send_po_supplier', [AgentController::class, 'SendPoSupplier']);
    Route::post('/close_complaint', [AgentController::class, 'CloseComplaint']);

    Route::get('email/resend', [AuthController::class, 'resend'])->name('verification.resend');
    Route::post('/perfilSupplier', [SupplierController::class, 'perfilSupplier']);

    Route::post('/addPart', [SupplierController::class, 'addPart']);
    Route::get('/partnos', [SupplierController::class, 'showPartnos']);
    // El proveedor cotiza la orden y se actualiza su registro
    Route::post('/add_supplier_cot', [SupplierController::class, 'AddSupplierCot']);
    // el proveedor rechaza una orden y explica el porque
    Route::post('/rechazar_cot_supplier', [SupplierController::class, 'RechazarCotSupplier']);
    Route::post('/change_user_vs', [AgentController::class, 'ChangeVS']);
    Route::get('/get_all_catalogos', [AgentController::class, 'GetAllCatalogos']);
    Route::post('/new_catalogo', [AgentController::class, 'NewCatalogo']);
    Route::post('/add_subservice_suppliers', [AgentController::class, 'AddSubserviceSuppliers']);
    Route::post('/show_supplier_proposals', [AgentController::class, 'ShowSupplierProposals']);
    Route::post('/show_subservice_complaint_suppliers', [AgentController::class, 'ShowSubserviceComplaintSupplier']);
    Route::post('/add_suppliers_complaint', [AgentController::class, 'AddSupplierComplaint']);
    Route::post('/proccess_internal_complaint', [AgentController::class, 'ProccessInternalComplaint']);
    Route::post('/change_complaint_type', [AgentController::class, 'ChangeComplaintType']);
    Route::post('/cancelar_rechazar_queja', [AgentController::class, 'CancelarRechazarQueja']);

    Route::post('/add_request', [ClientController::class, 'AddRequest']);
    Route::get('/acept_cot_show_supp/{service}', [ClientController::class, 'OrdenListaShowSupp']);
    Route::post('/subir_client_po', [ClientController::class, 'SubirClientPO']);
    Route::post('/mro_part_up_product_qty', [ClientController::class, 'MroPartUpProductQty']);
    // Route::get('/get_num_products', 'ClientController@GetNumberProducts');
    Route::get('/get_products', [ClientController::class, 'GetProducts']);
    Route::post('/add_products', [ClientController::class, 'AddProducts']);
    Route::post('/add_package',  [ClientController::class, 'AddPackage']);
    // El cliente da click en el checkbox para saber que cot si acepta, antes de cambiar al step 4
    Route::post('/acept_decline_supplier',  [ClientController::class, 'AceptDeclineSuppier']);
    Route::post('/perfilCustomer', 'ClientController@perfilCustomer');
    Route::get('/ordenes_TransTotal', 'AgentController@ordenesTransitoTotal');
    // Route::get('/ordenes_TransOtros', 'SupplierController@ordenesTransitoOtros');
    Route::get('/totalUsersAgent', 'AgentController@totalUsuariosAgent');
    Route::get('/totalUsers', 'SupplierController@totalUsuarios');
    // Route::get('/reviews_agent', 'AgentController@reviewsAgent');
    Route::get('/profile_locationsAdmin', 'SupplierController@profileLocationsAdmin');
    // Route::get('/reviews_admin', 'SupplierController@reviewsAdmin');
    Route::get('/profile_locationStd', 'ClientController@profileLocationStd');
    Route::get('/profile_info/{id}', [UserController::class, 'profileInfo']);
    Route::get('/user_comments/{id}', [CommentController::class, 'UserComments']);
    Route::get('/organization_categories/{id}',[OrganizationController::class, 'CategoriesList']);
    Route::get('/ordenes_perfil', 'AgentController@ordenesPerfil');
    Route::get('/ordenes_perfilAdmin', 'SupplierController@ordenesPerfilAdmin');
    Route::get('/ordenes_perfilStd', 'ClientController@ordenesPerfilStd');
    Route::get('/gastos_perfil', 'AgentController@gastosPerfil');
    Route::get('/gastos_perfilAdmin_supplier', 'SupplierController@gastosPerfilAdmin');
    Route::get('/gastos_perfilAdmin_client', 'ClientController@gastosPerfilAdmin');
    Route::get('/consumo_clientes', 'AgentController@ConsumoClientes');
    Route::get('/ventas_supplier', 'AgentController@VentasSupplier');
    Route::get('/gastos_perfilClient', 'ClientController@gastosPerfilClient');
    Route::get('/productos_serviciosAdmin', 'SupplierController@productosServiciosAdmin');
    Route::get('/usuarios_activosAgent', 'AgentController@usuariosActivosAgent');
    Route::get('/usuarios_activosAdmin', 'SupplierController@usuariosActivosAdmin');

    Route::get('/consumo_clientesAgent', 'AgentController@consumoClientesAgent');
    Route::get('/consumo_supplierAgent', 'AgentController@consumoSupplierAgent');
    Route::get('/steps_agent', 'AgentController@stepsAgent');
    Route::post('/change_profile_image', [GeneralController::class, 'ChangeProfileImage']);
    Route::get('/get_all_complaints', [GeneralController::class, 'GetAllComplaints']);
    Route::get('/get_complaint/{id}', [GeneralController::class, 'GetComplaintById']);
    Route::post('/create_location', 'UserController@createLocation');
    Route::get('/productos_serviciosAgent', 'AgentController@productosServiciosAgent');

    // Route::get('/get_order/{role}/{id}', 'ClientController@GetOrderById');
    Route::get('/get_order/{id}', [GeneralController::class, 'GetOrderById']);
    Route::get('/get_order_mro/{role}/{id}', 'GeneralController@GetOrderById');

    Route::post('/create_new_user', [UserController::class, 'Create']);
    Route::get('/user_complete', 'UserController@UserComplete');
    Route::get('/user_role', 'UserController@UserRole');
    Route::get('/get_users', 'UserController@GetUsers');
    Route::post('/add_category', [AgentController::class, 'AddCategory']);
    Route::post('/add_unit', [AgentController::class, 'AddUnit']);
    Route::get('/get_categories', [AgentController::class, 'GetCategories']);
    Route::get('/get_units', [AgentController::class, 'GetUnits']);
    Route::post('/add_partnos', [AgentController::class, 'AddPartnos']);
    Route::post('/subir_client_cot', [AgentController::class, 'AddClientCot']);

    Route::post('/subir-cotizacion', 'SupplierController@AddCotizacion');
    Route::post('/subir-cotizacion-mro', 'SupplierController@AddCotizacionMro');
    Route::post('/subir-cotizacion-agent-mro', 'AgentController@AddCotizacionMro');
    Route::get('/get-cotization-file/{id}', 'AgentController@GetCotizationFile');
    Route::get('/get-cotization-file-agent/{id}', 'ClientController@GetCotizationFile');
    Route::get('/get-cotization-files-agent/{service}/{id}', 'AgentController@GetCotizationFiles');
    Route::get('/get-cotization-files-supplier/{service}/{id}', 'SupplierController@GetCotizationFiles');
    Route::get('/get-cotization-files/{service}/{id}', 'ClientController@GetCotizationFiles');
    // borrar
    // Route::post('/supplier_proposal_winner', 'ClientController@SupplierProposalWinner');
    Route::get('/fecha_entrega/{id}', 'ClientController@FechaEntrega');
    // borrar
    // Route::get('/acept_supplier_proposal_winner_cost/{id}/{role}', 'ClientController@AceptSupplierProposalWinnerCost');
    Route::get('/supplier_proposal_rechazo/{id}', 'ClientController@SupplierProposalRechazo');
    // borrar
    // Route::get('/po_supplier_proposal_winner/{id}', 'ClientController@POSupplierProposalWinner');
    Route::get('/po_epno_cot_file/{id}', 'AgentController@POEpnoCotFile');
    Route::get('/request_supplier_proposal/{id}/{tipo}', 'SupplierController@RequestSupplierProposal');


    Route::post('/service_change_step', [GeneralController::class, 'ServiceChangeStep']);

    Route::post('/send_rate', [GeneralController::class, 'SendRate']);
    Route::get('/get_rate/{id}/{type}', 'GeneralController@GetRateById');
    Route::post('/get_conversation_messages', [GeneralController::class, 'GetConversationMessages']);
    Route::post('/send_order_comment', [GeneralController::class, 'SendOrderCommentById']);
    Route::get('/get_notifications/{flag}', [GeneralController::class, 'GetNotifications']);
    // Route::get('/get_notifications_total', 'GeneralController@GetNotificationsTotal');
    Route::get('/supplier_po_file/{id}', 'AgentController@SupplierPoFile');
    Route::post('/po_to_supplier', [AgentController::class, 'SendPoToSupplier']);
    Route::post('/send_invoice_file', [AgentController::class, 'SendInvoiceFile']);
    Route::post('/updown_user', [AgentController::class, 'UpdownUser']);
    Route::get('/get_all_users/{id}', [AgentController::class, 'Getallusers']);
    Route::post('/subir_client_cot_generada', [AgentController::class, 'SubirClientCotGenerada']);


    Route::get('/get_reviews', [GeneralController::class, 'GetReviews']);


    Route::get('/get_packages', [ClientController::class, 'GetPackage']);
    Route::post('/complaint_request', [ClientController::class, 'ComplaintRequest']);
    Route::get('/ganancias_client', [ClientController::class, 'Ganancias']);
    Route::get('/ganancias_resumen_client', [ClientController::class, 'GananciasResumen']);
    Route::get('/ganancias_supplier', 'SupplierController@Ganancias');
    Route::get('/ganancias_resumen_supplier', 'SupplierController@GananciasResumen');


    Route::get('/get_partnumbers_order/{role}/{id}/{tipo}', 'GeneralController@GetPartNumbersOrder');
    Route::get('/get_order_mro_log/{id}/{role}', 'GeneralController@GetOrderLogById');
    Route::get('/is_mro_req_ready/{id}', 'AgentController@isMroReqReady');
    Route::post('/change_mro_step_to_final', 'AgentController@ChangeMroStepToFinal');
    Route::get('/upload_po_agent_autorization/{id}', 'AgentController@UploadPoAutorization');
    // Route::get('/get_total', 'ClientController@GetTotal');
    Route::post('/up_client_mro_po', 'ClientController@UpClientMroPo');
    Route::get('/locations', 'SupplierController@showLocation');
    Route::post('/cot_manual_process', 'AgentController@ManualProcessCot');
    Route::post('/cot_add_more_suppliers', 'AgentController@CotAddMoreSuppliers');
    Route::post('/cot_automatic_process', 'AgentController@CotAutomaticProcess');
    Route::post('/subir_po_generada', [AgentController::class, 'SubirPOGenerada']);
    Route::post('/cancelar_cotizacion', 'GeneralController@CancelarCotizacion');
    Route::post('/send_product_comment', [ClientController::class, 'SendProductComment']);
    Route::get('/get_product_comments/{id}', [ClientController::class, 'GetProductComments']);
    Route::post('/send_product_answer', [SupplierController::class, 'SendProductAnswer']);
    Route::post('/marcar_como_leido', [GeneralController::class, 'MarkAsRead']);
});
