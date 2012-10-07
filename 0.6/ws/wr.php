<?php
session_start();

require_once('../../lib/functionsnew.php');
require_once('Walkroute.php');
require_once('../User.php');


$pconn=pg_connect("host=localhost dbname=osmgb user=graham password=1234");
$cleaned=clean_input($_REQUEST,'pgsql');


switch($cleaned["action"])
{
    case "add":
        if($_SERVER['REQUEST_METHOD']=='POST')
        {
            $userid=0;
            if(isset($_SERVER['PHP_AUTH_USER']) &&
                isset($_SERVER['PHP_AUTH_PW']))
            {
                if(($result==User::isValidLogin
                        ($_SERVER['PHP_AUTH_USER'],
                        $_SERVER['PHP_AUTH_PW']))!==null)
                {
                    $row=pg_fetch_array($result,null,PGSQL_ASSOC);
                    $userid=$row["id"];
                }
            }    
            elseif(isset($_SESSION["gatekeeper"]))
            {
                $userid=get_user_id ($_SESSION['gatekeeper'],
                                'users','username','id','pgsql');
            }
            if($userid==0)
            {
                header("HTTP/1.1 401 Unauthorized");
            }
            else
            {
                $j=Walkroute::addWR(stripslashes($_POST["route"]),$userid);
                echo $j;
            }
        }
        else
        {
            header("HTTP/1.1 400 Bad Request");
            echo "Please use a POST request";
        }
        break;

    case "get":
        if($_SERVER['REQUEST_METHOD']=='GET' &&
            isset($cleaned["format"]) && isset($cleaned["id"]))
        {
            $wr=new Walkroute($cleaned["id"]);
            $doAnnotations = (isset($cleaned["ann"]) && $cleaned["ann"] ) ?
                true:false;
            if($wr->isValid())
            {
                switch($cleaned["format"])
                {
                    case "json":
                    case "geojson":
                        header("Content-type: application/json");
                        echo $wr->toGeoJSON($doAnnotations);
                        break;

                    case "gpx":
                        header("Content-type: text/xml");
                        header("Content-Disposition: attachment; filename=".
                                    "walkroute${cleaned[id]}.gpx");
                        echo $wr->toGPX($doAnnotations);
                        break;
                }
            }
        }
        else
        {
            header("HTTP/1.1 400 Bad Request");
            echo "Please supply a format (json, gpx) and route ID, and use GET";
            exit;
        }
        break;
                    
    case "getByBbox":
        if($_SERVER['REQUEST_METHOD']=='GET'
            && isset($cleaned["bbox"]) && isset($cleaned["format"]))
        {
            $b = explode(",",$cleaned["bbox"]);
            if(is_array($b) && count($b)==4)
            {
                $routes=Walkroute::getRoutesByBbox($b[0],$b[1],$b[2],$b[3]);
                Walkroute::outputRoutes($routes,$cleaned["format"]);
            }
        }
        else
        {
            header("HTTP/1.1 400 Bad Request");
            echo "Please supply a bounding box and a format, and use GET";
        }
        break;

    case "getByUser":
        if($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            header("HTTP/1.1 400 Bad Request");
            echo "Please use GET";
        }
        else
        {
            $userid=0;
            if(isset($cleaned["userid"]))
                $userid = $cleaned["userid"];
            else if(isset($_SESSION["gatekeeper"]))
                $userid=get_user_id ($_SESSION['gatekeeper'],
                                'users','username','id','pgsql');
            if($userid>0)
            {
                $routes=Walkroute::getRoutesByUser($userid);
                Walkroute::outputRoutes($routes,$cleaned["format"]);
            }
            else
            {
                header("HTTP/1.1 400 Bad Request");
            }
        }
        break;
}

pg_close($pconn);
?>
