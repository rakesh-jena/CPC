<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('leaders')
            ->select(
                'leader_id', 'english_name', 'chinese_name', 'gender', 'dob', 'age',
                'birth_province', 'current_position', 'rank', 'province_of_employment',
                'status', 'ethnicity'
            );

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
                $q->where('english_name', 'ILIKE', $like)
                    ->orWhere('chinese_name', 'ILIKE', $like)
                    ->orWhere('leader_id', 'ILIKE', $like)
                    ->orWhere('current_position', 'ILIKE', $like)
                    ->orWhere('rank', 'ILIKE', $like)
                    ->orWhere('province_of_employment', 'ILIKE', $like)
                    ->orWhere('birth_province', 'ILIKE', $like)
                    ->orWhereExists(function ($career) use ($like) {
                        $career->select(DB::raw(1))->from('career_history as ch')
                            ->whereColumn('ch.leader_id', 'leaders.leader_id')
                            ->where(function ($c) use ($like) {
                                $c->where('ch.position', 'ILIKE', $like)
                                  ->orWhere('ch.department', 'ILIKE', $like)
                                  ->orWhere('ch.province', 'ILIKE', $like);
                            });
                    })
                    ->orWhereExists(function ($education) use ($like) {
                        $education->select(DB::raw(1))->from('education_training as et')
                            ->whereColumn('et.leader_id', 'leaders.leader_id')
                            ->where(function ($e) use ($like) {
                                $e->where('et.institution', 'ILIKE', $like)
                                  ->orWhere('et.degree', 'ILIKE', $like)
                                  ->orWhere('et.major', 'ILIKE', $like);
                            });
                    });
            });
        }

        foreach ([
            'rank' => 'rank',
            'position' => 'current_position',
            'posting' => 'province_of_employment',
            'birth' => 'birth_province',
            'gender' => 'gender',
        ] as $input => $column) {
            if ($request->filled($input)) {
                $query->where($column, $request->input($input));
            }
        }

        if ($request->filled('age_min')) $query->where('age', '>=', (int) $request->input('age_min'));
        if ($request->filled('age_max')) $query->where('age', '<=', (int) $request->input('age_max'));

        $sort = $request->input('sort', 'name');
        if ($sort === 'age_asc') $query->orderByRaw('age ASC NULLS LAST');
        elseif ($sort === 'age_desc') $query->orderByRaw('age DESC NULLS LAST');
        elseif ($sort === 'rank') $query->orderBy('rank')->orderBy('english_name');
        else $query->orderByRaw('english_name ASC NULLS LAST');

        $leaders = $query->paginate(24)->withQueryString();

        $filters = [
            'ranks' => DB::table('leaders')->whereNotNull('rank')->where('rank', '<>', '')->distinct()->orderBy('rank')->pluck('rank'),
            'positions' => DB::table('leaders')->whereNotNull('current_position')->where('current_position', '<>', '')->distinct()->orderBy('current_position')->pluck('current_position'),
            'postingProvinces' => DB::table('leaders')->whereNotNull('province_of_employment')->where('province_of_employment', '<>', '')->distinct()->orderBy('province_of_employment')->pluck('province_of_employment'),
            'birthProvinces' => DB::table('leaders')->whereNotNull('birth_province')->where('birth_province', '<>', '')->distinct()->orderBy('birth_province')->pluck('birth_province'),
            'genders' => DB::table('leaders')->whereNotNull('gender')->where('gender', '<>', '')->distinct()->orderBy('gender')->pluck('gender'),
        ];

        return view('public.leaders.index', compact('leaders', 'filters'));
    }

    public function show($leader)
    {
        $profile = DB::table('leaders')->where('leader_id', $leader)->first();
        abort_unless($profile, 404);

        $career = DB::table('career_history')
            ->where('leader_id', $leader)
            ->orderByRaw('start_year ASC NULLS LAST')
            ->orderBy('seq')
            ->get();

        $education = DB::table('education_training')
            ->where('leader_id', $leader)
            ->orderByRaw('start_year ASC NULLS LAST')
            ->orderBy('education_id')
            ->get();

        $network = DB::table('political_networks')->where('leader_id', $leader)->first();
        $inferred = DB::table('inferred_data')->where('leader_id', $leader)->first();

        $careerStats = [
            'appointments' => $career->count(),
            'provinces' => $career->pluck('province')->filter()->unique()->count(),
            'departments' => $career->pluck('department')->filter()->unique()->count(),
        ];

        return view('public.leaders.show', compact('profile', 'career', 'education', 'network', 'inferred', 'careerStats'));
    }
}
