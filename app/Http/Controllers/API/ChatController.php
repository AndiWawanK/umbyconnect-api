<?php

namespace App\Http\Controllers\API;
use Validator;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatroom;
use App\Models\User;
use Carbon\Carbon;
use Kreait\Firebase\Factory;

class ChatController extends Controller
{
    public function startChat(Request $request){
        $currentUser = $request->user();
        $user = User::where('id', $request->input('to'))->first();
        // $hasConversation = Chatroom::where('user_id', 144)->where('user_id', 143)->firstOrfail();
        // return response()->json($hasConversation);
        DB::beginTransaction();
        try{
            $create = Chatroom::insert([
                [
                    'user_id' => $currentUser->id, 
                    'room' => $request->input('room'), 
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                [
                    'user_id' => $request->input('to'), 
                    'room' => $request->input('room'),
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]
            ]);
            DB::commit();
            $firestore = app('firebase.firestore')->database();
            $firestore->collection('chat/'.$request->input('room').'/messages')->newDocument()->set([
                'message' => $request->input('message'),
                'thread_forward' => $request->input('thread_forward'),
                'link' => $request->input('link'),
                'is_read' => false,
                'sender_id' => $currentUser->id
            ]);
            $pushNotification = $this->sendNotification($user->fcm_token, $currentUser);
            return response()->json([
                'status' => true,
                'room' => $request->input('room'),
                'pushed' => json_decode($pushNotification)
            ]);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }

    public function sendNotification($fcmToken, $sender){
        $url = "https://fcm.googleapis.com/fcm/send";            
        $header = [
            'authorization: key=AAAAmcHmaWs:APA91bFuu5MQohefqIEE75-Z3L3xQ1yjKg0mMlSM2wzJ6TE698XfAk7prmukzqzkZM4AkLQWjQ8RxnCt2uQya4QPndlCZzUTi4Ggsmij-VN0XBfHFD7xPa3GFybL5b55P_qN3EScTIr4',
            'content-type: application/json'
        ];    
    
        $notification = [
            'title' => "Pesan baru",
            'body' => $sender->full_name .' memulai percakapan dengan kamu'
        ];
        $extraNotificationData = [
            "message" => $notification,
            "type" => 'chat',
        ];
    
        $fcmNotification = [
            'to'            => $fcmToken,
            'notification'  => $notification,
            'data'          => $extraNotificationData
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
        $result = curl_exec($ch);    
        curl_close($ch);
        
        return $result;
    }
}
