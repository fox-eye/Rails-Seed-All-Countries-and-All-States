<?php
/**
 * Build the Country and State Tables
 */
$file = fopen("./countryInfo.txt","r");
$contents = fread($file, filesize("countryInfo.txt"));
fclose($file);

$processed_countries = array();
$arr = explode("\n",$contents);
$i = 1;
foreach($arr as $country) {
  if(preg_match("/(^#)/",$country,$match) || !$country) {
    continue;
  }
  $arr_country = explode("\t", $country);
  $processed_countries[$arr_country[0]] = array("id"=>$i++, "iso"=>$arr_country[0], "name"=>$arr_country[4]);
}


$file = fopen("admin1CodesASCII.txt","r");
$contents = fread($file, filesize("admin1CodesASCII.txt"));
fclose($file);

$processed_regions = array();
$i = 1;
$admin1 = explode("\n",$contents);

foreach($admin1 as $section_admin1) {
  if(!$section_admin1) { continue; }
  $state = explode("\t",$section_admin1);
  if(!$state[1]) { continue; }
  //Figure out where to put the region
  $iso_and_region = explode(".", $state[0]);
  $country_iso = $iso_and_region[0];
  $region_iso = $iso_and_region[1];
  $target_country = $processed_countries[$country_iso];
  $processed_regions[] = array("id" => $i++, "country_id" => $target_country["id"], "iso" => $region_iso, "name" => $state[1]);
}

$seedfile = fopen("state_country_seeds.rb", "w");
fwrite($seedfile, "Country.delete_all\n");
fwrite($seedfile, 'connection.execute("ALTER TABLE countries AUTO_INCREMENT = 1")'."\n");

//Build the SQL Insert Statements (Country)
foreach($processed_countries as $country) {
  fwrite($seedfile, "Country.create(:id=>".$country['id'].",:iso=>\"".$country['iso']."\",:name=>\"".$country['name']."\")\n");
}

fwrite($seedfile, "\nState.delete_all\n");
fwrite($seedfile, 'connection.execute("ALTER TABLE states AUTO_INCREMENT=1")'."\n");

foreach($processed_regions as $region) {
  fwrite($seedfile, "State.create(:id=>".$region['id'].",:iso=>\"".$region['iso']."\",:name=>\"".$region['name']."\",:country_id=>".$region['country_id'].")\n");
}