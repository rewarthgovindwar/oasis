<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportLanguageRequestForm;
use App\SmBackup;
use App\SmLanguage;
use App\Traits\UploadTheme;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;
use Throwable;
use ZipArchive;

class LanguageController extends Controller
{
    use UploadTheme;

    public function index(string $lang)
    {
        $files = $this->fileList($lang);

        return view('backEnd.systemSettings.language_export', ['files' => $files, 'lang' => $lang]);
    }

    public function export(Request $request)
    {
        if (config('app.app_sync')) {
            Toastr::error(trans('Prohibited in demo mode.'), trans('common.failed'));

            return redirect()->back();
        }
            if(empty($request->lang_files)) {
                Toastr::error(__('system_settings.Files Empty'), __('common.error'));

                return redirect()->back()->withInput();
            }

            $fileName = $this->fileName($request->lang, $request->lang_files);
            if (file_exists($fileName)) {
                return response()->download($fileName);
            }
    }

    public function fileList(string $lang): array
    {

        $allActiveModules = Module::allEnabled();
        $fileList = glob('resources/lang/'.$lang.'/*.php');
        $moduleLangFileList = [];
        foreach ($allActiveModules as $key => $module) {
            $moduleLangFileList[] = glob('Modules/'.$key.'/Resources/lang/'.$lang.'/*.php');
        }

        $moduleLangFileList = array_reduce($moduleLangFileList, function ($carry, $array): array {
            return array_merge($carry, $array);
        }, []);

        return array_merge($fileList, $moduleLangFileList);

    }

    public function fileName(string $lang, array $fileList = [], ?string $fileName = null): string
    {
        $zipArchive = new ZipArchive;
        $fileName = $fileName ?? $lang.'_language_file'.'.zip';
        $filePath = public_path($fileName);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if ($zipArchive->open($filePath, ZipArchive::CREATE) == true) {
            foreach ($fileList as $filepath) {
                $zipArchive->addFile(base_path($filepath), $filepath);
            }

            $zipArchive->close();
        }

        return $filePath;
    }

    public function importLang(string $lang)
    {
        $backuplangs = SmBackup::whereNotNull('lang_type')
            ->where('school_id', auth()->user()->school_id)->get();
        $language = SmLanguage::where('language_universal', $lang)->first();

        return view('backEnd.systemSettings.language_import', ['backuplangs' => $backuplangs, 'language' => $language]);
    }

    public function import(ImportLanguageRequestForm $importLanguageRequestForm)
    {
        if (config('app.app_sync')) {
            Toastr::error(trans('Prohibited in demo mode.'), trans('common.failed'));

            return redirect()->back();
        }

        ini_set('memory_limit', '-1');

            if ($importLanguageRequestForm->hasFile('language_file')) {
                $path = $importLanguageRequestForm->language_file->store('language_file');
                $importLanguageRequestForm->language_file->getClientOriginalName();
                $zipArchive = new ZipArchive;
                $res = $zipArchive->open(storage_path('app/'.$path));
                if ($res == true) {
                    $zipArchive->extractTo(storage_path('app/tempLangUpdate'));
                    $zipArchive->close();
                } else {
                    abort(500, 'Error! Could not open File');
                }

                $src = storage_path('app/tempLangUpdate');
                $dst = base_path('/');
                $this->recurse_copy($src, $dst);
            }

            if (storage_path('app/language_file')) {
                $this->delete_directory(storage_path('app/language_file'));
            }

            if (storage_path('app/tempLangUpdate')) {
                $this->delete_directory(storage_path('app/tempLangUpdate'));
            }

            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            Toastr::success('Language File updated', 'Success');

            return redirect()->back();

    }

    private function recurse_copy($src, $dst)
    {
        
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
    }
    public function backupLanguage(string $lang)
    {
        if (config('app.app_sync')) {
            Toastr::error(trans('Prohibited in demo mode.'), trans('common.failed'));

            return redirect()->back();
        }

        $backup = SmBackup::latest()->first();
        $id = $backup ? $backup->id : 1;
        $uuid = date('Y-m-d').'_'.$id;
        $fileList = $this->fileList($lang);
        $fileName = $lang.'_backup_language_file_'.$uuid.'.zip';
        $this->fileName($lang, $fileList, $fileName);
        $this->backupLanguageStore($lang, $fileName);
        Toastr::success(__('common.Operation successful'), __('common.success'));

        return redirect()->route('lang-file-import', $lang);

    }


    private function backupLanguageStore(string $lang, string $file_name): void
    {
        $smBackup = new SmBackup();
        $smBackup->file_name = $file_name;
        $smBackup->source_link = $file_name;
        $smBackup->active_status = 1;
        $smBackup->lang_type = $lang ?? 'en';
        $smBackup->academic_id = getAcademicId();
        $smBackup->created_by = auth()->user()->id;
        $smBackup->save();
    }
}
