<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Goutte\Client;

class FetchData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:tafsir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $Chapters = DB::table('ic_quranic_suras_meta')
            ->select('id', 'sindex', 'ayas', 'start', 'name', 'tname', 'ename', 'type', 'sorder', 'rukus', 'audiofile',)
            ->get();

        foreach ($Chapters as $chapter) {
            $verses = DB::table('ic_quranic_text-quran-simple')
                ->where('sura', $chapter->id)
                ->select('id', 'sura', 'sura_ayat_id', 'arabic_text')
                ->get();
            echo $this->info("starting from Chapter $chapter->name");
            echo $this->info("tSoraNo=  $chapter->id");
            foreach ($verses as $verse) {
                echo $this->info("Verse  $verse->arabic_text");

                $client = new Client();
                $url = "https://www.altafsir.com/Tafasir.asp?tMadhNo=1&tTafsirNo=74&tSoraNo=" . $chapter->id . "&tAyahNo=$verse->sura_ayat_id&tDisplay=yes&UserProfile=0&LanguageId=2";
                $page = $client->request('GET', $url);
                echo $this->info("tAyahNo= $verse->sura_ayat_id");
                // echo "<pre>";
                // echo ($page->filter('.TextAyah')->text())." <br>  ";
                // echo ($page->filter('.TextResultEnglish')->text())." <br>  ";
                $text['verse_key'] = "$chapter->id:$verse->sura_ayat_id";
                $text['school'] = "Essential Tafsir";
                $text['tafsir'] = "Tafsir al-Jalalayn";
                $text['locale'] = "en";
                $text['ayyah_text'] = $page->filter('.TextAyah')->text();
                $text['tafsir_text'] = $page->filter('.TextResultEnglish')->text();
                $check = DB::table('tafsirs')->where('verse_key', $text['verse_key'])->where('tafsir', $text['tafsir'])->where('school', $text['school'])->where('locale', $text['locale'])->first();
                if (!$check) {
                    DB::table('tafsirs')->insert($text);
                    echo $this->info("verse key $chapter->id:$verse->sura_ayat_id tafsir inserted");
                } else
                    echo $this->info("verse no $chapter->id:$verse->sura_ayat_id tafsir already exists");
            }

            // return $this->result;
        }

        // $client = new Client();
        // $url = 'https://www.englishtafsir.com/Quran/114/index.html';
        // $page = $client->request('GET', $url);
        // // echo "<pre>";
        // echo ($page->filter('.sdfootnote-western')->text());
    }
}
