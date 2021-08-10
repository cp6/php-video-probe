<?php
require_once('vendor/autoload.php');

$v = new videoProbe();

//Create ffprobe output
$v->createOutputFile('BigBuckBunny.mkv','test.json');

//Set and load the output file
$v->setOutputFile('test.json');
$v->setOutput();

//Assign properties
$v->assignProperties();

//Video bitrate
echo $v->bitrateAsKbps(true,2);
echo $v->bitrateAsMbps();

//Video size
echo $v->sizeAsMB(true,2);//Format to 2 decimals
echo $v->sizeAsGB();

//Use this for pretty print json in Firefox
//header("Content-Type: application/json;charset=utf-8");

//Stream types that exist
echo json_encode($v->streamsTypes());

//Stream types and their amount
echo json_encode($v->streamsTypesAmounts());

//Array with refined information for the stream types
echo json_encode($v->video_streams);
//echo json_encode($v->audio_streams);
echo json_encode($v->subtitles);

//Access some of the assigned properties
echo $v->width;
echo $v->height;
echo $v->size;


//Media type 1 = video/audio, 2 = video only, 3 = audio only
echo $v->mediaType();

//Media type as readable string
echo $v->mediaTypeAsString($v->mediaType());

//Duration as a HH:MM:SS format string
echo $v->durationAsString();

//Height x width string (HxWp)
echo $v->widthHeightString();

//Streams amount
echo $v->streamsAmount();
//Streams amount for type
echo $v->streamsAmountForType('video');

//Streams order per index
echo json_encode($v->streamsOrder());

//Individually return a Stream
echo json_encode($v->returnStreamArray(1));//return stream index 1