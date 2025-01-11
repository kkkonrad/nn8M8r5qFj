<?php
date_default_timezone_set("America/New_York");
#0 Id; #1 QuotationDate; #2 TimeStampStart; #3 Open; #4 High; #5 Low; #6 Close; #7 UnifiedAssetPrice; #8 Volume; #9 TimeStampEnd; #10 AssetVolume;
#11 NumberOfTrades; #12 TakerBuyBaseAssetVolume; #13 TakerBuyQuoteAssetVolume; #14 EmptyValue; #15 WT_LB; #16 StochRSI; #17 RSI;
#18 WT_LB_Id; #19 WT_LB_SettingsId;20 WT_LB_Settings;21 WT_LB_AP;22 WT_LB_ESA;23 WT_LB_ABS;24 WT_LB_D;25 WT_LB_CI;26 WT_LB_Wt1Green;
#27 WT_LB_Wt2Red; #28 WT_LB_Wt1MinusWt2Blue

class Trader {
    var $btc=1;
    var $usd=0;
    var $border=70;
    var $count=0;

    var $open, $high, $low, $close, $volume, $assetvolume, $trades, $tbbav, $tbqav, $wtlb_green, $wtlb_red;

    const SELL = "sell";
    const BUY = "buy";
    var $nextaction = self::SELL;


    public function test(){
        if($this->nextaction==self::BUY
            && end($this->wtlb_green) <= -$this->border 
            && end($this->wtlb_red) <= -$this->border 
            && end($this->wtlb_red) < end($this->wtlb_green)
            && array_sum(array_slice($this->trades,-20))/20 > 1500
//           && (
//               end($this->close) < min(array_slice($this->close,-60)) 
//              || min(array_slice($this->close,360)) < min(array_slice($this->close,-120))
//            )
            ){
                $this->btc = ($this->usd / end($this->close)) * (999/1000);
                $this->usd = 0;
                $this->nextaction=self::SELL;
                echo "B: ".number_format($this->btc,2,'.','')." T:".end($this->trades)."\n";
                $this->count++;
                return;
        }
        if($this->nextaction==self::SELL
            && end($this->wtlb_green) >= $this->border 
            && end($this->wtlb_red) >= $this->border 
            && end($this->wtlb_red) > end($this->wtlb_green)
            && array_sum(array_slice($this->trades,-20))/20 > 1500
//            && (
//                end($this->close) > max(array_slice($this->close,-60)) 
//               || max(array_slice($this->close,-360)) > max(array_slice($this->close,-120))
//            )
            ){
                $this->usd = ($this->btc * end($this->close)) * (999/1000);
                $this->btc = 0;
                $this->nextaction=self::BUY;
                echo "S: ".number_format($this->usd,2,'.','')." T:".end($this->trades)." ";
                $this->count++;
                return;
        }
    }

    public function run(){
        $close = [];
        $row = 1;
        if (($handle = fopen("2024_08_26_21_21_31.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
                $time=intval(date('Hi', intval($data[2])/1000));
                //if(($time>=300 && $time<500) || ($time>=800 && $time<1030) || ($time>=1300 && $time<1500)){
                //if(($time>=230 && $time<500) || ($time>=730 && $time<1030) || ($time>=1250 && $time<1500)){
                //    echo intval($data[6])."\n";
                //}
                //$start = 0;
                $this->open[] = floatval($data[3]);
                $this->high[] = floatval($data[4]);
                $this->low[] = floatval($data[5]);
                $this->close[] = floatval($data[6]);
                $this->volume[] = floatval($data[8]);
                $this->assetvolume[] = floatval($data[10]);
                $this->trades[] = intval($data[11]);
                $this->tbbav[] = floatval($data[12]);
                $this->tbqav[] = floatval($data[13]);
                $this->wtlb_green[] = floatval($data[26]);
                $this->wtlb_red[] = floatval($data[27]);
                $this->test();
                //}
                $row++;
            }
            fclose($handle);
        }
        echo "\nilość transakcji: ".$this->count."\n";
    }

}

$trader = new Trader();
$trader->run();
