<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\WhatsappConfigRequest;
use App\Services\WhatsappConfigService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WhatsappConfigController extends Controller
{
    public function __construct(protected WhatsappConfigService $service)
    {
        $this->middleware('permission:'.Permission::VIEW_WHATSAPP_CONFIG->value)->only(['index']);
        $this->middleware('permission:'.Permission::MANAGE_WHATSAPP_CONFIG->value)->only(['update', 'destroy', 'test', 'getQr']);
    }

    public function index()
    {
        $config = $this->service->getConfig();

        return ApiResponse::success('Configuration retrieved successfully.', $config);
    }

    public function update(WhatsappConfigRequest $request)
    {
        $config = $this->service->saveConfig($request->validated());

        return ApiResponse::success('Configuration saved successfully.', $config);
    }

    public function destroy()
    {
        $this->service->resetConfig();

        return ApiResponse::success('Configuration reset successfully.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'target' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $result = $this->service->sendTestMessage(
            $request->target,
            $request->message ?? 'This is a test message from Asset Management System.'
        );

        return ApiResponse::success('Test message sent successfully.', $result);
    }

    public function getQr()
    {
        $data = $this->service->getQr();

        return ApiResponse::success('QR Code retrieved successfully.', $data);
    }

    public function check()
    {
        $result = $this->service->checkConnection();

        return ApiResponse::success('Connection status checked successfully.', $result);
    }
}
