<?php



Route::get('/storage/{FolderName}/{filename}', function ($FolderName,$filename)
{
    $path = storage_path("app/".$FolderName."/" . $filename);

    if (!File::exists($path)) {
        return  $path;
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->name('storage');
