<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'settings' => $request->user()->settings()->firstOrCreate(),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $request->user()->settings()->updateOrCreate([], [
            ...$request->validated(),
            'hide_values' => $request->boolean('hide_values'),
            'confirm_deletion' => $request->boolean('confirm_deletion'),
        ]);

        return back()->with('success', 'Preferências atualizadas.');
    }

    public function toggleValues(Request $request): JsonResponse
    {
        $settings = $request->user()->settings()->firstOrCreate();
        $settings->update(['hide_values' => ! $settings->hide_values]);

        return response()->json([
            'hidden' => $settings->hide_values,
            'message' => $settings->hide_values ? 'Valores ocultados.' : 'Valores exibidos.',
        ]);
    }

    public function toggleTheme(Request $request): JsonResponse
    {
        $settings = $request->user()->settings()->firstOrCreate();
        $theme = $settings->theme === 'dark' ? 'light' : 'dark';
        $settings->update(['theme' => $theme]);

        return response()->json([
            'theme' => $theme,
            'message' => $theme === 'dark' ? 'Modo noturno ativado.' : 'Modo claro ativado.',
        ]);
    }
}
