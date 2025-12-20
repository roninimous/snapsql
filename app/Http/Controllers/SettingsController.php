<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Get validated data and exclude active_tab (it's only for UI state)
        $validated = $request->validated();
        unset($validated['active_tab']);

        $user->update($validated);

        return back()
            ->with('success', 'Settings updated successfully.')
            ->withInput(['active_tab' => $request->input('active_tab', 'timezone')]);
    }
}
