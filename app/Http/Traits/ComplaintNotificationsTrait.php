<?php
namespace App\Http\Traits;

use App\Models\Notification;
use App\Models\Student;
use App\Notifications\ComplaintDetailsNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as Notify;
trait ComplaintNotificationsTrait {
    public function callNotification($user_id,$ntf_type,$complaint_id,$user_email,$role,$complaint_num,$service_title,
    $ul_name,$ul_phone,$ul_email,$ul_org
    ) {
        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->notification_type_id = $ntf_type;
        $notification->table_name = "complaints";
        $notification->table_id = $complaint_id;
        if ($notification->save()) {
            DB::select('call limitNotificationCount (?)', array($user_id));
        }

        Notify::route('mail', $user_email)
            ->notify(new ComplaintDetailsNotification(
                $ntf_type,
                $role,
                $complaint_id,
                $complaint_num,
                $service_title,
                $ul_name,
                $ul_phone,
                $ul_email,
                $ul_org,
            ));
    }
}