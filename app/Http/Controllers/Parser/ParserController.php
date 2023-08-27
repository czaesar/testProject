<?php

namespace App\Http\Controllers\Parser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parser\UploadParserRequest;

use App\Jobs\ParseFileJob;
use App\Models\Parser;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;


class ParserController extends Controller
{
    public function index()
    {
        $parsers = Parser::all()->groupBy('date');

        $rows = [];
        foreach ($parsers as $date => $data) {
            $row = [
                'date' => $date,
                'count' => count($data),
                'names' => $data->pluck('name')->toArray(),
            ];

            $rows[] = $row;
        }
        return response()->json($rows);
    }

    public function create(UploadParserRequest $request)
    {
        $filePath = $request->file('file')->path();
        ParseFileJob::dispatch($filePath);

        $redisKey = 'file_parsing_progress';
        $progress = Redis::hgetall($redisKey);

        return response()->json($progress, Response::HTTP_CREATED);
    }
}
