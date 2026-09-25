<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OfferwallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OfferwallController extends Controller
{
    public function launch(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_active, 403, 'Your account is inactive.');
        abort_unless(
            config('offerwall.enabled') && filled(config('offerwall.sdk_key'))
                && filled(config('offerwall.callback_secret'))
                && in_array(config('offerwall.callback_mode'), ['hmac_sha256', 'legacy_md5'], true),
            503,
            'Offerwall is currently unavailable. Please try again later.',
        );

        $url = 'https://rewards.unity.com/owp/web/link/'
            .rawurlencode(config('offerwall.sdk_key')).'/u/'.$request->user()->id
            .'?'.http_build_query(['event_name' => config('offerwall.placement')], '', '&', PHP_QUERY_RFC3986);

        return response()->json(['url' => $url])->header('Cache-Control', 'no-store');
    }

    public function callback(Request $request, OfferwallService $service): Response
    {
        $secret = config('offerwall.callback_secret');
        abort_unless(is_string($secret) && $secret !== '', 503);
        abort_if(strlen($request->getContent()) > 65536, 413);

        if (config('offerwall.callback_mode') === 'hmac_sha256') {
            abort_unless($request->isMethod('POST'), 403);
            $signature = $request->header('X-Tapjoy-Signature', '');
            abort_unless(hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature), 403);
            $data = json_decode($request->getContent(), true);
            abort_unless(is_array($data), 403);
            $validator = Validator::make($data, [
                'id' => ['required', 'string', 'max:191'],
                'user.id' => ['required', 'string', 'regex:/^[1-9][0-9]{0,18}$/D'],
                'timestamp' => ['required', 'integer', 'min:1', 'max:'.min(now()->addMinutes(5)->timestamp, 2147483647)],
                'offer.name' => ['nullable', 'string', 'max:255'],
                'offer.advertiser_app_name' => ['nullable', 'string', 'max:255'],
                'offer.task.name' => ['nullable', 'string', 'max:255'],
                'placement.name' => ['nullable', 'string', 'max:255'],
            ]);
            abort_if($validator->fails(), 403);
            $data = $validator->validated();
            $userId = $data['user']['id'];
            $transactionId = $data['id'];
            $offerName = $data['offer']['name'] ?? null;
            $completedAt = Carbon::createFromTimestamp($data['timestamp'])->setTimezone(config('app.timezone'));
            $payload = $data;
        } elseif (config('offerwall.callback_mode') === 'legacy_md5') {
            abort_unless($request->isMethod('GET'), 403);
            parse_str($request->server('QUERY_STRING', ''), $data);
            $validator = Validator::make($data, [
                'id' => ['required', 'string', 'max:191', 'not_regex:/:/'],
                'snuid' => ['required', 'string', 'regex:/^[1-9][0-9]{0,18}$/D'],
                'currency' => ['required', 'string', 'max:40', 'regex:/^[0-9]+(?:\.[0-9]+)?$/D'],
                'verifier' => ['required', 'string', 'regex:/^[a-f0-9]{32}$/D'],
            ]);
            abort_if($validator->fails(), 403);
            $data = $validator->validated();
            $expected = md5($data['id'].':'.$data['snuid'].':'.$data['currency'].':'.$secret);
            abort_unless(hash_equals($expected, $data['verifier']), 403);
            $userId = $data['snuid'];
            $transactionId = $data['id'];
            $offerName = null;
            $completedAt = now();
            $payload = ['id' => $transactionId, 'snuid' => $userId, 'time_source' => 'received_at'];
        } else {
            abort(503);
        }

        $user = User::find($userId);
        abort_unless($user, 403);

        try {
            $service->recordCompletion(
                $user, 'unity', $transactionId,
                offerName: $offerName, completedAt: $completedAt, payload: $payload,
            );
        } catch (ValidationException) {
            abort(403);
        }

        return response('OK', 200)->header('Cache-Control', 'no-store');
    }
}
