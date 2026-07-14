<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessSettingRequest;
use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSettingController extends Controller
{
    public function edit(): Response
    {
        $setting = BusinessSetting::current();
        $this->authorize('view', $setting);

        return Inertia::render('Admin/Settings/Index', ['setting' => $setting]);
    }

    public function update(UpdateBusinessSettingRequest $request): RedirectResponse
    {
        $setting = BusinessSetting::current();
        $this->authorize('update', $setting);
        $setting->update($request->validated());

        activity()->performedOn($setting)->log('Business settings updated.');

        return back()->with('success', 'Business settings updated.');
    }
}
