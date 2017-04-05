<html>
<head>
<meta http-equiv="Content-Language" content="es-ar">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="shortcut icon" href="favicon.ico">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,800" rel="stylesheet">
<style>
body {
  font-family: Montserrat;
  font-size: 14px;
  background-color:#FFF;   
}

</style>
<body>
<br>
<div class="container" style="width: 480px;">
<?php
//create array of data to be posted
require('qs_functions.php');
$c = qsrequest("c"); //combustible

$post_data['optionsRadios'] = $c;
$post_data['idproducto'] = $c;
$post_data['bandera'] = '';

//traverse array and prepare data for posting (key1=value1)
foreach ( $post_data as $key => $value) {
$post_items[] = $key . '=' . $value;
}
//create the final string to be posted using implode()
$post_string = implode ('&', $post_items);
//echo $post_string;
//create cURL connection
$curl_connection = 
curl_init('https://preciosensurtidor.minem.gob.ar/estacionesdeservicio/mapa-busqueda');
//set options
curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
//curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
//set data to be posted
curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
//perform our request
$result = curl_exec($curl_connection);
//show information regarding the request
//print_r(curl_getinfo($curl_connection));
//echo curl_errno($curl_connection) . '-' . 
//curl_error($curl_connection);
//echo $result;

function findVar($var, $url){
    $data = $url;
    if(strpos($data, $var) == false) return false;
    $len = strlen($var) + 2;
    $start = strpos($data, $var." = [") + $len; 
    $stop = strpos($data, ";", $start); 
    $val = substr($data, $start, ($stop-$start));
    return $val; 
}

$variable = "var aEmpresas";
$url = $result;
$value = findVar($variable, $url);

$healthy = array("\"precios\":{\"" . $c ."\":{", "}}");
$yummy   = array("", "");

$newphrase = str_replace($healthy, $yummy, $value);
//echo $newphrase;

$ip = getenv("REMOTE_ADDR");
$ipreplace = array(".");
$ipreplaced   = array("");
$newip = str_replace($ipreplace, $ipreplaced, $ip);

$rand = rand ();
$datajson = $newip . '-' . $rand . '.json';
$datacsv = $newip . '-' . $rand . '.csv';

$fp = fopen('output/' . $datajson,"wb");
fwrite($fp,$newphrase);
fclose($fp);
//close the connection
curl_close($curl_connection);


function jsonToCSV($jfilename, $cfilename)
{
    if (($json = file_get_contents($jfilename)) == false)
        die('Error reading json file...');
    $data = json_decode($json, true);
    $fp = fopen($cfilename, 'w');
    $header = false;
    foreach ($data as $row)
    {
        if (empty($header))
        {
            $header = array_keys($row);
            fputcsv($fp, $header);
            $header = array_flip($header);
        }
        fputcsv($fp, array_merge($header, $row));
    }
    fclose($fp);
    return;
}

$json_filename = 'output/' . $datajson;
$csv_filename = 'output/' . $datacsv;

jsonToCSV($json_filename, $csv_filename);
echo '<h4>Conversi&oacute;n de precios de surtidores exitosa. <a href="' . $csv_filename . '" target="_blank">Descargar csv</a>.</h4><br><p>Su archivo estar&aacute; disponible por 24 horas.</p>.';
echo '<h5><a href="javascript:history.back()" target="_top">Volver</a></h5>';
?>
</div>
</body>
</html>