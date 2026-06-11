<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Seooptimization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url as SitemapUrl;

class SeooptimizationController extends Controller
{
    public function maanSeooptimzationIndex()
    {
        $seooptimizations = Seooptimization::all();
        return view('admin.pages.seo.index',compact('seooptimizations'));
    }

    public function maanSeooptimzationStore(Request $request)
    {
        $request->validate([
            'keywords'=>'required',
            'author'=>'required',
            'meta_title'=>'required',
            'meta_description'=>'required',

        ]);
        if (Seooptimization::exists()){
            $seopotimizations               = Seooptimization::first() ;
        }else{
            $seopotimizations               = new Seooptimization();
        }

        $seopotimizations->keywords         = $request->keywords;
        $seopotimizations->author           = $request->author;
        $seopotimizations->meta_title       = $request->meta_title;
        $seopotimizations->meta_description = $request->meta_description;
        $seopotimizations->google_analytics = $request->google_analytics;
        $seopotimizations->save();
        //session message
        $this->setSuccess('Inserted');
        //redirect route
        return redirect()->route('admin.seo.index') ;
    }

    public function maanSeooptimzationUpdate(Request $request,Seooptimization $seooptimization)
    {

        $request->validate([
            'keywords'=>'required',
            'author'=>'required',
            'meta_title'=>'required',
            'meta_description'=>'required',

        ]);

        $seooptimization->keywords = $request->keywords;
        $seooptimization->author = $request->author;
        $seooptimization->meta_title = $request->meta_title;
        $seooptimization->meta_description = $request->meta_description;
        $seooptimization->google_analytics = $request->google_analytics;
        $seooptimization->save();

        //session message
        $this->setSuccess('Updated');

        return redirect()->route('admin.seo.index');

    }

    public function maanSeooptimzationSitemape()
    {
        $sitemap = Sitemap::create();

        $latestnews = News::join('newssubcategories','news.subcategory_id','=','newssubcategories.id')
            ->join('newscategories','newssubcategories.category_id','=','newscategories.id')
            ->select('news.id','news.title','news.date','news.created_at','newscategories.name as news_category')
            ->latest()
            ->get();
        foreach ($latestnews as $news){
            $sitemap->add(
                SitemapUrl::create(URL::to(strtolower($news->news_category)))
                    ->setLastModificationDate($news->created_at)
                    ->setChangeFrequency(SitemapUrl::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0)
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->setSuccess('Generated');
        return redirect()->route('admin.seo.index');
    }
}
