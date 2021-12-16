<?php

namespace App\Http\Controllers;

use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class ScraperController extends Controller
{
    private $result = array();
    public function scraper()
    {
        // fetching for indo-pak script of ayah
        $Chapters = DB::table('ic_quranic_suras_meta')
            ->select('id', 'sindex', 'ayas', 'start', 'name', 'tname', 'ename', 'type', 'sorder', 'rukus', 'audiofile',)
            ->get();

        foreach ($Chapters as $chapter) {
            $verses = DB::table('ic_quranic_text-quran-simple')
                ->where('sura', $chapter->id)
                ->select('id', 'sura', 'sura_ayat_id', 'arabic_text')
                ->get();
            foreach ($verses as $verse) {
                $client = new Client();
                $url = "https://dailyayat.com/$chapter->tname/$chapter->id/$verse->id";
                $page = $client->request('GET', $url);
                // echo "<pre>";
                // echo ($page->filter('.TextAyah')->text())." <br>  ";
                // echo ($page->filter('.TextResultEnglish')->text())." <br>  ";
                // $text['verse_key'] = "$chapter->id:$verse->id";
                // $text['school'] = "Essential Tafsir";
                // $text['tafsir'] = "Tafsir al-Jalalayn";
                // $text['locale'] = "en";
                $text = $page->filter('.indo1')->text();
               $text=  preg_replace('/﴾/i', '', $text);
               $text=  preg_replace('/﴿/i', '$', $text);
               $text=  preg_replace('/٪/i', '', $text);
              echo  $text. " <br> <br> <br>";
                // $text['tafsir_text'] = $page->filter('.TextResultEnglish')->text();
                // DB::table('tafsirs')->insert($text);
            }
            return (1);
        }
       
        // $client = new Client();
        // $url = 'https://dailyayat.com/al-fatiha/1/2';
        // $page = $client->request('GET', $url);
        // // echo "<pre>";
        // echo ($page->filter('#tafaseerResult')->text());
    }
    
    public function pdfReader(){
        // $parser = new \Samlot\pdfParser\Parser();
        $parser = new Parser();
        $filepath = 'Al-Ruqya.pdf';
       $pdf =  $parser->parseFile($filepath);
       $text = $pdf->getText();
       $pdfText = nl2br($text);
        echo $pdfText;
    }
}
