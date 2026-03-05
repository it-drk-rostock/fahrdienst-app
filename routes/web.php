<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\MaintenanceTemplateController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\HuReportController;
use App\Http\Controllers\DamageController; // <--- WICHTIG: Hat gefehlt!

Route::get('/', function () {
    return view('welcome');
});

Route::get('/Witt', function () {
    // Wenn der Nutzer schon eingeloggt ist -> Dashboard
    if (auth()->check()) {
        return redirect()->route('calendar.index'); // Oder 'dashboard', je nach deiner Route
    }
    // Wenn nicht eingeloggt -> Login
    return redirect()->route('Log_FMS_in');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [VehicleController::class, 'index'])->name('dashboard');

    // --- PROFIL ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- FAHRZEUGE (CRUD) ---
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::patch('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::post('/vehicles/{vehicle}/toggle-status', [VehicleController::class, 'toggleStatus'])->name('vehicles.toggle-status');

    // --- CHECKLISTEN & AUDITS (REIFEN) ---
    // Diese Route fehlte für das Reifen-Modal:
    Route::post('/vehicles/{vehicle}/audit', [VehicleController::class, 'storeAudit'])->name('vehicles.audit.store');

    // --- SCHADENSMANAGEMENT ---
    // 1. Neuen Mangel anlegen (aus der Akte)
    Route::post('/vehicles/{vehicle}/damage', [VehicleController::class, 'storeDamage'])->name('vehicles.damage.store');

    // 2. Bestehenden Mangel bearbeiten & Bilder hochladen (FEHLTE)
    Route::put('/damages/{damage}', [DamageController::class, 'update'])->name('damages.update');

    // 3. Einzelnes Bild löschen (FEHLTE)
    Route::delete('/damages/{damage}/image/{index}', [DamageController::class, 'destroyImage'])->name('damages.image.destroy');

    // 4. Mangel komplett löschen (FEHLTE)
    Route::delete('/damages/{damage}', [DamageController::class, 'destroy'])->name('damages.destroy');

    // --- HU BERICHTE ---
    //Route::post('/vehicles/{vehicle}/hu', [HuReportController::class, 'store'])->name('hu-reports.store');
    Route::delete('/hu-reports/{huReport}', [HuReportController::class, 'destroy'])->name('hu-reports.destroy');
    Route::post('/vehicles/{vehicle}/hu', [App\Http\Controllers\VehicleController::class, 'storeHu'])->name('vehicles.hu.store');
    Route::put('/vehicles/{vehicle}/hu/{huReport}', [VehicleController::class, 'updateHu'])->name('vehicles.hu.update');
    // --- WARTUNGSPLÄNE ---
    Route::get('/maintenance-templates', [MaintenanceTemplateController::class, 'index'])->name('maintenance-templates.index');
    Route::post('/maintenance-templates', [MaintenanceTemplateController::class, 'store'])->name('maintenance-templates.store');
    Route::post('/maintenance-templates/{template}/confirm', [MaintenanceTemplateController::class, 'confirm'])->name('maintenance-templates.confirm');

    // --- WERKSTATT & DISPO ---
    Route::get('/workshop/dispatch', [WorkshopController::class, 'index'])->name('workshop.dispatch');
    Route::post('/workshop/dispatch', [WorkshopController::class, 'store'])->name('workshop.store');

    // Update & Löschen von Terminen
    Route::put('/workshop-appointments/{id}', [WorkshopController::class, 'update'])->name('workshop.update');
    Route::delete('/workshop-appointments/{id}', [WorkshopController::class, 'destroy'])->name('workshop.destroy');

    // --- KALENDER ---
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    // Organisation / Stammdaten
  Route::get('/organization', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organization.index');

  // Areas
  Route::post('/organization/area', [App\Http\Controllers\OrganizationController::class, 'storeArea'])->name('organization.area.store');
  Route::put('/organization/area/{area}', [App\Http\Controllers\OrganizationController::class, 'updateArea'])->name('organization.area.update'); // NEU
  Route::delete('/organization/area/{area}', [App\Http\Controllers\OrganizationController::class, 'destroyArea'])->name('organization.area.delete');

  // Cost Centers
  Route::post('/organization/costcenter', [App\Http\Controllers\OrganizationController::class, 'storeCostCenter'])->name('organization.costcenter.store');
  Route::put('/organization/costcenter/{costCenter}', [App\Http\Controllers\OrganizationController::class, 'updateCostCenter'])->name('organization.costcenter.update'); // NEU
  Route::delete('/organization/costcenter/{costCenter}', [App\Http\Controllers\OrganizationController::class, 'destroyCostCenter'])->name('organization.costcenter.delete');

  // Drag & Drop Route: Termin verschieben
  Route::post('/calendar/move', [CalendarController::class, 'moveAppointment'])->name('calendar.move');
});

require __DIR__.'/auth.php';
