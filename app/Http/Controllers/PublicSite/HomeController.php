<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'leaders' => DB::table('leaders')->count(),
            'career_records' => DB::table('career_history')->count(),
            'education_records' => DB::table('education_training')->count(),
            'provinces' => DB::table('career_history')->whereNotNull('province')->where('province', '<>', '')->distinct('province')->count('province'),
        ];

        $featured = DB::table('leaders')
            ->select('leader_id', 'english_name', 'chinese_name', 'current_position', 'rank', 'province_of_employment')
            ->whereNotNull('english_name')
            ->orderBy('english_name')
            ->limit(8)
            ->get();

        $recentCareer = DB::table('career_history as c')
            ->join('leaders as l', 'l.leader_id', '=', 'c.leader_id')
            ->select('l.leader_id', 'l.english_name', 'l.chinese_name', 'c.position', 'c.department', 'c.province', 'c.start_year', 'c.end_year')
            ->orderByDesc('c.end_year')
            ->limit(6)
            ->get();

        return view('public.home', compact('stats', 'featured', 'recentCareer'));
    }
}
