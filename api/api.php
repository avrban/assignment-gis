<?php
require_once("config.php");

/*router*/
if (isset($_REQUEST['q'])) {
    echo get_locations($_REQUEST['q']);
}

if (isset($_POST['get_locality']) && isset($_POST['lokalita'])) {
    echo get_locality($_POST['lokalita']);
}

if (isset($_POST['get_attractions']) && isset($_POST['lokalita']) && isset($_POST['vzdialenost']) && isset($_POST['typy']) && isset($_POST['datum'])) {

    if (isset($_POST['hodiny']) && $_POST['hodiny'] == "true") $hodiny = true; else $hodiny = false;
    echo get_attractions($_POST['lokalita'], $_POST['vzdialenost'], $_POST['typy'], $_POST['datum'], $hodiny);
}

if (isset($_POST['get_tracks']) && isset($_POST['lokalita']) && isset($_POST['vzdialenost']) && isset($_POST['trate'])) {

    echo get_tracks($_POST['lokalita'], $_POST['vzdialenost'], $_POST['trate']);
}

/*autocomplete lokalit*/
function get_locations($name)
{
    global $db;

    $q = "
    SELECT DISTINCT(name)
    FROM planet_osm_polygon
    WHERE boundary = 'administrative'
    AND lower(name) LIKE lower('" . $name . "%')";

    $res = pg_query($db, $q);

    $results = array();
    while ($row = pg_fetch_row($res)) {
        $results[] = array(
            'label' => $row[0],
            'value' => $row[0],
        );
    }

    return json_encode($results);
}

/*zvolena lokalita*/
function get_locality($locality)
{
    global $db;

    $q = "SELECT ST_AsGeoJSON(way) as geo, ST_X(ST_Centroid(way)) AS x, ST_Y(ST_Centroid(way)) AS y
    FROM planet_osm_polygon
    WHERE name = '" . $locality . "'
    AND boundary = 'administrative'
    LIMIT 1";

    $res = pg_query($db, $q);

    $results = '
    { 
    "type": "FeatureCollection", 
    "features": 
        [';

    $d = false;

    while ($row = pg_fetch_row($res)) {
        $results .= '
            { 
                "type": "Feature", 
                "geometry": ' . $row[0] . ',
                "properties": 
                {
                    "centroidX": "' . $row[1] . '",
                    "centroidY": "' . $row[2] . '"
                }
            }';
    }

    $results .= "
        ]
    }";
    return $results;
}

/*nacitanie atrakcii v lokalte podla parametrov*/
function get_attractions($locality, $distance, $types, $date, $check_hours)
{
    global $db;
    include("oh_parser.php");

    if (empty($distance)) $distance = 0;
    else $distance *= 1000;

    $q_types = " p2.leisure = 'aaa' ";
    if (in_array("muzea", $types)) {
        $q_types .= " OR p2.tourism = 'museum' ";
    }
    if (in_array("pamiatky", $types)) {
        $q_types .= " OR p2.tourism = 'attraction' ";
    }
    if (in_array("priroda", $types)) {
        $q_types .= " OR p2.leisure = 'recreation_ground' OR p2.leisure = 'nature_reserve' ";
    }
    if (in_array("parky", $types)) {
        $q_types .= " OR p2.leisure = 'park' OR p2.leisure = 'garden' OR p2.leisure = 'picnic_table' ";
    }
    if (in_array("aquaparky", $types)) {
        $q_types .= " OR p2.leisure = 'water_park' ";
    }
    if (in_array("plavby", $types)) {
        $q_types .= " OR p2.leisure = 'marina' ";
    }
    if (in_array("sportoviska", $types)) {
        $q_types .= " OR p2.leisure = 'sports_centre' OR p2.leisure = 'track' OR p2.leisure = 'stadium'  OR p2.leisure = 'miniature_golf' OR p2.leisure = 'horse_riding'";
    }
    if (in_array("lyzovanie", $types)) {
        $q_types .= " OR p2.leisure = 'ski_resort' ";
    }


    $q = "
WITH polygons AS (
    SELECT DISTINCT(p2.name) as name, ST_AsGeoJSON(p2.way) AS geo, ST_X(ST_Centroid(p2.way)) AS x, ST_Y(ST_Centroid(p2.way)) AS y, ST_Distance(p1.way::geography,p2.way::geography) as distance, p2.leisure AS leisure, p2.tourism AS tourism, p2.route AS route
    FROM planet_osm_polygon p1
    JOIN planet_osm_polygon p2
    ON st_contains(p1.way,p2.way) OR ST_DWithin(p1.way::geography,p2.way::geography," . $distance . ")
    WHERE p1.name = '" . $locality . "'
    AND p1.boundary = 'administrative'
    AND p2.name IS NOT NULL
    AND ( " . $q_types . " )
), points AS (
    SELECT DISTINCT(p2.name) as name, ST_AsGeoJSON(p2.way) AS geo, ST_X(ST_Centroid(p2.way)) AS x, ST_Y(ST_Centroid(p2.way)) AS y, ST_Distance(p1.way::geography,p2.way::geography) as distance, p2.leisure AS leisure, p2.tourism AS tourism, p2.route AS route
    FROM planet_osm_polygon p1
    JOIN planet_osm_point p2
    ON st_contains(p1.way,p2.way) OR ST_DWithin(p1.way::geography,p2.way::geography," . $distance . ")
    WHERE p1.name = '" . $locality . "'
    AND p1.boundary = 'administrative'
    AND p2.name IS NOT NULL
    AND ( " . $q_types . " )
 )
 
    SELECT name, geo, x, y, distance, leisure, tourism, route
    FROM polygons
    UNION ALL 
    SELECT name, geo, x, y, distance, leisure, tourism, route
    FROM points
    ORDER BY distance
    ";


    $res = pg_query($db, $q);

    $results = '
    { 
    "type": "FeatureCollection", 
    "features": 
        [';

    $d = false;

    while ($row = pg_fetch_row($res)) {
        if ($d) $results .= ',';
        $d = true;

        if ($check_hours) $hodiny = get_openinghours($row[0], $date);
        else $hodiny = "";

        $results .= '
            { 
                "type": "Feature", 
                "geometry": ' . $row[1] . ',
                "properties": 
                {
                    "name": "' . $row[0] . '",
                    "centroidX": "' . $row[2] . '",
                    "centroidY": "' . $row[3] . '",
                    "distance": "' . $row[4] . '",
                    "hours": "' . $hodiny . '",
                    "leisure": "' . $row[5] . '",
                    "tourism": "' . $row[6] . '"
                }
            }';
    }

    $results .= "
        ]
    }";
    return $results;
}

/*nacitanie trati a chodnikov podla parametrov*/
function get_tracks($locality, $distance, $types)
{
    global $db;

    if (empty($distance)) $distance = 0;
    else $distance *= 1000;

    $q_types = " p2.route = 'aaa' ";
    if (in_array("turisticke", $types)) {
        $q_types .= " OR p2.route = 'foot' ";
    }
    if (in_array("cyklisticke", $types)) {
        $q_types .= " OR p2.route = 'bicycle' ";
    }
    if (in_array("bezkarske", $types)) {
        $q_types .= " OR p2.route = 'ski' ";
    }
    if (in_array("horolezecke", $types)) {
        $q_types .= " OR p2.route = 'ski' ";
    }


    $q = "
    SELECT DISTINCT(p2.name) as name, ST_AsGeoJSON(p2.way) AS geo, ST_X(ST_Centroid(p2.way)) AS x, ST_Y(ST_Centroid(p2.way)) AS y, ST_Distance(p1.way::geography,p2.way::geography) as distance, p2.route AS route, ST_Length(p2.way::geography) AS length, p2.tracktype AS grade
    FROM planet_osm_polygon p1
    JOIN planet_osm_line p2
    ON st_contains(p1.way,p2.way) OR st_intersects(p1.way,p2.way) OR ST_DWithin(p1.way::geography,p2.way::geography," . $distance . ")
    WHERE p1.name = '" . $locality . "'
    AND p1.boundary = 'administrative'
    AND p2.name IS NOT NULL
    AND ( " . $q_types . " )
    ORDER BY ST_Distance(p1.way::geography,p2.way::geography) 
    ";

    $res = pg_query($db, $q);

    $results = '
    { 
    "type": "FeatureCollection", 
    "features": 
        [';

    $d = false;

    while ($row = pg_fetch_row($res)) {
        if ($d) $results .= ',';
        $d = true;

        $results .= '
            { 
                "type": "Feature", 
                "geometry": ' . $row[1] . ',
                "properties": 
                {
                    "name": "' . $row[0] . '",
                    "centroidX": "' . $row[2] . '",
                    "centroidY": "' . $row[3] . '",
                    "distance": "' . $row[4] . '",
                    "route": "' . $row[5] . '",
                    "length": "' . $row[6] . '",
                    "grade": "' . $row[7] . '"
                }
            }';
    }

    $results .= "
        ]
    }";
    return $results;
}