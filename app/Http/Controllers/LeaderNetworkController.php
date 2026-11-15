<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderNetworkController extends Controller
{
    public function show(Request $request, $leaderId)
    {
        $leader = DB::table('leaders')->where('leader_id', $leaderId)->first();

        if (!$leader) {
            abort(404, 'Leader not found.');
        }

        $minScore = max(0, (int) $request->query('min_score', 4));

        // Each CSV row is one undirected connection. Find the selected leader
        // whether they appear in endpoint 1 or endpoint 2.
        $relationships = DB::table('leader_relationships')
            ->where(function ($query) use ($leaderId) {
                $query->where('leader_id_1', $leaderId)
                    ->orWhere('leader_id_2', $leaderId);
            })
            ->where('relationship_score', '>=', $minScore)
            ->orderByDesc('relationship_score')
            ->get();

        $neighborIds = [];

        foreach ($relationships as $relationship) {
            $neighborIds[] = $relationship->leader_id_1 === $leaderId
                ? $relationship->leader_id_2
                : $relationship->leader_id_1;
        }

        $neighborIds = array_values(array_unique($neighborIds));
        $neighborLeaders = empty($neighborIds)
            ? collect()
            : DB::table('leaders')->whereIn('leader_id', $neighborIds)->get()->keyBy('leader_id');

        // The supplied dataset's global maximum score is 21. Keep this fixed
        // so a score of 13 has the same visual size across different leaders.
        $scoreScaleMaximum = 21;

        $elements = [
            'nodes' => [],
            'edges' => [],
        ];

        $elements['nodes'][] = [
            'data' => [
                'id' => $leader->leader_id,
                'name' => $leader->english_name ?: $leader->leader_id,
                'chineseName' => $leader->chinese_name,
                'position' => $leader->current_position,
                'rank' => $leader->rank,
                'isCentral' => true,
                'size' => 62,
            ],
        ];

        foreach ($relationships as $relationship) {
            $neighborId = $relationship->leader_id_1 === $leaderId
                ? $relationship->leader_id_2
                : $relationship->leader_id_1;

            $neighbor = $neighborLeaders->get($neighborId);

            $name = $neighbor
                ? ($neighbor->english_name ?: $neighborId)
                : ($relationship->leader_id_1 === $neighborId
                    ? ($relationship->leader_1_name ?: $neighborId)
                    : ($relationship->leader_2_name ?: $neighborId));

            $chineseName = $neighbor ? $neighbor->chinese_name : null;
            $position = $neighbor ? $neighbor->current_position : null;
            $rank = $neighbor ? $neighbor->rank : null;
            $score = (int) $relationship->relationship_score;

            $elements['nodes'][] = [
                'data' => [
                    'id' => $neighborId,
                    'name' => $name,
                    'chineseName' => $chineseName,
                    'position' => $position,
                    'rank' => $rank,
                    'isCentral' => false,
                    // Visual scale only; source relationship_score is unchanged.
                    'size' => 22 + (($score / $scoreScaleMaximum) * 30),
                ],
            ];

            $elements['edges'][] = [
                'data' => [
                    'id' => 'edge-' . $relationship->id,
                    'source' => $leaderId,
                    'target' => $neighborId,
                    'score' => $score,
                    'width' => 1.5 + (($score / $scoreScaleMaximum) * 7),
                    'relationshipTypes' => $relationship->relationship_types,
                    'totalEvidence' => (int) $relationship->total_evidence_records,
                    'careerEvidence' => (int) $relationship->career_evidence_records,
                    'educationEvidence' => (int) $relationship->education_evidence_records,
                    'evidenceDetails' => $relationship->evidence_details,
                ],
            ];
        }

        // Remove any accidental duplicate nodes while preserving their first entry.
        $uniqueNodes = [];
        foreach ($elements['nodes'] as $node) {
            $uniqueNodes[$node['data']['id']] = $node;
        }
        $elements['nodes'] = array_values($uniqueNodes);

        return view('leaders.network', [
            'leader' => $leader,
            'elements' => $elements,
            'minScore' => $minScore,
            'connectionCount' => count($elements['edges']),
        ]);
    }
}
