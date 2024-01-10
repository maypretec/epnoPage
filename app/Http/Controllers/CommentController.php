<?php

namespace App\Http\Controllers;
#region Models
use App\Models\ServiceComment;
#endregion

#region Helpers
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
#endregion

class CommentController extends Controller
{
   /**
    * Summary: All coments by user
    *
    * Function that calls for all comments created by given user.
    *
    * @param Request $request id for searched user id
    * @return Response Dictionary with all the comments made by the specified user
    * @throws conditon
    **/
   public function UserComments($request)
   {
    if (!Auth::check()) {
        return response('Session not found', 401);
    }
        $commentsService=ServiceComment::where('user_id',$request)->with('user_info')->with('service')->get();
        $commentList=[];
        foreach ($commentsService as $comment) {
            $commentData = [
                'comment' => $comment->comment,
                'created_at' => $comment->created_at,
                'order_num' => $comment->service->order_num,
                'user_name' => $comment->user_info->user_name,
                'org_name' => $comment->user_info->org_name,
            ];
            $commentList[] = $commentData;
        }
        
        return response()->json($commentList);
   } 
}
