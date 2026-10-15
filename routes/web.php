<?php

use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'pages::dropzone')->name('dashboard');
    Route::livewire('/project/{project}', 'pages::project')->name('project');
    Route::livewire('/project/{project}/workbench', 'pages::workbench')->name('workbench');
    Route::livewire('/project/{project}/shelf/{shelf}', 'pages::project.shelf')->name('shelf');
});

require __DIR__.'/settings.php';
