<?
namespace Spring\Main;
use \Bitrix\Main\Application;
use \Bitrix\Main\Web\HttpClient;

class ExchangeService{
	public function loadApiData($link){
		$httpClient = new HttpClient();
		$jsonString = $httpClient->get($link);
		$data = json_decode($jsonString, true);
		
		$el = new \CIBlockElement;
		$arFields = array(
			"IBLOCK_ID"       => 5,
			"NAME"            => date('d.m.Y H:i:s'),
			"ACTIVE"          => "Y",
			"PROPERTY_VALUES" => array(
				"TEMPERATURE" => $data["hourly"]["temperature_2m"][0],
				"WIND_SPEED"  => $data["hourly"]["wind_speed_10m"][0]
			)
		);
		$el->Add($arFields);

		return $data;
	}
	
	public function parseJson($filePath){
		$jsonString = file_get_contents(Application::getDocumentRoot().'/'.$filePath);
		$data = json_decode($jsonString, true);

		return $data;
	}
}
?>