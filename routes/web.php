<?php

use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'pages::dropzone.index')->name('dashboard');
    Route::livewire('/project/{project}', 'pages::project.index')->name('project');
    Route::livewire('/project/{project}/workbench', 'pages::workbench.index')->name('workbench');
    Route::livewire('/project/{project}/blueprint', 'pages::project.blueprint')->name('blueprint');
    Route::livewire('/project/{project}/shelf/{shelf}', 'pages::project.shelf')->name('shelf');
});

require __DIR__.'/settings.php';
