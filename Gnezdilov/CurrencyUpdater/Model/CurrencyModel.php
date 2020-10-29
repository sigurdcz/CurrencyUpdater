<?php 
declare(strict_types = 1); 
namespace Gnezdilov\CurrencyUpdater\Model;

class CurrencyModel
{

    
    private $updateableCurrenciesIso = [
        'EUR', 'USD'
    ];

    private $responseCurrencies;
    private $apiBaseUrl = 'https://www.cnb.cz/en/financial-markets/foreign-exchange-market/central-bank-exchange-rate-fixing/central-bank-exchange-rate-fixing/';
    private $apiFileName = 'daily.txt';
    private $apiSessionId = '385F7CBF54A30CA0AE98DC6E7123A262';
    private $apiDate = '27.10.2019';
    private $isStrict = false;

 
	
    function __construct()
    {
        //cache response
        $this->responseCurrencies = $this->getCurrencies();
    }

    // opet nutny refactor do kolekce
    /**
     * 
     *
     * @param string $iso
     * @return CurrencyIteratorItem|null
     */
    public function getCurrencyByIso(string $iso): ?CurrencyIteratorItem
    {
        $return = null;
        foreach ($this->responseCurrencies as $CurrencyIteratorItem) {
            if ($CurrencyIteratorItem->getCode() == $iso) {
                $return = $CurrencyIteratorItem;
                break;
            }
        }
        return $return;
    }

    public function isUpdateAbleSuccess(): bool{
        (bool) $return = false;
        $return = $this->checkUpdateAbleSuccess()->code === 1;
        return $return;
    }

    public function checkUpdateAbleSuccess(): object{
        
        // data definition
        (array) $data = null;
        (array) $data['messages'] = [];
        (int) $data['code'] = 1;
        

        if(count($this->updateableCurrenciesIso)> 0){
            if($this->isStrict){
                (array) $updateableCurrenciesIso = $this->updateableCurrenciesIso;
                (array) $aviableCurrencies = [];
                foreach ($this->responseCurrencies as $CurrencyIteratorItem) {
                    (string) $code = $CurrencyIteratorItem->getCode();
                    $aviableCurrencies[] = $code;
                }
                arsort($aviableCurrencies);
                arsort($updateableCurrenciesIso);
                (array) $notUpdated = (array_diff( $updateableCurrenciesIso, $aviableCurrencies));
                if(count($notUpdated) === 0){
                    $data['code'] = 2;
                }else{
                    $data['messages']['warning'][] = "Currencies are not in response list. They are cant be updated. [".implode('|', $notUpdated)."]";
                    $data['code'] = 3;
                }
            }
        }else{
            $data['messages']['warning'][] = "Updateable Currencies are empty!";
            $data['code'] = 4;
        }


        return  json_decode(json_encode($data));
    }

    public function setUpdateableCurrencies(array $codes): CurrencyModel{
        $this->updateableCurrenciesIso = $codes;
        return $this;
    }
    
    public function setUpdateStrict(bool $isStrict): CurrencyModel{
        $this->isStrictUpdate = $isStrict;
        return $this;
    }

    public function getIsStrict(): bool{
        return $this->isStrict;
    }

    /**
     * @return array<CurrencyIteratorItem>
     */
    public function getCurrencies(): array
    {
        (object) $response = $this->getResponse();
        (string) $data = $response->data;

        (array) $currenciesResponse = $this->getParsedResponse($data);
        array_shift($currenciesResponse); // data - datum,cas
        array_shift($currenciesResponse); // header tabulky

        (array) $currencies = [];
        foreach ($currenciesResponse as $currencyResponse) {
            $currencyItemParsed = explode('|', $currencyResponse);
            $currencies[] = new CurrencyIteratorItem($currencyItemParsed[0], $currencyItemParsed[1], (int) $currencyItemParsed[2], $currencyItemParsed[3], (float) $currencyItemParsed[4]);
        }

        return $currencies;
    }
    private function getParsedResponse(string $response = ''): array
    {
        (array) $lines = explode(PHP_EOL, $response);
        $lines = array_filter($lines);
        return $lines;
    }
    public function getApiUrl(): string
    {
        (string) $return = $this->apiBaseUrl . '/' . $this->apiFileName . ';jsessionid=' . $this->apiSessionId . '?date=' . $this->apiDate;
        return $return;
    }
    public function isCurrencyServerAviable(): bool
    {
        (bool) $return = $this->hasResponde200($this->getApiUrl());
        return $return;
    }
    private function getResponse(): object
    {
        (array) $data = [];
        $data['data'] = '';


        $url = $this->getApiUrl();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ));

        $dataResponse = curl_exec($curl);

        curl_close($curl);

        $data['data'] = $dataResponse;



        return  json_decode(json_encode($data));
    }

    public function updateAll(): void
    {
        $updateableCurrenciesIso = $this->updateableCurrenciesIso;
        foreach ($this->getCurrencies() as $CurrencyIteratorItem) {
            (string) $code = $CurrencyIteratorItem->getCode();

            if (in_array($code, $updateableCurrenciesIso)) {
                $this->update($CurrencyIteratorItem);
            } else {
                //"ISO [$code] is not updateable.";
            }
        }
    }

    public function update(CurrencyIteratorItem $CurrencyIteratorItem): void
    {

        (string) $code = $CurrencyIteratorItem->getCode();
        (float) $rate =  $CurrencyIteratorItem->getRate();
        // pokud existuje nejaky model zodpovidajici za Curency v Magentu tak použít ten jinak 
        // tabulka `directory_currency_rate`  nese data
    }
	
	
	/** check if header of url has 404 responde in all headers */
	private function hasResponde200($url)
	{
		$file_headers = @get_headers($url);
		//info($file_headers);
		$exists = false;

		if ($file_headers) {
			foreach (array_reverse($file_headers) as $header) {
				if ($header == 'HTTP/1.1 200' || $header == 'HTTP/1.1 200 200' || $header == 'HTTP/1.1 200 OK') {
					$exists = true;
				}
			}
		}
		return $exists;
	}


}
include('helper.php');
