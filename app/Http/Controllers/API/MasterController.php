<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Major;
use App\Models\Faculty;

class MasterController extends Controller
{
    public function showAvatarOptions(Request $request){
        $results = [];
        $entry = scandir(public_path('_avatar'));
        $counter = 0;
        foreach ($entry as $key => $value) {
            if ($value != "." && $value != "..") {
                $path = '_avatar/' . $value;
                $val = [
                    'id' => $counter = $counter + 1,
                    'avatar' => url($path)
                ];
                array_push($results, $val);
            }
        }
        return response()->json($results);
    }

    public function showTopics(Request $request){
        $topics = Topic::select('id', 'name', 'description', 'icon')->get();
        return response()->json($topics, 200);
    }

    public function showFaculty(Request $request){
        if($request->code){
            $major = Major::where('faculty_code', $request->code)->get();
            return response()->json($major);
        }
        $faculty = Faculty::get();
        return response()->json($faculty);
    }
}
