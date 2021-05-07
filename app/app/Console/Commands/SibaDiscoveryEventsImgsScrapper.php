<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sientifica\Curl;

class SibaDiscoveryEventsImgsScrapper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siba:discovery-events-img-scrapper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando recupera desde el sitio web de discovery, las imagenes para los eventos que están emitiendo los canales de la cadena';

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
     * @return mixed
     */
    public function handle()
    {
        //
        //$curl = new Curl;
        //$response = $curl->urlGet("https://tudiscovery.com/api/shows?langcode=es-CO");
        //$response = $curl->urlGet("https://tudiscovery.com/shows");
        $dbFileName = storage_path('siba/discovery-shows.json');
        $fpDb = fopen($dbFileName,"r");
        $dbRawContent = fread($fpDb, filesize($dbFileName));
        fclose($fpDb);
        $showsDb = json_decode($dbRawContent);
        //print_r($showsDb);
        for ($i=0;$i<count($showsDb->shows);$i++)
        {
            $showName = $showsDb->shows[$i]->name;
            $qtyPics = 1;
            $showHandleNameRaw = preg_split("/\//",$showsDb->shows[$i]->url);
            sleep(rand(1,7));
            $this->getImage($showsDb->shows[$i]->show_poster->image_original_url."?w=1920&crop=focalpoint&fit=crop&q=80", $showHandleNameRaw[2].".jpg");

        }

        
        

    }

    private function getImage ($url,$imageName)
    {
        $fp = fopen(storage_path('siba/imgs/'.$imageName),"w+");

        //Inicia Curl
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);

        /*
        if($headers != null && in_array('Custom-SSL-Verification:false',$headers)){
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        }
        */
        curl_setopt($c, CURLOPT_FILE, $fp);

        $contents = curl_exec($c);

        if($errno = curl_errno($c)) {
            $error_message = curl_strerror($errno);
            
            curl_close($c);
            fclose($fp);

            $contents = "cURL error ({$errno}):\n {$error_message}";
            echo "[error] cURL error ({$errno}): {$error_message}\n";
            return false;
        }
        else{

            curl_close($c);
            fclose($fp);
            
            echo "[ok] Se procesó la imagen correctamente para {$url}\n";
            return true;

        }
        
    }
}
