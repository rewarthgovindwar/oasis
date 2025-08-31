<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\SmCourse;
use App\SmCourseCategory;
use App\SmHeaderMenuManager;
use App\SmNews;
use App\SmNewsCategory;
use App\SmPage;
use Exception;
use Illuminate\Http\Request;

class SmHeaderMenuManagerController extends Controller
{
    public function index()
    {
        /*
        try {
        */
            if (activeTheme() !== 'edulia') {
                $pages = SmPage::where('school_id', app('school')->id)->where('is_dynamic', 1)->get();
                $static_pages = SmPage::where('school_id', app('school')->id)->where('is_dynamic', 0)->get();
                $courses = SmCourse::where('school_id', app('school')->id)->get();
                $courseCategories = SmCourseCategory::where('school_id', app('school')->id)->get();
                $news = SmNews::where('school_id', app('school')->id)->where('mark_as_archive', 0)->get();
                $news_categories = SmNewsCategory::where('school_id', app('school')->id)->get();
                $menus = SmHeaderMenuManager::with('childs')->where('school_id', app('school')->id)->where('theme', 'default')->where('parent_id', null)->orderBy('position')->get();

                return view('backEnd.frontSettings.headerMenuManager', ['pages' => $pages, 'static_pages' => $static_pages, 'courses' => $courses, 'courseCategories' => $courseCategories, 'news_categories' => $news_categories, 'news' => $news, 'menus' => $menus]);
            }
        $themeBasedMenuManagerController = new ThemeBasedMenuManagerController();

        return $themeBasedMenuManagerController->index();

        /*
        } catch (\Exception $e) {
            return response('error');
        }
        */
    }

    public function store(Request $request)
    {
/*
        try {
        */
            if (activeTheme() !== 'edulia') {
                if ($request->type == 'dPages') {
                    foreach ($request->element_id as $data) {
                        $dpage = SmPage::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $dpage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'link' => $dpage->slug,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'sPages') {
                    foreach ($request->element_id as $data) {
                        $spage = SmPage::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $spage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'link' => $spage->slug,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'dCourse') {
                    foreach ($request->element_id as $data) {
                        $spage = SmCourse::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $spage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'dCourseCategory') {
                    foreach ($request->element_id as $data) {
                        $spage = SmCourseCategory::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $spage->category_name,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'dNews') {
                    foreach ($request->element_id as $data) {
                        $dNews = SmNews::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $dNews->news_title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'dNewsCategory') {
                    foreach ($request->element_id as $data) {
                        $dNewsCategory = SmNewsCategory::findOrFail($data);
                        SmHeaderMenuManager::create([
                            'title' => $dNewsCategory->category_name,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == 'customLink') {
                    SmHeaderMenuManager::create([
                        'title' => $request->title,
                        'link' => $request->link,
                        'type' => $request->type,
                        'position' => 387437,
                        'theme' => 'default',
                        'school_id' => app('school')->id,
                    ]);
                }
            } else {
                $themeBasedMenuManagerController = new ThemeBasedMenuManagerController();
                $themeBasedMenuManagerController->store($request);
            }

            return $this->reloadWithData();
        /*
        } catch (Exception $exception) {
            return response('error');
        }
        */
    }

    public function update(Request $request)
    {
/*
        try {
        */
            if (activeTheme() !== 'edulia') {
                if ($request->type == 'dPages') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->page,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'sPages') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->static_pages,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'dCourse') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->course,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'dCourseCategory') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->course_category,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'dNews') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->news,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'dNewsCategory') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->news_category,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == 'customLink') {
                    SmHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'link' => $request->link,
                        'type' => $request->type,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                }
            } else {
                $themeBasedMenuManagerController = new ThemeBasedMenuManagerController();
                $themeBasedMenuManagerController->update($request);
            }

            return $this->reloadWithData();
        /*
        } catch (Exception $exception) {
            return response('error');
        }
        */
    }

    public function delete(Request $request)
    {
        /*
        try {
        */
            $element = SmHeaderMenuManager::find($request->id);
            if (count($element->childs) > 0) {
                foreach ($element->childs as $child) {
                    $child->update(['parent_id' => $element->parent_id]);
                }
            }

            $element->delete();

            return $this->reloadWithData();
        /*
        } catch (Exception $exception) {
            return response('error');
        }
        */
    }

    public function reordering(Request $request): bool
    {
        $menuItemOrder = json_decode($request->get('order'));
        $this->orderMenu($menuItemOrder, null);

        return true;
    }

    private function orderMenu(array $menuItems, $parentId): void
    {
        foreach ($menuItems as $index => $item) {

            $menuItem = SmHeaderMenuManager::findOrFail($item->id);
            $menuItem->update([
                'position' => $index + 1,
                'parent_id' => $parentId,
            ]);
            if (isset($item->children)) {
                $this->orderMenu($item->children, $menuItem->id);
            }
        }
    }

    private function reloadWithData()
    {
        if (activeTheme() !== 'edulia') {
            $pages = SmPage::where('is_dynamic', 1)->where('school_id', app('school')->id)->get();
            $static_pages = SmPage::where('is_dynamic', 0)->where('school_id', app('school')->id)->get();
            $courses = SmCourse::where('school_id', app('school')->id)->get();
            $courseCategories = SmCourseCategory::where('school_id', app('school')->id)->get();
            $news = SmNews::where('school_id', app('school')->id)->where('mark_as_archive', 0)->get();
            $news_categories = SmNewsCategory::where('school_id', app('school')->id)->get();
            $menus = SmHeaderMenuManager::with('childs')->where('parent_id', null)->where('school_id', app('school')->id)->where('theme', 'default')->orderBy('position')->get();

            return view('backEnd.frontSettings.headerSubmenuList', ['pages' => $pages, 'static_pages' => $static_pages, 'courses' => $courses, 'courseCategories' => $courseCategories, 'news_categories' => $news_categories, 'news' => $news, 'menus' => $menus]);
        }

        $themeBasedMenuManagerController = new ThemeBasedMenuManagerController();

        return $themeBasedMenuManagerController->renderData();

    }
}
